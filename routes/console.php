<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('risk:calculate-all', function (\App\Services\RiskCalculatorService $calculator) {
    $this->info("Starting risk calculation for all countries...");
    $countries = \App\Models\Country::all();
    $total = $countries->count();
    $this->output->progressStart($total);

    $successCount = 0;
    $errorCount = 0;

    foreach ($countries as $country) {
        try {
            $calculator->calculateCountryRisk($country);
            $successCount++;
        } catch (\Exception $e) {
            $errorCount++;
            \Illuminate\Support\Facades\Log::error("Failed to calculate risk for {$country->name} ({$country->iso_code}): " . $e->getMessage());
        }
        $this->output->progressAdvance();
    }

    $this->output->progressFinish();
    $this->info("Risk calculation completed. Success: {$successCount}, Failed: {$errorCount}.");
})->purpose('Calculate risk scores for all countries');
