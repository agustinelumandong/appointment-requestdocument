<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\Country;
use App\Models\Regions;
use App\Models\Province;
use App\Models\City;
use App\Models\Barangay;

final class PhilippineLocationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting Philippine locations seeding...');

        // Get or create Philippines country
        $philippines = Country::firstOrCreate(
            ['code' => 'PH'],
            ['name' => 'Philippines']
        );

        $this->command->info('Philippines country: ' . $philippines->name);

        // Load the JSON data
        $path = database_path('data/philippine_locations.json');

        if (!File::exists($path)) {
            $this->command->error('Philippine locations JSON file not found at: ' . $path);
            return;
        }

        $json = File::get($path);
        $data = json_decode($json, true);

        if (!$data) {
            $this->command->error('Failed to decode Philippine locations JSON file');
            return;
        }

        $this->command->info('Processing Philippine locations data...');

        $totalRegions = 0;
        $totalProvinces = 0;
        $totalCities = 0;
        $totalBarangays = 0;

        foreach ($data as $regionCode => $regionData) {
            $this->command->info("Processing region: {$regionData['region_name']} ({$regionCode})");

            // Create or get region
            $region = Regions::firstOrCreate(
                [
                    'code' => $regionCode,
                    'country_id' => $philippines->id
                ],
                [
                    'name' => $regionData['region_name']
                ]
            );

            $totalRegions++;

            // Process provinces
            foreach ($regionData['province_list'] as $provinceName => $provinceData) {
                $this->command->info("  Processing province: {$provinceName}");

                // Create or get province
                $province = Province::firstOrCreate(
                    [
                        'name' => $provinceName,
                        'region_id' => $region->id
                    ],
                    [
                        'code' => null // No code provided in JSON for provinces
                    ]
                );

                $totalProvinces++;

                // Process municipalities/cities
                foreach ($provinceData['municipality_list'] as $cityName => $cityData) {
                    $this->command->info("    Processing city/municipality: {$cityName}");

                    // Create or get city
                    $city = City::firstOrCreate(
                        [
                            'name' => $cityName,
                            'province_id' => $province->id,
                            'region_id' => $region->id
                        ],
                        [
                            'zip_code' => null // No zip code provided in JSON
                        ]
                    );

                    $totalCities++;

                    // Process barangays
                    if (isset($cityData['barangay_list']) && is_array($cityData['barangay_list'])) {
                        foreach ($cityData['barangay_list'] as $barangayName) {
                            // Create barangay if it doesn't exist
                            $barangay = Barangay::firstOrCreate(
                                [
                                    'name' => $barangayName,
                                    'city_id' => $city->id
                                ],
                                [
                                    'code' => null // No code provided in JSON for barangays
                                ]
                            );

                            $totalBarangays++;
                        }
                    }
                }
            }
        }

        $this->command->info("Philippine locations seeded successfully!");
        $this->command->info("Total regions: {$totalRegions}");
        $this->command->info("Total provinces: {$totalProvinces}");
        $this->command->info("Total cities/municipalities: {$totalCities}");
        $this->command->info("Total barangays: {$totalBarangays}");
    }
}
