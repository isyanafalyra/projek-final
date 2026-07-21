<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Country;
use App\Models\RiskScore;
use App\Services\RiskCalculatorService;

class SyncRiskScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-risk-scores';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize initial risk scores for all countries that do not have one yet';

    /**
     * Execute the console command.
     */
    public function handle(RiskCalculatorService $riskCalculator)
    {
        $this->info('Starting risk score synchronization...');

        $countries = Country::all();
        $syncedCount = 0;

        foreach ($countries as $country) {
            $hasScore = RiskScore::where('country_id', $country->id)->exists();

            if (!$hasScore) {
                try {
                    $riskCalculator->calculateCountryRisk($country);
                    $syncedCount++;
                    $this->line("Calculated risk for {$country->name} ({$country->iso_code})");
                } catch (\Exception $e) {
                    $this->error("Failed to calculate for {$country->name}: " . $e->getMessage());
                }
            }
        }

        $this->info("Finished! Successfully synchronized risk scores for {$syncedCount} countries.");
    }
}
