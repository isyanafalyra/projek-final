<?php

namespace App\Services;

use App\Models\Port;
use App\Models\Country;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class PortImporterService
{
    /**
     * Import or update ports from the local JSON dataset.
     *
     * @return array Import summary statistics
     */
    public function import(): array
    {
        $jsonPath = database_path('data/ports.json');
        
        if (!File::exists($jsonPath)) {
            Log::error("Ports JSON file not found at: {$jsonPath}");
            throw new \Exception("Ports dataset file not found.");
        }

        $portsData = json_decode(File::get($jsonPath), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("Failed to parse ports JSON: " . json_last_error_msg());
            throw new \Exception("Invalid JSON format in ports dataset.");
        }

        // Get all countries from database
        $countries = Country::all();
        $countriesMap = $countries->keyBy(function ($country) {
            return strtoupper($country->iso_code);
        });

        $importedCount = 0;
        $portsPerCountry = [];

        foreach ($portsData as $locode => $port) {
            $countryCode = strtoupper(substr($locode, 0, 2));

            if (isset($countriesMap[$countryCode])) {
                $country = $countriesMap[$countryCode];
                $coords = $port['coordinates'] ?? [0.0, 0.0];
                $lng = isset($coords[0]) ? (float) $coords[0] : 0.0;
                $lat = isset($coords[1]) ? (float) $coords[1] : 0.0;

                // Determine port type
                $name = $port['name'] ?? 'Port';
                $lowerName = strtolower($name);
                $type = 'cargo'; // Default type
                if (str_contains($lowerName, 'terminal')) {
                    $type = 'terminal';
                } elseif (str_contains($lowerName, 'oil') || str_contains($lowerName, 'petroleum') || str_contains($lowerName, 'lng')) {
                    $type = 'oil';
                } elseif (str_contains($lowerName, 'ferry') || str_contains($lowerName, 'passenger')) {
                    $type = 'ferry';
                } elseif (str_contains($lowerName, 'container')) {
                    $type = 'container';
                }

                // Check for matches in existing ports to avoid duplicates
                $matchedPort = $this->findMatchingPort($country->id, $locode, $name, $lat, $lng);

                if ($matchedPort) {
                    // Update existing port (prefer UN/LOCODE as code)
                    $newCode = $matchedPort->code;
                    if (str_starts_with($matchedPort->code ?? '', 'WPI-') && !str_starts_with($locode, 'WPI-')) {
                        $newCode = $locode;
                    }

                    // Keep more descriptive name
                    $newName = $matchedPort->name;
                    if (strlen($name) > strlen($matchedPort->name) && str_contains(strtolower($name), strtolower($matchedPort->name))) {
                        $newName = $name;
                    }

                    $matchedPort->update([
                        'code' => $newCode,
                        'name' => $newName,
                        'city' => $matchedPort->city ?? $port['city'] ?? $port['name'] ?? null,
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'type' => $type,
                        'status' => 'active'
                    ]);
                } else {
                    // Create new port
                    Port::create([
                        'name' => $name,
                        'country_id' => $country->id,
                        'country_code' => $country->iso_code,
                        'city' => $port['city'] ?? $port['name'] ?? null,
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'code' => $locode,
                        'status' => 'active',
                        'type' => $type
                    ]);
                }

                $importedCount++;
                $portsPerCountry[$country->name] = ($portsPerCountry[$country->name] ?? 0) + 1;
            }
        }

        // Find countries without any ports in the dataset
        $missingCountries = [];
        foreach ($countries as $country) {
            if (!isset($portsPerCountry[$country->name])) {
                $missingCountries[] = [
                    'name' => $country->name,
                    'iso_code' => $country->iso_code
                ];
            }
        }

        // Sort statistics
        ksort($portsPerCountry);
        usort($missingCountries, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return [
            'total_imported' => $importedCount,
            'ports_per_country' => $portsPerCountry,
            'missing_countries' => $missingCountries,
        ];
    }

    /**
     * Find a matching port based on code, name similarity, and proximity.
     */
    protected function findMatchingPort(int $countryId, string $locode, string $name, float $lat, float $lng): ?Port
    {
        // 1. Try exact code match first
        $port = Port::where('country_id', $countryId)->where('code', $locode)->first();
        if ($port) {
            return $port;
        }

        // 2. Fetch all ports of the country to compare proximity and name similarity
        $existingPorts = Port::where('country_id', $countryId)->get();
        $cleanName = $this->getCleanName($name);

        foreach ($existingPorts as $existing) {
            $latDiff = abs($existing->latitude - $lat);
            $lngDiff = abs($existing->longitude - $lng);
            $distance = sqrt($latDiff * $latDiff + $lngDiff * $lngDiff);

            // Proximity threshold: 0.1 degrees
            if ($distance < 0.1) {
                $existingClean = $this->getCleanName($existing->name);

                $isExact = ($cleanName === $existingClean);
                $isSubstring = (str_contains($cleanName, $existingClean) || str_contains($existingClean, $cleanName));
                $sharedWord = $this->shareSignificantWord($cleanName, $existingClean);

                if ($isExact || $isSubstring || $sharedWord !== false) {
                    return $existing;
                }
            }
        }

        return null;
    }

    /**
     * Helper to clean names for matching.
     */
    protected function getCleanName(string $name): string
    {
        $name = strtolower($name);
        $name = preg_replace('/\b(port of|port de|porto de|port)\b/', '', $name);
        $name = preg_replace('/[^a-z0-9]/', ' ', $name);
        $name = preg_replace('/\s+/', ' ', $name);
        return trim($name);
    }

    /**
     * Helper to check for shared significant words.
     */
    protected function shareSignificantWord(string $name1, string $name2): bool|string
    {
        $words1 = explode(' ', $name1);
        $words2 = explode(' ', $name2);
        $ignoredWords = ['java', 'sumatra', 'sulawesi', 'kalimantan', 'island', 'sumatera', 'port', 'terminal', 'cargo', 'west', 'east', 'south', 'north', 'gulf', 'bay', 'harbor', 'harbour'];
        
        foreach ($words1 as $w1) {
            if (strlen($w1) < 4 || in_array($w1, $ignoredWords)) continue;
            foreach ($words2 as $w2) {
                if ($w1 === $w2) {
                    return $w1;
                }
            }
        }
        return false;
    }
}
