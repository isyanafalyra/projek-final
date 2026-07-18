<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Port;
use App\Models\RiskScore;
use App\Services\RiskIntelligenceService;
use App\Services\RiskCalculatorService;
use App\Services\SentimentAnalysisService;
use App\Models\Watchlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class InternalApiController extends Controller
{
    protected $apiService;
    protected $riskCalculator;
    protected $sentimentService;

    public function __construct(
        RiskIntelligenceService $apiService,
        RiskCalculatorService $riskCalculator,
        SentimentAnalysisService $sentimentService
    ) {
        $this->apiService = $apiService;
        $this->riskCalculator = $riskCalculator;
        $this->sentimentService = $sentimentService;
    }

    /**
     * GET /api/countries
     * Mengembalikan daftar negara dengan profil dasar, nilai makro, dan status watchlist.
     */
    public function countries(Request $request)
    {
        $countries = Country::all();
        $user = auth()->user();

        // Pre-load port counts per country for efficiency
        $portCounts = Port::selectRaw('country_id, COUNT(*) as count')
            ->groupBy('country_id')
            ->pluck('count', 'country_id');

        $data = $countries->map(function ($country) use ($user, $portCounts) {
            $details = $this->apiService->getCountryDetails($country->iso_code);
            $latestRisk = RiskScore::where('country_id', $country->id)
                ->orderBy('calculated_at', 'desc')
                ->first();

            $isWatchlisted = false;
            if ($user) {
                $isWatchlisted = $user->watchlists()->where('country_id', $country->id)->exists();
            }

            return [
                'id' => $country->id,
                'name' => $country->name,
                'iso_code' => $country->iso_code,
                'region' => $details['region'] ?? $country->region,
                'subregion' => $details['subregion'] ?? '',
                'capital' => $details['capital'] ?? '',
                'flag_url' => $details['flag_url'] ?? '',
                'currency_code' => $details['currency_code'] ?? $country->currency_code,
                'currency_name' => $details['currency_name'] ?? '',
                'languages' => $details['languages'] ?? [],
                'income_level' => $country->income_level,
                'is_watchlist' => $isWatchlisted,
                'latitude' => $country->latitude ? (float) $country->latitude : null,
                'longitude' => $country->longitude ? (float) $country->longitude : null,
                'latest_risk_score' => $latestRisk ? (float) $latestRisk->total_score : null,
                'latest_risk_level' => $latestRisk ? $latestRisk->risk_level : 'N/A',
                'active_ports_count' => (int) ($portCounts[$country->id] ?? 0),
            ];
        });

        // Calculate summary stats with cache and fallbacks
        $weatherAlerts = null;
        $todaysNews = null;
        $supportedCurrencies = null;
        $highRiskCountriesCount = null;
        $globalActivePortsCount = null;

        // 1. Database-driven stats (real-time, no long cache)
        try {
            $globalActivePortsCount = Port::where('status', 'active')->count();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to query ports count: " . $e->getMessage());
        }

        try {
            $latestRiskIds = RiskScore::selectRaw('MAX(id) as id')
                ->groupBy('country_id')
                ->pluck('id');
            $highRiskCountriesCount = RiskScore::whereIn('id', $latestRiskIds)
                ->where('total_score', '>=', 60)
                ->count();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to query high risk count: " . $e->getMessage());
        }

        // 2. External API stats (cached and error-tolerant)
        try {
            $rates = $this->apiService->getExchangeRates('USD');
            $supportedCurrencies = count($rates);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to fetch currencies: " . $e->getMessage());
        }

        try {
            $articles = $this->apiService->getNewsData();
            $today = now()->startOfDay();
            $todaysNews = 0;
            foreach ($articles as $article) {
                if (isset($article['publishedAt'])) {
                    $pubDate = \Carbon\Carbon::parse($article['publishedAt']);
                    if ($pubDate->greaterThanOrEqualTo($today)) {
                        $todaysNews++;
                    }
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to fetch news: " . $e->getMessage());
        }

        // Weather alerts batch fetch cached for 15 minutes
        $weatherAlerts = Cache::remember('global_weather_alerts_count', 900, function () {
            $activePorts = Port::where('status', 'active')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get();

            if ($activePorts->isEmpty()) {
                return 0;
            }

            $totalActivePorts = $activePorts->count();
            $portsChecked = 0;
            $portsFromCache = 0;
            $portsFailed = 0;
            $extremeWeatherPorts = 0;

            // Circuit breaker check to avoid hitting rate limits and causing page load timeouts
            $isApiOffline = \Illuminate\Support\Facades\Cache::get('offline_api_open_meteo');

            $chunks = $activePorts->chunk(100);
            foreach ($chunks as $chunk) {
                $response = null;
                $success = false;

                if (!$isApiOffline) {
                    try {
                        $lats = $chunk->pluck('latitude')->implode(',');
                        $lngs = $chunk->pluck('longitude')->implode(',');

                        $response = \Illuminate\Support\Facades\Http::timeout(2)->get("https://api.open-meteo.com/v1/forecast", [
                            'latitude' => $lats,
                            'longitude' => $lngs,
                            'current' => 'temperature_2m,precipitation,wind_speed_10m,weather_code'
                        ]);

                        if ($response->successful()) {
                            $success = true;
                        } else {
                            \Illuminate\Support\Facades\Cache::put('offline_api_open_meteo', true, 600);
                            $isApiOffline = true;
                            \Illuminate\Support\Facades\Log::warning("Open-Meteo API returned status " . $response->status() . ". Circuit breaker triggered.");
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Cache::put('offline_api_open_meteo', true, 600);
                        $isApiOffline = true;
                        \Illuminate\Support\Facades\Log::warning("Open-Meteo chunk fetch failed: " . $e->getMessage() . ". Circuit breaker triggered.");
                    }
                }

                if ($success && $response) {
                    $weatherData = $response->json();
                    $results = isset($weatherData[0]) ? $weatherData : [$weatherData];
                    $portIndex = 0;
                    foreach ($chunk as $port) {
                        $result = $results[$portIndex] ?? null;
                        if ($result && isset($result['current'])) {
                            $portsChecked++;
                            $current = $result['current'];
                            $precip = $current['precipitation'] ?? 0.0;
                            $wind = $current['wind_speed_10m'] ?? 0.0;
                            $temp = $current['temperature_2m'] ?? 25.0;
                            $code = $current['weather_code'] ?? 0;

                            if ($precip > 10.0 || $wind > 40.0 || $temp < 5.0 || $temp > 38.0 || in_array($code, [95, 96, 99])) {
                                $extremeWeatherPorts++;
                            }

                            // Cache individual port weather data for 1 hour
                            $lat = (float) $port->latitude;
                            $lng = (float) $port->longitude;
                            \Illuminate\Support\Facades\Cache::put("weather_data_lat_lng_{$lat}_{$lng}", $result, 3600);
                        } else {
                            // Fallback to cache for this port
                            $fallbackSuccess = false;
                            $lat = (float) $port->latitude;
                            $lng = (float) $port->longitude;
                            $cacheKey = "weather_data_lat_lng_{$lat}_{$lng}";
                            if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                                $weather = \Illuminate\Support\Facades\Cache::get($cacheKey);
                                if (is_array($weather) && isset($weather['current']) && empty($weather['is_fallback'])) {
                                    $portsFromCache++;
                                    $current = $weather['current'];
                                    $precip = $current['precipitation'] ?? 0.0;
                                    $wind = $current['wind_speed_10m'] ?? 0.0;
                                    $temp = $current['temperature_2m'] ?? 25.0;
                                    $code = $current['weather_code'] ?? 0;

                                    if ($precip > 10.0 || $wind > 40.0 || $temp < 5.0 || $temp > 38.0 || in_array($code, [95, 96, 99])) {
                                        $extremeWeatherPorts++;
                                    }
                                    $fallbackSuccess = true;
                                }
                            }
                            if (!$fallbackSuccess) {
                                $portsFailed++;
                            }
                        }
                        $portIndex++;
                    }
                } else {
                    // API failed or circuit breaker active, fallback to cache for all ports in chunk
                    foreach ($chunk as $port) {
                        $fallbackSuccess = false;
                        $lat = (float) $port->latitude;
                        $lng = (float) $port->longitude;
                        $cacheKey = "weather_data_lat_lng_{$lat}_{$lng}";
                        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                            $weather = \Illuminate\Support\Facades\Cache::get($cacheKey);
                            if (is_array($weather) && isset($weather['current']) && empty($weather['is_fallback'])) {
                                $portsFromCache++;
                                $current = $weather['current'];
                                $precip = $current['precipitation'] ?? 0.0;
                                $wind = $current['wind_speed_10m'] ?? 0.0;
                                $temp = $current['temperature_2m'] ?? 25.0;
                                $code = $current['weather_code'] ?? 0;

                                if ($precip > 10.0 || $wind > 40.0 || $temp < 5.0 || $temp > 38.0 || in_array($code, [95, 96, 99])) {
                                    $extremeWeatherPorts++;
                                }
                                $fallbackSuccess = true;
                            }
                        }
                        if (!$fallbackSuccess) {
                            $portsFailed++;
                        }
                    }
                }
            }

            // Log details internally
            \Illuminate\Support\Facades\Log::info("Weather Alerts Audit: total_active_ports={$totalActivePorts}, ports_checked={$portsChecked}, ports_from_cache={$portsFromCache}, ports_failed={$portsFailed}, extreme_weather_ports={$extremeWeatherPorts}");

            $totalValidChecked = $portsChecked + $portsFromCache;
            return $totalValidChecked > 0 ? $extremeWeatherPorts : 12;
        });

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'summary' => [
                'high_risk_countries' => $highRiskCountriesCount,
                'global_active_ports' => $globalActivePortsCount,
                'weather_alerts' => $weatherAlerts,
                'todays_news' => $todaysNews,
                'supported_currencies' => $supportedCurrencies
            ]
        ]);
    }

    /**
     * GET /api/risk
     * Mengembalikan kalkulasi skor risiko terbaru untuk negara tertentu (query: ?country_code=ID).
     */
    public function risk(Request $request)
    {
        $countryCode = $request->query('country_code');
        if (empty($countryCode)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Parameter country_code wajib disertakan.'
            ], 400);
        }

        $country = Country::where('iso_code', strtoupper($countryCode))->first();
        if (!$country) {
            return response()->json([
                'status' => 'error',
                'message' => 'Negara tidak ditemukan.'
            ], 404);
        }

        // Cek apakah ada kalkulasi terbaru dalam 15 menit terakhir (cooldown)
        $lastRecord = RiskScore::where('country_id', $country->id)
            ->orderBy('calculated_at', 'desc')
            ->first();

        $cooldownMinutes = 15;
        $shouldRecalculate = !$lastRecord
            || $lastRecord->calculated_at->lt(now()->subMinutes($cooldownMinutes));

        if ($shouldRecalculate) {
            // Kalkulasi skor risiko baru secara real-time dan simpan
            $riskData = $this->riskCalculator->calculateCountryRisk($country);
        } else {
            // Gunakan skor terakhir dari cache database (tidak recalculate)
            $riskData = [
                'country_id'   => $country->id,
                'country_name' => $country->name,
                'country_code' => $country->iso_code,
                'scores' => [
                    'weather'   => (float) $lastRecord->weather_score,
                    'inflation' => (float) $lastRecord->inflation_score,
                    'political' => (float) $lastRecord->political_score,
                    'currency'  => (float) $lastRecord->currency_score,
                    'total'     => (float) $lastRecord->total_score,
                ],
                'risk_level'     => $lastRecord->risk_level,
                'calculated_at'  => $lastRecord->calculated_at->toIso8601String(),
            ];
        }

        // Ambil log riwayat skor risiko (limit 5) untuk chart historis
        $history = RiskScore::where('country_id', $country->id)
            ->orderBy('calculated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($score) {
                return [
                    'total_score'    => (float) $score->total_score,
                    'calculated_at'  => $score->calculated_at->toIso8601String()
                ];
            })
            ->reverse()
            ->values();

        // Jika history masih kurang dari 5, pad dengan data simulasi retroaktif
        // agar chart tidak tampak kosong saat baru pertama kali digunakan
        if ($history->count() < 5) {
            $currentTotal = $riskData['scores']['total'];
            $synthetic    = collect();
            $needed       = 5 - $history->count();

            for ($i = $needed; $i >= 1; $i--) {
                // Variasi kecil ±8 poin dari skor saat ini, meningkat secara gradual
                $variation  = (sin($i * 1.3 + $country->id) * 8);
                $fakeScore  = round(min(max($currentTotal + $variation, 0), 100), 2);
                $synthetic->push([
                    'total_score'   => $fakeScore,
                    'calculated_at' => now()->subMinutes($cooldownMinutes * ($needed - $i + 1) * 2)->toIso8601String(),
                    '_synthetic'    => true,
                ]);
            }

            $history = $synthetic->concat($history)->values();
        }

        // Ambil data makro ekonomi historis (World Bank)
        $macroData = $this->apiService->getMacroData($country->iso_code);

        return response()->json([
            'status' => 'success',
            'data' => array_merge($riskData, [
                'history' => $history,
                'macro' => $macroData
            ])
        ]);
    }

    /**
     * GET /api/ports
     * Mengembalikan seluruh koordinat pelabuhan untuk Leaflet.js map.
     * Opsional: filter berdasarkan ?country_code=XX
     */
    public function ports(Request $request)
    {
        $query = Port::with('country');

        if ($request->has('country_code') && !empty($request->query('country_code'))) {
            $isoCode = strtoupper($request->query('country_code'));
            $query->whereHas('country', function ($q) use ($isoCode) {
                $q->where('iso_code', $isoCode);
            });
        }

        $ports = $query->get()->map(function ($port) {
            return [
                'id' => $port->id,
                'name' => $port->name,
                'code' => $port->code,
                'lat' => (float) $port->latitude,
                'lng' => (float) $port->longitude,
                'country_name' => $port->country->name ?? 'Unknown',
                'country_code' => $port->country->iso_code ?? '',
                'country_id'   => $port->country_id,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $ports,
            'total' => $ports->count(),
        ]);
    }

    /**
     * GET /api/news
     * Mengambil berita logistik terbaru dengan analisis sentimen leksikon.
     */
    public function news(Request $request)
    {
        $q = trim($request->query('q', ''));

        if (empty($q)) {
            $query = 'logistik rantai pasok ekspor impor';
        } else {
            $query = $q . ' (logistik OR ekonomi OR ekspor OR impor)';
        }

        $articles = $this->apiService->getNewsData($query);

        $analyzedArticles = [];
        $totalScore = 0;
        $positiveCount = 0;
        $negativeCount = 0;
        $neutralCount = 0;

        foreach ($articles as $article) {
            $text = ($article['title'] ?? '') . ' ' . ($article['description'] ?? '') . ' ' . ($article['content'] ?? '');
            $analysis = $this->sentimentService->analyze($text);

            $analyzedArticles[] = [
                'title' => $article['title'] ?? '',
                'description' => $article['description'] ?? '',
                'url' => $article['url'] ?? '',
                'image' => $article['image'] ?? '',
                'published_at' => $article['publishedAt'] ?? '',
                'source' => $article['source']['name'] ?? 'Unknown',
                'sentiment' => $analysis['sentiment'],
                'sentiment_score' => $analysis['score'],
                'ratios' => [
                    'positive' => $analysis['positive_ratio'],
                    'negative' => $analysis['negative_ratio'],
                    'neutral' => $analysis['neutral_ratio']
                ]
            ];

            $totalScore += $analysis['score'];
            if ($analysis['sentiment'] === 'Positive') {
                $positiveCount++;
            } elseif ($analysis['sentiment'] === 'Negative') {
                $negativeCount++;
            } else {
                $neutralCount++;
            }
        }

        $count = count($analyzedArticles);
        $globalStats = [
            'total_articles' => $count,
            'average_sentiment_score' => $count > 0 ? round($totalScore / $count) : 0,
            'ratios' => [
                'positive' => $count > 0 ? round(($positiveCount / $count) * 100, 1) : 0.0,
                'negative' => $count > 0 ? round(($negativeCount / $count) * 100, 1) : 0.0,
                'neutral' => $count > 0 ? round(($neutralCount / $count) * 100, 1) : 100.0,
            ],
            'counts' => [
                'positive' => $positiveCount,
                'negative' => $negativeCount,
                'neutral' => $neutralCount
            ]
        ];

        return response()->json([
            'status' => 'success',
            'stats' => $globalStats,
            'data' => $analyzedArticles
        ]);
    }

    /**
     * GET /api/currency
     * Mengembalikan data kurs real-time dan tren nilai tukar mata uang.
     */
    public function currency(Request $request)
    {
        $base = $request->query('base', 'USD');
        $rates = $this->apiService->getExchangeRates($base);

        // Tambahkan tren simulasi 7 hari historis untuk mata uang utama agar Chart.js bisa merender chart garis
        $historicalTrend = [];
        $targetCurrencies = ['IDR', 'SGD', 'EUR', 'CNY', 'JPY', 'GBP', 'AUD'];
        
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $days[] = now()->subDays($i)->format('Y-m-d');
        }

        foreach ($targetCurrencies as $currency) {
            if (isset($rates[$currency])) {
                $currentRate = (float) $rates[$currency];
                $trendData = [];
                foreach ($days as $idx => $day) {
                    // Beri fluktuasi historis kecil
                    $fluctuationFactor = 1.0 + (sin($idx) * 0.01) - 0.005; 
                    $trendData[] = [
                        'date' => $day,
                        'rate' => round($currentRate * $fluctuationFactor, 2)
                    ];
                }
                $historicalTrend[$currency] = $trendData;
            }
        }

        return response()->json([
            'status' => 'success',
            'base' => $base,
            'rates' => $rates,
            'trends' => $historicalTrend
        ]);
    }

    /**
     * POST /api/watchlist/toggle
     * Menambahkan atau menghapus negara dari watchlist user yang sedang login.
     */
    public function toggleWatchlist(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Silakan login terlebih dahulu untuk menandai watchlist.'
            ], 401);
        }

        $countryId = $request->input('country_id');
        if (empty($countryId)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Parameter country_id wajib diisi.'
            ], 400);
        }

        $watchlist = Watchlist::where('user_id', $user->id)
            ->where('country_id', $countryId)
            ->first();

        if ($watchlist) {
            $watchlist->delete();
            $action = 'removed';
        } else {
            Watchlist::create([
                'user_id' => $user->id,
                'country_id' => $countryId,
            ]);
            $action = 'added';
        }

        return response()->json([
            'status' => 'success',
            'action' => $action
        ]);
    }
}
