<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $seededCount = 0;

        try {
            // 1. Fetch REST Countries API to build currency mapping
            $currencyResponse = Http::timeout(15)->get('https://restcountries.com/v3.1/all');
            $currencyMap = [];
            if ($currencyResponse->successful()) {
                $restData = $currencyResponse->json();
                foreach ($restData as $c) {
                    $cca2 = $c['cca2'] ?? null;
                    if ($cca2 && isset($c['currencies'])) {
                        $keys = array_keys($c['currencies']);
                        $currencyMap[strtoupper($cca2)] = $keys[0] ?? 'USD';
                    }
                }
            }

            // 2. Fetch World Bank API for basic country profiles and coordinates
            $wbResponse = Http::timeout(15)->get('http://api.worldbank.org/v2/country?format=json&per_page=300');
            if ($wbResponse->successful()) {
                $wbData = $wbResponse->json();
                $countriesList = $wbData[1] ?? [];

                foreach ($countriesList as $c) {
                    $isoCode = strtoupper($c['iso2Code'] ?? '');
                    $name = $c['name'] ?? '';
                    $region = $c['region']['value'] ?? '';
                    $incomeLevel = $c['incomeLevel']['value'] ?? 'High income';
                    $lat = !empty($c['latitude']) ? (float) $c['latitude'] : null;
                    $lng = !empty($c['longitude']) ? (float) $c['longitude'] : null;

                    // Skip aggregates or empty codes
                    if (empty($isoCode) || $region === 'Aggregates' || empty($name)) {
                        continue;
                    }

                    $currencyCode = $currencyMap[$isoCode] ?? 'USD';

                    Country::updateOrCreate(
                        ['iso_code' => $isoCode],
                        [
                            'name' => $name,
                            'region' => $region,
                            'currency_code' => $currencyCode,
                            'income_level' => $incomeLevel,
                            'latitude' => $lat,
                            'longitude' => $lng,
                        ]
                    );
                    $seededCount++;
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to seed countries dynamically: " . $e->getMessage());
        }

        // If dynamic seeding failed or fetched no countries, fall back to our premium static list
        if ($seededCount === 0) {
            $fallbackCountries = [
                [
                    'name' => 'Indonesia',
                    'iso_code' => 'ID',
                    'region' => 'East Asia & Pacific',
                    'currency_code' => 'IDR',
                    'income_level' => 'Upper middle income',
                    'latitude' => -2.5489,
                    'longitude' => 118.0149
                ],
                [
                    'name' => 'Singapore',
                    'iso_code' => 'SG',
                    'region' => 'East Asia & Pacific',
                    'currency_code' => 'SGD',
                    'income_level' => 'High income',
                    'latitude' => 1.3521,
                    'longitude' => 103.8198
                ],
                [
                    'name' => 'China',
                    'iso_code' => 'CN',
                    'region' => 'East Asia & Pacific',
                    'currency_code' => 'CNY',
                    'income_level' => 'Upper middle income',
                    'latitude' => 35.8617,
                    'longitude' => 104.1954
                ],
                [
                    'name' => 'United States',
                    'iso_code' => 'US',
                    'region' => 'North America',
                    'currency_code' => 'USD',
                    'income_level' => 'High income',
                    'latitude' => 37.0902,
                    'longitude' => -95.7129
                ],
                [
                    'name' => 'Netherlands',
                    'iso_code' => 'NL',
                    'region' => 'Europe & Central Asia',
                    'currency_code' => 'EUR',
                    'income_level' => 'High income',
                    'latitude' => 52.1326,
                    'longitude' => 5.2913
                ],
                [
                    'name' => 'Japan',
                    'iso_code' => 'JP',
                    'region' => 'East Asia & Pacific',
                    'currency_code' => 'JPY',
                    'income_level' => 'High income',
                    'latitude' => 36.2048,
                    'longitude' => 138.2529
                ],
                [
                    'name' => 'Germany',
                    'iso_code' => 'DE',
                    'region' => 'Europe & Central Asia',
                    'currency_code' => 'EUR',
                    'income_level' => 'High income',
                    'latitude' => 51.1657,
                    'longitude' => 10.4515
                ],
                [
                    'name' => 'Australia',
                    'iso_code' => 'AU',
                    'region' => 'East Asia & Pacific',
                    'currency_code' => 'AUD',
                    'income_level' => 'High income',
                    'latitude' => -25.2744,
                    'longitude' => 133.7751
                ],
                [
                    'name' => 'United Kingdom',
                    'iso_code' => 'GB',
                    'region' => 'Europe & Central Asia',
                    'currency_code' => 'GBP',
                    'income_level' => 'High income',
                    'latitude' => 55.3781,
                    'longitude' => -3.4360
                ],
                [
                    'name' => 'India',
                    'iso_code' => 'IN',
                    'region' => 'South Asia',
                    'currency_code' => 'INR',
                    'income_level' => 'Lower middle income',
                    'latitude' => 20.5937,
                    'longitude' => 78.9629
                ],
                [
                    'name' => 'Malaysia',
                    'iso_code' => 'MY',
                    'region' => 'East Asia & Pacific',
                    'currency_code' => 'MYR',
                    'income_level' => 'Upper middle income',
                    'latitude' => 4.2105,
                    'longitude' => 101.9758
                ],
                [
                    'name' => 'South Korea',
                    'iso_code' => 'KR',
                    'region' => 'East Asia & Pacific',
                    'currency_code' => 'KRW',
                    'income_level' => 'High income',
                    'latitude' => 35.9078,
                    'longitude' => 127.7669
                ],
            ];

            foreach ($fallbackCountries as $country) {
                Country::updateOrCreate(
                    ['iso_code' => $country['iso_code']],
                    $country
                );
            }
        }
    }
}
