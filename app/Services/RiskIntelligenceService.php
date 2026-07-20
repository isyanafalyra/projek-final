<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Pool;

class RiskIntelligenceService
{
    /**
     * Open-Meteo API: Mengambil data cuaca berdasarkan koordinat latitude & longitude.
     * Cache duration: 1 Jam (3600 detik)
     */
    public function getWeatherData(float $lat, float $lng): array
    {
        $cacheKey = "weather_data_lat_lng_{$lat}_{$lng}";

        return Cache::remember($cacheKey, 3600, function () use ($lat, $lng) {
            if (!\Illuminate\Support\Facades\Cache::get('offline_api_open_meteo')) {
                try {
                    $response = Http::timeout(3)->get('https://api.open-meteo.com/v1/forecast', [
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,rain,showers,snowfall,weather_code,wind_speed_10m,wind_gusts_10m',
                        'timezone' => 'auto'
                    ]);

                    if ($response->successful()) {
                        return $response->json();
                    }
                } catch (\Exception $e) {
                    Log::error("Open-Meteo API Error: " . $e->getMessage());
                    \Illuminate\Support\Facades\Cache::put('offline_api_open_meteo', true, 600);
                }
            }

            // Fallback empty weather data
            return [
                'current' => [
                    'temperature_2m' => 0.0,
                    'relative_humidity_2m' => 0,
                    'apparent_temperature' => 0.0,
                    'precipitation' => 0.0,
                    'wind_speed_10m' => 0.0,
                    'weather_code' => 0,
                ],
                'is_fallback' => true
            ];
        });
    }

    /**
     * World Bank API: Mengambil data indikator makroekonomi (GDP, Inflasi, Populasi, Ekspor/Impor).
     * Cache duration: 30 Hari (2592000 detik)
     */
    public function getMacroData(string $countryIso): array
    {
        $countryIso = strtoupper($countryIso);
        $cacheKey = "worldbank_macro_data_{$countryIso}";

        // Try retrieving from cache
        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            if (is_array($cached) && !empty($cached) && !isset($cached['is_fallback'])) {
                return $cached;
            }
        }

        $result = [];

