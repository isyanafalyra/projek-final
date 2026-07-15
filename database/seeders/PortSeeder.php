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
        $importer = app(\App\Services\PortImporterService::class);
        $result = $importer->import();

        $this->command->info("Successfully seeded {$result['total_imported']} ports.");
    }
}
