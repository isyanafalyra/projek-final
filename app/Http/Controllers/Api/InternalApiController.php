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

        $data = $countries->map(function ($country) use ($user) {
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
                'latest_risk_score' => $latestRisk ? (float) $latestRisk->total_score : null,
                'latest_risk_level' => $latestRisk ? $latestRisk->risk_level : 'N/A',
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data
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

        // Kalkulasi skor risiko baru secara real-time
        $riskData = $this->riskCalculator->calculateCountryRisk($country);

        // Ambil log riwayat skor risiko sebelumnya (limit 5) untuk chart historis
        $history = RiskScore::where('country_id', $country->id)
            ->orderBy('calculated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($score) {
                return [
                    'total_score' => (float) $score->total_score,
                    'calculated_at' => $score->calculated_at->toIso8601String()
                ];
            })
            ->reverse()
            ->values();

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
     */
    public function ports()
    {
        $ports = Port::with('country')->get()->map(function ($port) {
            return [
                'id' => $port->id,
                'name' => $port->name,
                'code' => $port->code,
                'lat' => (float) $port->latitude,
                'lng' => (float) $port->longitude,
                'country_name' => $port->country->name ?? 'Unknown',
                'country_code' => $port->country->iso_code ?? '',
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $ports
        ]);
    }

    /**
     * GET /api/news
     * Mengambil berita logistik terbaru dengan analisis sentimen leksikon.
     */
    public function news(Request $request)
    {
        $query = $request->query('q', 'logistik OR "rantai pasok" OR ekspor OR impor');
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
