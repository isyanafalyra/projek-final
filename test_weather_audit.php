<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Country;
use App\Models\Port;
use App\Services\RiskIntelligenceService;
use App\Services\RiskCalculatorService;
use Illuminate\Support\Facades\Cache;

// Clear the Open-Meteo cache/offline flag to make sure we do a clean call
Cache::forget('offline_api_open_meteo');

$countries = Country::all();
echo "Total Countries in DB: " . $countries->count() . PHP_EOL;

$targetCodes = ['AU', 'ID', 'US'];
$targetCountries = Country::whereIn('iso_code', $targetCodes)->get();

if ($targetCountries->isEmpty()) {
    echo "No target countries found in DB! Checking existing countries:" . PHP_EOL;
    foreach ($countries as $c) {
        echo " - " . $c->name . " (ISO: " . $c->iso_code . ")" . PHP_EOL;
    }
}

$service = app(RiskIntelligenceService::class);
$calculator = app(RiskCalculatorService::class);

foreach ($targetCodes as $code) {
    echo "=========================================" . PHP_EOL;
    echo "AUDITING COUNTRY CODE: $code" . PHP_EOL;
    $country = Country::where('iso_code', $code)->first();
    if (!$country) {
        echo "Country $code not found in DB!" . PHP_EOL;
        continue;
    }

    echo "Country Name: " . $country->name . PHP_EOL;
    echo "ISO Code: " . $country->iso_code . PHP_EOL;
    
    // Coordinates lookup logic matching calculateWeatherRisk
    $lat = -6.2088;
    $lng = 106.8456;
    $source = "Default Jakarta";

    $port = Port::where('country_id', $country->id)->first();
    if ($port) {
        $lat = (float) $port->latitude;
        $lng = (float) $port->longitude;
        $source = "Port: " . $port->name;
    } elseif ($country->latitude !== null && $country->longitude !== null) {
        $lat = (float) $country->latitude;
        $lng = (float) $country->longitude;
        $source = "Country table coords";
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
        $cCode = strtoupper($country->iso_code);
        if (isset($capitalCoords[$cCode])) {
            $lat = $capitalCoords[$cCode][0];
            $lng = $capitalCoords[$cCode][1];
            $source = "Capital coordinates fallback";
        }
    }

    echo "Source Coords: $source" . PHP_EOL;
    echo "Latitude: $lat" . PHP_EOL;
    echo "Longitude: $lng" . PHP_EOL;

    // Clear cache for this specific coord to force fresh API call
    $cacheKey = "weather_data_lat_lng_{$lat}_{$lng}";
    Cache::forget($cacheKey);

    // Weather API call
    $url = "https://api.open-meteo.com/v1/forecast?latitude=$lat&longitude=$lng&current=temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,rain,showers,snowfall,weather_code,wind_speed_10m,wind_gusts_10m&timezone=auto";
    echo "Weather API request URL: $url" . PHP_EOL;

    try {
        $response = Http::timeout(5)->get($url);
        echo "HTTP Response Status: " . $response->status() . PHP_EOL;
        
        if ($response->successful()) {
            $weatherData = $response->json();
            $current = $weatherData['current'] ?? null;
            if ($current) {
                echo "Temperature: " . ($current['temperature_2m'] ?? 'N/A') . " C" . PHP_EOL;
                echo "Wind Speed: " . ($current['wind_speed_10m'] ?? 'N/A') . " km/h" . PHP_EOL;
                echo "Precipitation: " . ($current['precipitation'] ?? 'N/A') . " mm" . PHP_EOL;
                echo "Weather Code: " . ($current['weather_code'] ?? 'N/A') . PHP_EOL;
            } else {
                echo "No 'current' key in weather data!" . PHP_EOL;
                print_r($weatherData);
            }
        } else {
            echo "API request failed with body: " . $response->body() . PHP_EOL;
        }
    } catch (\Exception $e) {
        echo "Exception during API call: " . $e->getMessage() . PHP_EOL;
    }

    // Now let's calculate risk score
    $riskDetails = $calculator->calculateCountryRisk($country);
    echo "Calculated weather risk score: " . $riskDetails['scores']['weather'] . PHP_EOL;
}
