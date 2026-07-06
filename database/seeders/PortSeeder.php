<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Port;
use App\Models\Country;

class PortSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ports = [
            [
                'name' => 'Tanjung Priok (Jakarta)',
                'country_code' => 'ID',
                'latitude' => -6.10250000,
                'longitude' => 106.89060000,
                'code' => 'WPI-51140'
            ],
            [
                'name' => 'Tanjung Perak (Surabaya)',
                'country_code' => 'ID',
                'latitude' => -7.20260000,
                'longitude' => 112.72310000,
                'code' => 'WPI-51190'
            ],
            [
                'name' => 'Port of Singapore',
                'country_code' => 'SG',
                'latitude' => 1.26440000,
                'longitude' => 103.83980000,
                'code' => 'WPI-49950'
            ],
            [
                'name' => 'Port of Shanghai',
                'country_code' => 'CN',
                'latitude' => 31.22220000,
                'longitude' => 121.53970000,
                'code' => 'WPI-51740'
            ],
            [
                'name' => 'Port of Shenzhen',
                'country_code' => 'CN',
                'latitude' => 22.50500000,
                'longitude' => 113.88600000,
                'code' => 'WPI-51820'
            ],
            [
                'name' => 'Port of Rotterdam',
                'country_code' => 'NL',
                'latitude' => 51.88500000,
                'longitude' => 4.28670000,
                'code' => 'WPI-32860'
            ],
            [
                'name' => 'Port of Los Angeles',
                'country_code' => 'US',
                'latitude' => 33.72880000,
                'longitude' => -118.26200000,
                'code' => 'WPI-22240'
            ],
            [
                'name' => 'Port of New York & New Jersey',
                'country_code' => 'US',
                'latitude' => 40.66980000,
                'longitude' => -74.15980000,
                'code' => 'WPI-21840'
            ],
            [
                'name' => 'Port of Tokyo',
                'country_code' => 'JP',
                'latitude' => 35.61860000,
                'longitude' => 139.79440000,
                'code' => 'WPI-53640'
            ],
            [
                'name' => 'Port of Hamburg',
                'country_code' => 'DE',
                'latitude' => 53.52440000,
                'longitude' => 9.94810000,
                'code' => 'WPI-32320'
            ],
            [
                'name' => 'Port of Melbourne',
                'country_code' => 'AU',
                'latitude' => -37.82880000,
                'longitude' => 144.91250000,
                'code' => 'WPI-52900'
            ],
            [
                'name' => 'Port of London',
                'country_code' => 'GB',
                'latitude' => 51.50340000,
                'longitude' => 0.05440000,
                'code' => 'WPI-31800'
            ],
            [
                'name' => 'Port of Mumbai (Nhava Sheva)',
                'country_code' => 'IN',
                'latitude' => 18.94820000,
                'longitude' => 72.94630000,
                'code' => 'WPI-48740'
            ],
            [
                'name' => 'Port Klang',
                'country_code' => 'MY',
                'latitude' => 3.00110000,
                'longitude' => 101.35330000,
                'code' => 'WPI-49990'
            ],
            [
                'name' => 'Port of Busan',
                'country_code' => 'KR',
                'latitude' => 35.10440000,
                'longitude' => 129.04310000,
                'code' => 'WPI-53800'
            ],
        ];

        foreach ($ports as $port) {
            $country = Country::where('iso_code', $port['country_code'])->first();
            if ($country) {
                Port::updateOrCreate(
                    ['code' => $port['code']],
                    [
                        'name' => $port['name'],
                        'country_id' => $country->id,
                        'latitude' => $port['latitude'],
                        'longitude' => $port['longitude'],
                    ]
                );
            }
        }
    }
}
