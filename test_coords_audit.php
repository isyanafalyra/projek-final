<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Country;
use App\Models\Port;

$countries = Country::all();
$totalCountries = $countries->count();
$countriesWithPorts = 0;
$countriesWithNullCoords = 0;
$countriesNotInCapitalList = 0;

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

echo "Total Countries: $totalCountries" . PHP_EOL;

foreach ($countries as $c) {
    $hasPort = Port::where('country_id', $c->id)->exists();
    if ($hasPort) {
        $countriesWithPorts++;
    }
    
    $hasTableCoords = ($c->latitude !== null && $c->longitude !== null);
    if (!$hasTableCoords) {
        $countriesWithNullCoords++;
    }
    
    $inCapitalList = isset($capitalCoords[strtoupper($c->iso_code)]);
    if (!$inCapitalList) {
        $countriesNotInCapitalList++;
    }
    
    if (!$hasPort && !$hasTableCoords && !$inCapitalList) {
        // This country falls back to Jakarta coordinates!
        echo " -> Falls back to Jakarta: " . $c->name . " (" . $c->iso_code . ")" . PHP_EOL;
    }
}

echo PHP_EOL;
echo "Countries with ports: $countriesWithPorts" . PHP_EOL;
echo "Countries with null lat/lng in DB table: $countriesWithNullCoords" . PHP_EOL;
echo "Countries not in hardcoded capital coords list: $countriesNotInCapitalList" . PHP_EOL;