        if (!\Illuminate\Support\Facades\Cache::get('offline_api_worldbank')) {
            try {
                $indicators = [
                    'gdp' => 'NY.GDP.MKTP.CD', // GDP (current USD)
                    'inflation' => 'FP.CPI.TOTL.ZG', // Inflation, consumer prices (annual %)
                    'population' => 'SP.POP.TOTL', // Population, total
                    'exports_gdp' => 'NE.EXP.GNFS.ZS', // Exports of goods and services (% of GDP)
                    'imports_gdp' => 'NE.IMP.GNFS.ZS', // Imports of goods and services (% of GDP)
                ];

                // Fetch all indicators concurrently using HTTP pool to avoid sequential timeouts
                $responses = Http::pool(function (Pool $pool) use ($countryIso, $indicators) {
                    $requests = [];
                    foreach ($indicators as $key => $code) {
                        $requests[$key] = $pool->as($key)->timeout(3)->get("http://api.worldbank.org/v2/country/{$countryIso}/indicator/{$code}", [
                            'date' => '2015:2025',
                            'format' => 'json'
                        ]);
                    }
                    return $requests;
                });

                foreach ($indicators as $key => $code) {
                    $response = $responses[$key] ?? null;
                    if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                        $data = $response->json();
                        // Respon World Bank bertipe array: [metadata, data_records]
                        if (isset($data[1]) && is_array($data[1])) {
                            $records = [];
                            foreach ($data[1] as $record) {
                                if ($record['value'] !== null) {
                                    $records[] = [
                                        'year' => (int) $record['date'],
                                        'value' => (float) $record['value']
                                    ];
                                }
                            }
                            // Urutkan tahun terlama ke terbaru
                            usort($records, fn($a, $b) => $a['year'] <=> $b['year']);
                            $result[$key] = $records;
                        }
                    }
                }
                
                if (empty($result)) {
                    \Illuminate\Support\Facades\Cache::put('offline_api_worldbank', true, 600);
                }
            } catch (\Exception $e) {
                Log::error("World Bank API Concurrent Error ({$countryIso}): " . $e->getMessage());
                \Illuminate\Support\Facades\Cache::put('offline_api_worldbank', true, 600);
            }
        }

        if (empty($result)) {
            // Fallback realistic macro data
            $mockData = $this->getMockMacroData($countryIso);
            $mockData['is_fallback'] = true;
            // Cache fallback/mock data for max 5 minutes (300 seconds)
            Cache::put($cacheKey, $mockData, 300);
            return $mockData;
        }

        // Cache successful response for 30 days (2592000 seconds)
        Cache::put($cacheKey, $result, 2592000);
        return $result;
    }

    /**
     * REST Countries API (Menggunakan countries.dev): Mengambil informasi dasar/profil negara.
     * Cache duration: 30 Hari (2592000 detik)
     */
    public function getCountryDetails(string $countryIso): array
    {
        $countryIso = strtoupper($countryIso);
        $cacheKey = "rest_country_details_{$countryIso}";

        // Try retrieving from cache
        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            if (is_array($cached) && !empty($cached) && !isset($cached['is_fallback'])) {
                return $cached;
            }
        }

        if (!\Illuminate\Support\Facades\Cache::get('offline_api_countries_dev')) {
            try {
                $response = Http::timeout(3)->get("https://countries.dev/alpha/{$countryIso}");

                if ($response->successful()) {
                    $info = $response->json();
                    if (isset($info['name'])) {
                        $currencyCode = '';
                        $currencyName = '';
                        if (isset($info['currencies'][0])) {
                            $currencyCode = $info['currencies'][0]['code'] ?? '';
                            $currencyName = $info['currencies'][0]['name'] ?? '';
                        }

                        $langs = [];
                        if (isset($info['languages']) && is_array($info['languages'])) {
                            foreach ($info['languages'] as $lang) {
                                if (isset($lang['name'])) {
                                    $langs[] = $lang['name'];
                                }
                            }
                        }

                        $data = [
                            'official_name' => $info['name'] ?? '',
                            'capital' => $info['capital'] ?? '',
                            'region' => $info['region'] ?? '',
                            'subregion' => $info['subregion'] ?? '',
                            'flag_url' => $info['flags']['png'] ?? $info['flags']['svg'] ?? '',
                            'currency_code' => $currencyCode,
                            'currency_name' => $currencyName,
                            'languages' => $langs,
                        ];

                        // Cache successful response for 30 days (2592000 seconds)
                        Cache::put($cacheKey, $data, 2592000);
                        return $data;
                    }
                }
            } catch (\Exception $e) {
                Log::error("countries.dev API Error for {$countryIso}: " . $e->getMessage());
                \Illuminate\Support\Facades\Cache::put('offline_api_countries_dev', true, 600);
            }
        }

        // Fallback dictionary untuk negara yang terdaftar di database kita
        $fallbacks = [
            'ID' => [
                'official_name' => 'Republic of Indonesia',
                'capital' => 'Jakarta',
                'region' => 'Asia',
                'subregion' => 'South-Eastern Asia',
                'flag_url' => 'https://flagcdn.com/w320/id.png',
                'currency_code' => 'IDR',
                'currency_name' => 'Indonesian rupiah',
                'languages' => ['Indonesian'],
            ],
            'SG' => [
                'official_name' => 'Republic of Singapore',
                'capital' => 'Singapore',
                'region' => 'Asia',
                'subregion' => 'South-Eastern Asia',
                'flag_url' => 'https://flagcdn.com/w320/sg.png',
                'currency_code' => 'SGD',
                'currency_name' => 'Singapore dollar',
                'languages' => ['English', 'Malay', 'Tamil', 'Chinese'],
            ],
            'CN' => [
                'official_name' => 'People\'s Republic of China',
                'capital' => 'Beijing',
                'region' => 'Asia',
                'subregion' => 'Eastern Asia',
                'flag_url' => 'https://flagcdn.com/w320/cn.png',
                'currency_code' => 'CNY',
                'currency_name' => 'Chinese yuan',
                'languages' => ['Chinese'],
            ],
            'US' => [
                'official_name' => 'United States of America',
                'capital' => 'Washington, D.C.',
                'region' => 'Americas',
                'subregion' => 'North America',
                'flag_url' => 'https://flagcdn.com/w320/us.png',
                'currency_code' => 'USD',
                'currency_name' => 'United States dollar',
                'languages' => ['English'],
            ],
            'NL' => [
                'official_name' => 'Kingdom of the Netherlands',
                'capital' => 'Amsterdam',
                'region' => 'Europe',
                'subregion' => 'Western Europe',
                'flag_url' => 'https://flagcdn.com/w320/nl.png',
                'currency_code' => 'EUR',
                'currency_name' => 'Euro',
                'languages' => ['Dutch'],
            ],
            'JP' => [
                'official_name' => 'Japan',
                'capital' => 'Tokyo',
                'region' => 'Asia',
                'subregion' => 'Eastern Asia',
                'flag_url' => 'https://flagcdn.com/w320/jp.png',
                'currency_code' => 'JPY',
                'currency_name' => 'Japanese yen',
                'languages' => ['Japanese'],
            ],
            'DE' => [
                'official_name' => 'Federal Republic of Germany',
                'capital' => 'Berlin',
                'region' => 'Europe',
                'subregion' => 'Western Europe',
                'flag_url' => 'https://flagcdn.com/w320/de.png',
                'currency_code' => 'EUR',
                'currency_name' => 'Euro',
                'languages' => ['German'],
            ],
            'AU' => [
                'official_name' => 'Commonwealth of Australia',
                'capital' => 'Canberra',
                'region' => 'Oceania',
                'subregion' => 'Australia and New Zealand',
                'flag_url' => 'https://flagcdn.com/w320/au.png',
                'currency_code' => 'AUD',
                'currency_name' => 'Australian dollar',
                'languages' => ['English'],
            ],
            'GB' => [
                'official_name' => 'United Kingdom of Great Britain and Northern Ireland',
                'capital' => 'London',
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'flag_url' => 'https://flagcdn.com/w320/gb.png',
                'currency_code' => 'GBP',
                'currency_name' => 'British pound',
                'languages' => ['English'],
            ],
            'IN' => [
                'official_name' => 'Republic of India',
                'capital' => 'New Delhi',
                'region' => 'Asia',
                'subregion' => 'Southern Asia',
                'flag_url' => 'https://flagcdn.com/w320/in.png',
                'currency_code' => 'INR',
                'currency_name' => 'Indian rupee',
                'languages' => ['Hindi', 'English'],
            ],
            'MY' => [
                'official_name' => 'Malaysia',
                'capital' => 'Kuala Lumpur',
                'region' => 'Asia',
                'subregion' => 'South-Eastern Asia',
                'flag_url' => 'https://flagcdn.com/w320/my.png',
                'currency_code' => 'MYR',
                'currency_name' => 'Malaysian ringgit',
                'languages' => ['Malay'],
            ],
            'KR' => [
                'official_name' => 'Republic of Korea',
                'capital' => 'Seoul',
                'region' => 'Asia',
                'subregion' => 'Eastern Asia',
                'flag_url' => 'https://flagcdn.com/w320/kr.png',
                'currency_code' => 'KRW',
                'currency_name' => 'South Korean won',
                'languages' => ['Korean'],
            ],
        ];

        $fallbackData = $fallbacks[$countryIso] ?? [
            'official_name' => 'Unknown Country',
            'capital' => 'Unknown',
            'region' => 'Unknown',
            'subregion' => 'Unknown',
            'flag_url' => '',
            'currency_code' => 'USD',
            'currency_name' => 'US Dollar',
            'languages' => [],
            'is_fallback' => true
        ];

        // Cache fallback/mock data for max 5 minutes (300 seconds)
        Cache::put($cacheKey, $fallbackData, 300);

        return $fallbackData;
    }

    /**
     * ExchangeRate API: Mengambil kurs mata uang real-time terhadap USD.
     * Cache duration: 12 Jam (43200 detik)
     */
    public function getExchangeRates(string $baseCurrency = 'USD'): array
    {
        $baseCurrency = strtoupper($baseCurrency);
        $cacheKey = "exchange_rates_{$baseCurrency}";

        return Cache::remember($cacheKey, 43200, function () use ($baseCurrency) {
            if (!\Illuminate\Support\Facades\Cache::get('offline_api_exchange_rate')) {
                try {
                    $response = Http::timeout(3)->get("https://open.er-api.com/v6/latest/{$baseCurrency}");

                    if ($response->successful()) {
                        $data = $response->json();
                        return $data['rates'] ?? [];
                    }
                } catch (\Exception $e) {
                    Log::error("ExchangeRate API Error: " . $e->getMessage());
                    \Illuminate\Support\Facades\Cache::put('offline_api_exchange_rate', true, 600);
                }
            }

            // Fallback basic conversion rates
            return [
                'USD' => 1.0,
                'IDR' => 16350.0,
                'SGD' => 1.35,
                'EUR' => 0.92,
                'CNY' => 7.25,
                'JPY' => 160.5,
                'GBP' => 0.78,
                'AUD' => 1.49,
                'INR' => 83.5,
                'MYR' => 4.71,
                'KRW' => 1380.0
            ];
        });
    }

    /**
     * GNews API: Mengambil berita logistik & ekonomi terbaru.
     * Jika API key tidak ada, otomatis memuat mock berita berkualitas.
     * Cache duration: 4 Jam (14400 detik)
     */
    public function getNewsData(string $query = 'global supply chain OR logistics OR shipping OR freight OR trade OR port'): array
    {
        $cacheKey = "gnews_data_" . md5($query);

        return Cache::remember($cacheKey, 14400, function () use ($query) {
            $apiKey = config('services.gnews.key');

            if (!empty($apiKey) && !\Illuminate\Support\Facades\Cache::get('offline_api_gnews')) {
                try {
                    $response = Http::timeout(3)->get('https://gnews.io/api/v4/search', [
                        'q' => $query,
                        'lang' => 'en',
                        'apikey' => $apiKey,
                        'max' => 10
                    ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        $articles = $data['articles'] ?? [];
                        if (!empty($articles)) {
                            return $articles;
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("GNews API Error: " . $e->getMessage());
                    \Illuminate\Support\Facades\Cache::put('offline_api_gnews', true, 600);
                }
            }

            // Jika API Key tidak ada atau limit habis, gunakan Bing News RSS (Berita Riil, Direct Links)
            if (!\Illuminate\Support\Facades\Cache::get('offline_api_bing')) {
                try {
                    $rssQuery = urlencode($query);
                    // Menggunakan Bing News RSS karena memberikan direct URL, menghindari 403 CloudFront dari redirector Google
                    $rssUrl = "https://www.bing.com/news/search?q={$rssQuery}&format=rss";
                    $rssResponse = Http::timeout(5)->get($rssUrl);
                    
                    if ($rssResponse->successful()) {
                        $xml = simplexml_load_string($rssResponse->body());
                        if ($xml && isset($xml->channel->item)) {
                            $articles = [];
                            $count = 0;
                            foreach ($xml->channel->item as $item) {
                                if ($count >= 10) break;
                                
                                $url = (string) $item->link;
                                
                                $articles[] = [
                                    'title' => (string) $item->title,
                                    'description' => strip_tags((string) $item->description),
                                    'content' => strip_tags((string) $item->description),
                                    'source' => ['name' => (string) ($item->source ?? 'Global News')],
                                    'url' => $url,
                                    'image' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?q=80&w=600',
                                    'publishedAt' => date('Y-m-d\TH:i:s\Z', strtotime((string) $item->pubDate))
                                ];
                                $count++;
                            }
                            
                            if (!empty($articles)) {
                                return $articles;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Bing News RSS Error: " . $e->getMessage());
                    \Illuminate\Support\Facades\Cache::put('offline_api_bing', true, 600);
                }
            }

            return $this->getMockNewsData($query);
        });
    }

    /**
     * Mock Data World Bank jika koneksi API gagal
     */
    private function getMockMacroData(string $countryIso): array
    {
        $years = [2018, 2019, 2020, 2021, 2022, 2023, 2024];
        $mock = [
            'gdp' => [],
            'inflation' => [],
            'population' => [],
            'exports_gdp' => [],
            'imports_gdp' => []
        ];

        // Random values generators depending on country code
        $factor = ($countryIso === 'ID') ? 1.0 : (($countryIso === 'SG') ? 0.35 : 10.0);

        foreach ($years as $idx => $year) {
            $mock['gdp'][] = ['year' => $year, 'value' => ($factor * 1e11) * (1 + (0.05 * $idx))];
            
            // Realistic inflation rates for hyperinflation/high-inflation countries
            $inflationVal = 2.5 + (sin($idx) * 1.5);
            if ($countryIso === 'VE') {
                $inflationVal = 200.0 + ($idx * 15);
            } elseif ($countryIso === 'ZW') {
                $inflationVal = 150.0 + ($idx * 10);
            } elseif ($countryIso === 'AR') {
                $inflationVal = 140.0 + ($idx * 12);
            } elseif ($countryIso === 'SD') {
                $inflationVal = 80.0 + ($idx * 5);
            } elseif ($countryIso === 'TR') {
                $inflationVal = 55.0 + ($idx * 3);
            }
            $mock['inflation'][] = ['year' => $year, 'value' => $inflationVal];
            
            $mock['population'][] = ['year' => $year, 'value' => ($countryIso === 'ID') ? (265e6 + ($year - 2018) * 2.5e6) : (5.6e6 + ($year - 2018) * 0.1e6)];
            $mock['exports_gdp'][] = ['year' => $year, 'value' => ($countryIso === 'SG') ? 175.0 : 20.0 + (cos($idx) * 2)];
            $mock['imports_gdp'][] = ['year' => $year, 'value' => ($countryIso === 'SG') ? 145.0 : 18.0 + (sin($idx) * 2)];
        }

        return $mock;
    }

    /**
     * Mock Data berita rantai pasok dalam Bahasa Indonesia untuk fallback
     */
    private function getMockNewsData(string $query): array
    {
        return [
            [
                'title' => 'Kemacetan Pelabuhan Utama Global Berangsur Pulih, Biaya Kontainer Menurun',
                'description' => 'Laporan logistik maritim menunjukkan bahwa antrean kapal di pelabuhan utama dunia seperti Shanghai dan Los Angeles telah berkurang sebesar 15% pada kuartal ini, membawa angin segar bagi importir global.',
                'content' => 'Kemacetan di pelabuhan-pelabuhan utama dunia dilaporkan mulai membaik secara bertahap. Hal ini dipicu oleh optimalisasi rute pelayaran dan peningkatan jam kerja operasional pelabuhan. Dampaknya, biaya pengapalan logistik kontainer rute Asia-Eropa terpantau turun sebesar 8% dari bulan lalu, mengurangi beban inflasi pengapalan.',
                'source' => ['name' => 'Logistik Indonesia News'],
                'url' => 'https://example.com/logistik-pulih',
                'image' => 'https://images.unsplash.com/photo-1578575437130-527eed3abbec?q=80&w=600',
                'publishedAt' => now()->subHours(2)->toIso8601String()
            ],
            [
                'title' => 'Ketegangan Geopolitik di Selat Malaka Meningkatkan Kekhawatiran Keterlambatan Pengiriman',
                'description' => 'Peningkatan patroli militer dan latihan keamanan di wilayah Selat Malaka memaksa beberapa rute pengapalan kargo melambat, meningkatkan risiko waktu transit ekspor.',
                'content' => 'Para pelaku usaha logistik mengkhawatirkan rute perdagangan vital di Selat Malaka yang mengalami perlambatan akibat eskalasi keamanan regional. Beberapa perusahaan asuransi maritim dikabarkan menaikkan premi asuransi perang tambahan sebesar 3%, yang berpotensi memicu naiknya harga barang impor konsumen.',
                'source' => ['name' => 'Warta Ekonomi Global'],
                'url' => 'https://example.com/geopolitik-selat-malaka',
                'image' => 'https://images.unsplash.com/photo-1494412574643-ff11b0a5c1c3?q=80&w=600',
                'publishedAt' => now()->subHours(5)->toIso8601String()
            ],
            [
                'title' => 'Krisis Badai Tropis di Asia Timur Rusak Beberapa Infrastruktur Dermaga',
                'description' => 'Topan kuat yang melanda pesisir Asia Timur menyebabkan jadwal kapal terhambat dan merusak sebagian fasilitas kontainer di pelabuhan lokal.',
                'content' => 'Badai tropis dengan kecepatan angin mencapai 120 km/jam menghantam wilayah pesisir Asia Timur. Otoritas pelabuhan setempat terpaksa menghentikan layanan bongkar muat kontainer selama 48 jam demi alasan keselamatan, menyebabkan tumpukan kargo dan keterlambatan pengiriman komoditas penting.',
                'source' => ['name' => 'Antara Cuaca & Logistik'],
                'url' => 'https://example.com/badai-dermaga-rusak',
                'image' => 'https://images.unsplash.com/photo-1527030280862-64139fbe04ca?q=80&w=600',
                'publishedAt' => now()->subHours(8)->toIso8601String()
            ],
            [
                'title' => 'Laju Inflasi Global yang Stabil Memberi Kepercayaan pada Pasar Ekspor Indonesia',
                'description' => 'Negara-negara tujuan ekspor Indonesia melaporkan tingkat inflasi yang lebih terkendali di bawah 3%, meningkatkan daya beli komoditas manufaktur.',
                'content' => 'Stabilnya inflasi di beberapa negara mitra dagang utama seperti Amerika Serikat dan Jepang diprediksi akan menstimulasi ekspor non-migas Indonesia. Permintaan untuk komoditas tekstil, alas kaki, dan kelapa sawit diproyeksikan tumbuh 4,5% pada semester kedua tahun ini.',
                'source' => ['name' => 'Kabar Dagang Nasional'],
                'url' => 'https://example.com/inflasi-stabil-ekspor',
                'image' => 'https://images.unsplash.com/photo-1526304640581-d334cdbbf45e?q=80&w=600',
                'publishedAt' => now()->subHours(12)->toIso8601String()
            ],
            [
                'title' => 'Depresiasi Rupiah terhadap Dollar Menekan Biaya Impor Bahan Baku Logistik',
                'description' => 'Kenaikan nilai dollar AS terhadap mata uang Asia membuat importir lokal harus merogoh kocek lebih dalam untuk mendatangkan komponen suku cadang.',
                'content' => 'Nilai tukar rupiah yang melemah hingga menyentuh Rp 16.400 per dollar AS memberikan dampak signifikan pada neraca biaya importir lokal. Industri yang bergantung pada suku cadang impor, seperti perakitan otomotif dan elektronik, bersiap melakukan penyesuaian tarif harga produk.',
                'source' => ['name' => 'Moneter & Finansial Indonesia'],
                'url' => 'https://example.com/kurs-rupiah-melemah',
                'image' => 'https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?q=80&w=600',
                'publishedAt' => now()->subHours(24)->toIso8601String()
            ]
        ];
    }
}
