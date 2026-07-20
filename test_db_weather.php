<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\RiskScore;
use App\Models\Country;

$nonZeroScores = RiskScore::where('weather_score', '>', 0)->get();
echo "Total RiskScore records: " . RiskScore::count() . PHP_EOL;
echo "Total RiskScore records with weather_score > 0: " . $nonZeroScores->count() . PHP_EOL;

if ($nonZeroScores->count() > 0) {
    echo "Sample countries with non-zero weather scores:" . PHP_EOL;
    foreach ($nonZeroScores->take(10) as $scoreRecord) {
        $country = Country::find($scoreRecord->country_id);
        echo " - " . ($country ? $country->name : "Unknown") . ": Weather Score = " . $scoreRecord->weather_score . PHP_EOL;
    }
} else {
    echo "All calculated weather scores in the DB are indeed 0." . PHP_EOL;
}
