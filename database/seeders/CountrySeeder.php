<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            [
                'name' => 'Indonesia',
                'iso_code' => 'ID',
                'region' => 'East Asia & Pacific',
                'currency_code' => 'IDR',
                'income_level' => 'Upper middle income'
            ],
            [
                'name' => 'Singapore',
                'iso_code' => 'SG',
                'region' => 'East Asia & Pacific',
                'currency_code' => 'SGD',
                'income_level' => 'High income'
            ],
            [
                'name' => 'China',
                'iso_code' => 'CN',
                'region' => 'East Asia & Pacific',
                'currency_code' => 'CNY',
                'income_level' => 'Upper middle income'
            ],
            [
                'name' => 'United States',
                'iso_code' => 'US',
                'region' => 'North America',
                'currency_code' => 'USD',
                'income_level' => 'High income'
            ],
            [
                'name' => 'Netherlands',
                'iso_code' => 'NL',
                'region' => 'Europe & Central Asia',
                'currency_code' => 'EUR',
                'income_level' => 'High income'
            ],
            [
                'name' => 'Japan',
                'iso_code' => 'JP',
                'region' => 'East Asia & Pacific',
                'currency_code' => 'JPY',
                'income_level' => 'High income'
            ],
            [
                'name' => 'Germany',
                'iso_code' => 'DE',
                'region' => 'Europe & Central Asia',
                'currency_code' => 'EUR',
                'income_level' => 'High income'
            ],
            [
                'name' => 'Australia',
                'iso_code' => 'AU',
                'region' => 'East Asia & Pacific',
                'currency_code' => 'AUD',
                'income_level' => 'High income'
            ],
            [
                'name' => 'United Kingdom',
                'iso_code' => 'GB',
                'region' => 'Europe & Central Asia',
                'currency_code' => 'GBP',
                'income_level' => 'High income'
            ],
            [
                'name' => 'India',
                'iso_code' => 'IN',
                'region' => 'South Asia',
                'currency_code' => 'INR',
                'income_level' => 'Lower middle income'
            ],
            [
                'name' => 'Malaysia',
                'iso_code' => 'MY',
                'region' => 'East Asia & Pacific',
                'currency_code' => 'MYR',
                'income_level' => 'Upper middle income'
            ],
            [
                'name' => 'South Korea',
                'iso_code' => 'KR',
                'region' => 'East Asia & Pacific',
                'currency_code' => 'KRW',
                'income_level' => 'High income'
            ],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['iso_code' => $country['iso_code']],
                $country
            );
        }
    }
}
