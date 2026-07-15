<?php

namespace App\Services;

use App\Models\Country;
use App\Models\RiskScore;
use App\Models\Port;
use Illuminate\Support\Facades\Log;

class RiskCalculatorService
{
    protected $apiService;
    protected $sentimentService;

    public function __construct(RiskIntelligenceService $apiService, SentimentAnalysisService $sentimentService)
    {
        $this->apiService = $apiService;
        $this->sentimentService = $sentimentService;
    }

    /**
     * Menghitung total skor risiko (0 - 100) untuk suatu negara.
     * Secara otomatis menyimpan hasilnya ke dalam tabel `risk_scores` dan mengembalikan detail data.
     */
    public function calculateCountryRisk(Country $country): array
    {
        // 1. Weather Risk (Bobot 30%)
        $weatherScore = $this->calculateWeatherRisk($country);

        // 2. Inflation Risk (Bobot 20%)
        $inflationScore = $this->calculateInflationRisk($country);

        // 3. Political/News Risk (Bobot 40%)
        $politicalScore = $this->calculatePoliticalRisk($country);

        // 4. Currency Risk (Bobot 10%)
        $currencyScore = $this->calculateCurrencyRisk($country);

        // Calculate Weighted Total Score
        $totalScore = ($weatherScore * 0.3) + ($inflationScore * 0.2) + ($politicalScore * 0.4) + ($currencyScore * 0.1);
        $totalScore = round($totalScore, 2);

        // Determine Risk Level
        if ($totalScore < 30) {
            $riskLevel = 'Low Risk';
        } elseif ($totalScore <= 60) {
            $riskLevel = 'Medium Risk';
        } else {
            $riskLevel = 'High Risk';
        }

        // Save to database log
        try {
            RiskScore::create([
                'country_id' => $country->id,
                'weather_score' => $weatherScore,
                'inflation_score' => $inflationScore,
                'political_score' => $politicalScore,
                'currency_score' => $currencyScore,
                'total_score' => $totalScore,
                'risk_level' => $riskLevel,
                'calculated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to save risk score log: " . $e->getMessage());
        }

        return [
            'country_id' => $country->id,
            'country_name' => $country->name,
            'country_code' => $country->iso_code,
            'scores' => [
                'weather' => $weatherScore,
                'inflation' => $inflationScore,
                'political' => $politicalScore,
                'currency' => $currencyScore,
                'total' => $totalScore,
            ],
            'risk_level' => $riskLevel,
            'calculated_at' => now()->toIso8601String()
        ];
    }

    /**
     * Perhitungan Weather Risk (30%)
     */
    private function calculateWeatherRisk(Country $country): float
    {
        // Temukan koordinat (prioritas: pelabuhan utama milik negara tersebut, atau koordinat default ibukota)
        $lat = -6.2088; // Default Jakarta
        $lng = 106.8456;

        $port = Port::where('country_id', $country->id)->first();
        if ($port) {
            $lat = (float) $port->latitude;
            $lng = (float) $port->longitude;
        } elseif ($country->latitude !== null && $country->longitude !== null) {
            $lat = (float) $country->latitude;
            $lng = (float) $country->longitude;
        } else {
            $capitalCoords = [
                'ID' => [-6.2088, 106.8456],
                'SG' => [1.3521, 103.8198],
                'CN' => [39.9042, 116.4074],
                'US' => [38.9072, -77.0369],
                'NL' => [52.3676, 4.9041],
                'JP' => [35.6762, 139.6503],
                'DE' => [52.5200, 13.4050],
                'AU' => [-35.2809, 149.1300],
                'GB' => [51.5074, -0.1278],
                'IN' => [28.6139, 77.2090],
                'MY' => [3.1390, 101.6869],
                'KR' => [37.5665, 126.9780],
            ];
            $code = strtoupper($country->iso_code);
            if (isset($capitalCoords[$code])) {
                $lat = $capitalCoords[$code][0];
                $lng = $capitalCoords[$code][1];
            }
        }

        $weather = $this->apiService->getWeatherData($lat, $lng);
        if (!isset($weather['current'])) {
            return 30.0; // Default Neutral Risk
        }

        $current = $weather['current'];
        $score = 0.0;

        // 1. Curah Hujan (Precipitation) - Max 40 poin
        // 0-2mm: 0 poin, 2-10mm: sedang (20 poin), >10mm: tinggi (40 poin)
        $precip = $current['precipitation'] ?? 0.0;
        if ($precip > 10.0) {
            $score += 40;
        } elseif ($precip > 2.0) {
            $score += 20;
        } elseif ($precip > 0.0) {
            $score += 5;
        }

        // 2. Kecepatan Angin (Wind Speed) - Max 30 poin
        // Kecepatan angin dalam km/h: >40 km/h: badai besar (30 poin), >20 km/h: sedang (15 poin)
        $wind = $current['wind_speed_10m'] ?? 0.0;
        if ($wind > 40.0) {
            $score += 30;
        } elseif ($wind > 20.0) {
            $score += 15;
        } elseif ($wind > 10.0) {
            $score += 5;
        }

        // 3. Suhu Ekstrem (Temperature) - Max 15 poin
        // Sangat dingin (< 5C) atau sangat panas (> 38C) menambah kompleksitas logistik kontainer
        $temp = $current['temperature_2m'] ?? 25.0;
        if ($temp < 5.0 || $temp > 38.0) {
            $score += 15;
        }

        // 4. Weather Code (Kondisi Cuaca Buruk / Badai) - Max 15 poin
        // WMO codes: 95-99 adalah Thunderstorm, 71-86 salju lebat, 51-67 gerimis/hujan lebat berkelanjutan
        $code = $current['weather_code'] ?? 0;
        if (in_array($code, [95, 96, 99])) {
            $score += 15; // Thunderstorm / Badai petir
        } elseif (in_array($code, [65, 67, 82, 86])) {
            $score += 10; // Hujan/salju lebat
        }

        return min($score, 100.0);
    }

    /**
     * Perhitungan Inflation Risk (20%)
     */
    private function calculateInflationRisk(Country $country): float
    {
        $macro = $this->apiService->getMacroData($country->iso_code);
        if (!isset($macro['inflation']) || empty($macro['inflation'])) {
            return 25.0; // Default Neutral Risk
        }

        // Ambil data tahun terakhir
        $latestInflation = end($macro['inflation']);
        $rate = (float) $latestInflation['value'];

        // Ideal inflasi target bank sentral umumnya 1.5% s.d 3.0%
        if ($rate < 0.0) {
            // Deflasi/Resesi: Risiko menengah-tinggi
            return min(abs($rate) * 15, 100.0);
        } elseif ($rate <= 3.0 && $rate >= 1.0) {
            // Sehat: Risiko sangat rendah
            return 10.0;
        } else {
            // Inflasi tinggi: Risiko meningkat
            // Contoh: inflasi 5% -> (5 - 3) * 10 = 20 poin. inflasi 12% -> (12 - 3) * 10 = 90 poin.
            return min(($rate - 3.0) * 10 + 10, 100.0);
        }
    }

    /**
     * Perhitungan Political/News Risk (40%)
     */
    private function calculatePoliticalRisk(Country $country): float
    {
        // Ambil berita logistik global
        $articles = $this->apiService->getNewsData();
        if (empty($articles)) {
            return 30.0; // Default
        }

        $scores = [];
        $countryName = strtolower($country->name);
        $countryCode = strtolower($country->iso_code);

        foreach ($articles as $article) {
            $title = $article['title'] ?? '';
            $desc = $article['description'] ?? '';
            $text = $title . ' ' . $desc;

            $analysis = $this->sentimentService->analyze($text);
            
            // Cek apakah berita mengandung nama negara atau kode negara tersebut
            if (stripos($text, $countryName) !== false || stripos($text, $countryCode) !== false) {
                // Bobot ganda untuk berita yang spesifik menyebutkan negara terkait
                $scores[] = $analysis['score'];
                $scores[] = $analysis['score'];
            } else {
                // Bobot biasa sebagai sentimen latar belakang logistik global
                $scores[] = $analysis['score'];
            }
        }

        if (empty($scores)) {
            return 30.0;
        }

        $avgSentiment = array_sum($scores) / count($scores);

        // Rumus Pemetaan:
        // Sentiment range: -100 (Sangat Negatif) s.d +100 (Sangat Positif)
        // Risk score range: 100 (Risiko Maksimum) s.d 0 (Risiko Minimum)
        // Rumus: (100 - avgSentiment) / 2
        $riskScore = (100 - $avgSentiment) / 2;

        return min(max($riskScore, 0.0), 100.0);
    }

    /**
     * Perhitungan Currency Risk (10%)
     */
    private function calculateCurrencyRisk(Country $country): float
    {
        $currencyCode = strtoupper($country->currency_code);

        // Jika mata uang dasarnya adalah USD, risikonya sangat rendah (karena perdagangan internasional memakai USD)
        if ($currencyCode === 'USD') {
            return 10.0;
        }

        $rates = $this->apiService->getExchangeRates('USD');
        if (empty($rates) || !isset($rates[$currencyCode])) {
            return 30.0; // Default
        }

        // Tentukan volatilitas standar mata uang berdasarkan kode mata uangnya
        // Ditambah dengan fluktuasi drift kecil berdasarkan rate harian agar dinamis
        $baseRisks = [
            'EUR' => 15.0, // Sangat stabil
            'SGD' => 15.0, // Sangat stabil
            'CNY' => 20.0, // Stabil dikontrol ketat
            'JPY' => 45.0, // Volatilitas tinggi baru-baru ini
            'GBP' => 20.0, // Cukup stabil
            'AUD' => 25.0, // Stabil komoditas
            'INR' => 35.0, // Sedang
            'MYR' => 35.0, // Sedang
            'IDR' => 40.0, // Sedang-tinggi
            'KRW' => 35.0, // Sedang
        ];

        $baseRisk = $baseRisks[$currencyCode] ?? 30.0;

        // Modifikasi kecil berdasarkan nilai rate saat ini agar terasa real-time
        $rateValue = (float) $rates[$currencyCode];
        // Menggunakan digit terakhir dari rate sebagai drift generator agar aman & konsisten
        $drift = fmod($rateValue, 5.0) - 2.5; 

        return min(max($baseRisk + $drift, 0.0), 100.0);
    }
}
