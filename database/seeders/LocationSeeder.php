<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Regions;
use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\Region;
use App\Models\City;
use App\Models\Barangay;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

final class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // Check if Philippines already exists
        $philippines = Country::where('code', 'PH')->first();

        if (!$philippines) {
            // Philippines doesn't exist, create it
            $philippines = Country::create([
                'name' => 'Philippines',
                'code' => 'PH'
            ]);
            $this->seedPhilippinesData($philippines);
        } else {
            // Philippines exists, check if barangays are already seeded
            $barangayCount = Barangay::whereHas('city.region.country', function ($query) {
                $query->where('code', 'PH');
            })->count();

            if ($barangayCount == 0) {
                $this->command->info('Philippines exists but no barangays found. Seeding barangays only...');
                $this->seedPhilippineBarangays($philippines);
            } else {
                $this->command->info('Philippines and barangays already exist. Skipping...');
            }
        }

        // Check and create other countries only if they don't exist
        $this->seedCountryIfNotExists('United States', 'US', 'seedUSAData');
        $this->seedCountryIfNotExists('United Kingdom', 'GB', 'seedUKData');
        $this->seedCountryIfNotExists('Canada', 'CA', 'seedCanadaData');
        $this->seedCountryIfNotExists('Australia', 'AU', 'seedAustraliaData');
        $this->seedCountryIfNotExists('Japan', 'JP', 'seedJapanData');
        $this->seedCountryIfNotExists('Germany', 'DE', 'seedGermanyData');
        $this->seedCountryIfNotExists('France', 'FR', 'seedFranceData');
    }

    private function seedCountryIfNotExists(string $name, string $code, string $method): void
    {
        $country = Country::where('code', $code)->first();

        if (!$country) {
            $country = Country::create([
                'name' => $name,
                'code' => $code
            ]);
            $this->$method($country);
        } else {
            $this->command->info("$name already exists. Skipping...");
        }
    }

    private function seedPhilippinesData(Country $philippines): void
    {
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

        $this->command->info('Seeding Philippine locations from JSON file...');

        foreach ($data as $regionCode => $regionData) {
            $this->command->info("Processing region: " . $regionData['region_name']);

            // Create region
            $region = Regions::create([
                'name' => $regionData['region_name'],
                'code' => $regionCode,
                'country_id' => $philippines->id
            ]);

            // Process provinces (treated as cities in our structure)
            foreach ($regionData['province_list'] as $provinceName => $provinceData) {
                $this->command->info("  Processing province: " . $provinceName);

                // Create city (province in JSON is treated as city in our DB)
                $city = City::create([
                    'name' => $provinceName,
                    'region_id' => $region->id
                ]);

                // Process municipalities and their barangays
                foreach ($provinceData['municipality_list'] as $municipalityName => $municipalityData) {
                    // Create barangays for this municipality
                    foreach ($municipalityData['barangay_list'] as $barangayName) {
                        Barangay::create([
                            'name' => $barangayName,
                            'city_id' => $city->id,
                            'code' => null // Optional: you can add codes if needed
                        ]);
                    }
                }
            }
        }

        $this->command->info('Philippine locations seeded successfully!');
    }

    private function seedPhilippineBarangays(Country $philippines): void
    {
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

        // Create mapping between JSON region codes and our database region codes
        $regionMapping = [
            '01' => 'I',        // REGION I -> Ilocos Region
            '02' => 'II',       // REGION II -> Cagayan Valley
            '03' => 'III',      // REGION III -> Central Luzon
            '04' => 'IV-A',     // REGION IV-A -> CALABARZON
            '4A' => 'IV-A',     // Alternative format for REGION IV-A
            '4B' => 'IV-B',     // REGION IV-B -> MIMAROPA (not in current data)
            '05' => 'V',        // REGION V -> Bicol Region
            '06' => 'VI',       // REGION VI -> Western Visayas
            '07' => 'VII',      // REGION VII -> Central Visayas
            '08' => 'VIII',     // REGION VIII -> Eastern Visayas
            '09' => 'IX',       // REGION IX -> Zamboanga Peninsula
            '10' => 'X',        // REGION X -> Northern Mindanao
            '11' => 'XI',       // REGION XI -> Davao Region
            '12' => 'XII',      // REGION XII -> SOCCSKSARGEN (not in our current data)
            '13' => 'XIII',     // REGION XIII -> Caraga (not in our current data)
            'BARMM' => 'BARMM', // BARMM (not in our current data)
            'CAR' => 'CAR',     // CAR (not in our current data)
            'NCR' => 'NCR'      // NCR -> National Capital Region
        ];

        $this->command->info('Seeding Philippine barangays from JSON file...');

        foreach ($data as $jsonRegionCode => $regionData) {
            $this->command->info("Processing region: " . $regionData['region_name']);

            // Map JSON region code to our database region code
            $dbRegionCode = $regionMapping[$jsonRegionCode] ?? null;

            if (!$dbRegionCode) {
                $this->command->info("No mapping found for region code: $jsonRegionCode, skipping...");
                continue;
            }

            // Find existing region
            $region = Regions::where('country_id', $philippines->id)
                ->where('code', $dbRegionCode)
                ->first();

            if (!$region) {
                $this->command->info("Region not found in database: $dbRegionCode, skipping...");
                continue;
            }

            // For each province in the JSON, we'll create barangays under the first matching city
            // Since our current structure doesn't have all provinces as separate cities
            foreach ($regionData['province_list'] as $provinceName => $provinceData) {
                $this->command->info("  Processing province: " . $provinceName);

                // Get the first city in this region (we'll associate all barangays with it for now)
                $city = City::where('region_id', $region->id)->first();

                if (!$city) {
                    $this->command->info("  No cities found in region: " . $region->name . ", skipping...");
                    continue;
                }

                $barangayCount = 0;
                // Process municipalities and their barangays
                foreach ($provinceData['municipality_list'] as $municipalityName => $municipalityData) {
                    // Create barangays for this municipality
                    foreach ($municipalityData['barangay_list'] as $barangayName) {
                        // Check if barangay already exists to avoid duplicates
                        $existingBarangay = Barangay::where('city_id', $city->id)
                            ->where('name', $barangayName)
                            ->first();

                        if (!$existingBarangay) {
                            Barangay::create([
                                'name' => $barangayName,
                                'city_id' => $city->id,
                                'code' => null
                            ]);
                            $barangayCount++;
                        }
                    }
                }

                $this->command->info("    Created $barangayCount barangays for $provinceName");
            }
        }

        $this->command->info('Philippine barangays seeded successfully!');
    }

    private function seedUSAData(Country $usa): void
    {
        $states = [
            [
                'name' => 'California',
                'code' => 'CA',
                'cities' => [
                    'Los Angeles',
                    'San Francisco',
                    'San Diego',
                    'Sacramento',
                    'Oakland',
                    'Fresno',
                    'Long Beach',
                    'Santa Ana',
                    'Anaheim',
                    'Riverside'
                ]
            ],
            [
                'name' => 'New York',
                'code' => 'NY',
                'cities' => [
                    'New York City',
                    'Buffalo',
                    'Rochester',
                    'Yonkers',
                    'Syracuse',
                    'Albany'
                ]
            ],
            [
                'name' => 'Texas',
                'code' => 'TX',
                'cities' => [
                    'Houston',
                    'San Antonio',
                    'Dallas',
                    'Austin',
                    'Fort Worth',
                    'El Paso'
                ]
            ],
            [
                'name' => 'Florida',
                'code' => 'FL',
                'cities' => [
                    'Jacksonville',
                    'Miami',
                    'Tampa',
                    'Orlando',
                    'St. Petersburg',
                    'Tallahassee'
                ]
            ],
            [
                'name' => 'Illinois',
                'code' => 'IL',
                'cities' => [
                    'Chicago',
                    'Aurora',
                    'Rockford',
                    'Joliet',
                    'Naperville',
                    'Springfield'
                ]
            ],
        ];

        foreach ($states as $stateData) {
            $region = Regions::create([
                'name' => $stateData['name'],
                'code' => $stateData['code'],
                'country_id' => $usa->id
            ]);

            foreach ($stateData['cities'] as $cityName) {
                City::create([
                    'name' => $cityName,
                    'region_id' => $region->id
                ]);
            }
        }
    }

    private function seedUKData(Country $uk): void
    {
        $regions = [
            [
                'name' => 'England',
                'code' => 'ENG',
                'cities' => [
                    'London',
                    'Birmingham',
                    'Manchester',
                    'Leeds',
                    'Liverpool',
                    'Sheffield',
                    'Bristol',
                    'Newcastle',
                    'Nottingham',
                    'Leicester'
                ]
            ],
            [
                'name' => 'Scotland',
                'code' => 'SCT',
                'cities' => [
                    'Edinburgh',
                    'Glasgow',
                    'Aberdeen',
                    'Dundee',
                    'Stirling'
                ]
            ],
            [
                'name' => 'Wales',
                'code' => 'WLS',
                'cities' => [
                    'Cardiff',
                    'Swansea',
                    'Newport',
                    'Wrexham',
                    'Barry'
                ]
            ],
            [
                'name' => 'Northern Ireland',
                'code' => 'NIR',
                'cities' => [
                    'Belfast',
                    'Derry',
                    'Lisburn',
                    'Newtownabbey',
                    'Bangor'
                ]
            ],
        ];

        foreach ($regions as $regionData) {
            $region = Regions::create([
                'name' => $regionData['name'],
                'code' => $regionData['code'],
                'country_id' => $uk->id
            ]);

            foreach ($regionData['cities'] as $cityName) {
                City::create([
                    'name' => $cityName,
                    'region_id' => $region->id
                ]);
            }
        }
    }

    private function seedCanadaData(Country $canada): void
    {
        $provinces = [
            [
                'name' => 'Ontario',
                'code' => 'ON',
                'cities' => [
                    'Toronto',
                    'Ottawa',
                    'Hamilton',
                    'London',
                    'Kitchener',
                    'Windsor'
                ]
            ],
            [
                'name' => 'Quebec',
                'code' => 'QC',
                'cities' => [
                    'Montreal',
                    'Quebec City',
                    'Laval',
                    'Gatineau',
                    'Longueuil'
                ]
            ],
            [
                'name' => 'British Columbia',
                'code' => 'BC',
                'cities' => [
                    'Vancouver',
                    'Victoria',
                    'Surrey',
                    'Burnaby',
                    'Richmond'
                ]
            ],
            [
                'name' => 'Alberta',
                'code' => 'AB',
                'cities' => [
                    'Calgary',
                    'Edmonton',
                    'Red Deer',
                    'Lethbridge',
                    'Medicine Hat'
                ]
            ],
        ];

        foreach ($provinces as $provinceData) {
            $region = Regions::create([
                'name' => $provinceData['name'],
                'code' => $provinceData['code'],
                'country_id' => $canada->id
            ]);

            foreach ($provinceData['cities'] as $cityName) {
                City::create([
                    'name' => $cityName,
                    'region_id' => $region->id
                ]);
            }
        }
    }

    private function seedAustraliaData(Country $australia): void
    {
        $states = [
            [
                'name' => 'New South Wales',
                'code' => 'NSW',
                'cities' => [
                    'Sydney',
                    'Newcastle',
                    'Wollongong',
                    'Albury',
                    'Wagga Wagga'
                ]
            ],
            [
                'name' => 'Victoria',
                'code' => 'VIC',
                'cities' => [
                    'Melbourne',
                    'Geelong',
                    'Ballarat',
                    'Bendigo',
                    'Shepparton'
                ]
            ],
            [
                'name' => 'Queensland',
                'code' => 'QLD',
                'cities' => [
                    'Brisbane',
                    'Gold Coast',
                    'Townsville',
                    'Cairns',
                    'Toowoomba'
                ]
            ],
            [
                'name' => 'Western Australia',
                'code' => 'WA',
                'cities' => [
                    'Perth',
                    'Fremantle',
                    'Rockingham',
                    'Mandurah',
                    'Bunbury'
                ]
            ],
        ];

        foreach ($states as $stateData) {
            $region = Regions::create([
                'name' => $stateData['name'],
                'code' => $stateData['code'],
                'country_id' => $australia->id
            ]);

            foreach ($stateData['cities'] as $cityName) {
                City::create([
                    'name' => $cityName,
                    'region_id' => $region->id
                ]);
            }
        }
    }

    private function seedJapanData(Country $japan): void
    {
        $prefectures = [
            [
                'name' => 'Tokyo',
                'code' => '13',
                'cities' => [
                    'Shinjuku',
                    'Shibuya',
                    'Harajuku',
                    'Ginza',
                    'Akihabara'
                ]
            ],
            [
                'name' => 'Osaka',
                'code' => '27',
                'cities' => [
                    'Osaka',
                    'Sakai',
                    'Higashiosaka',
                    'Hirakata',
                    'Toyonaka'
                ]
            ],
            [
                'name' => 'Kanagawa',
                'code' => '14',
                'cities' => [
                    'Yokohama',
                    'Kawasaki',
                    'Sagamihara',
                    'Fujisawa',
                    'Chigasaki'
                ]
            ],
            [
                'name' => 'Kyoto',
                'code' => '26',
                'cities' => [
                    'Kyoto',
                    'Uji',
                    'Kameoka',
                    'Joyo',
                    'Mukō'
                ]
            ],
        ];

        foreach ($prefectures as $prefectureData) {
            $region = Regions::create([
                'name' => $prefectureData['name'],
                'code' => $prefectureData['code'],
                'country_id' => $japan->id
            ]);

            foreach ($prefectureData['cities'] as $cityName) {
                City::create([
                    'name' => $cityName,
                    'region_id' => $region->id
                ]);
            }
        }
    }

    private function seedGermanyData(Country $germany): void
    {
        $states = [
            [
                'name' => 'Bavaria',
                'code' => 'BY',
                'cities' => [
                    'Munich',
                    'Nuremberg',
                    'Augsburg',
                    'Regensburg',
                    'Ingolstadt'
                ]
            ],
            [
                'name' => 'North Rhine-Westphalia',
                'code' => 'NW',
                'cities' => [
                    'Cologne',
                    'Düsseldorf',
                    'Dortmund',
                    'Essen',
                    'Duisburg'
                ]
            ],
            [
                'name' => 'Baden-Württemberg',
                'code' => 'BW',
                'cities' => [
                    'Stuttgart',
                    'Mannheim',
                    'Karlsruhe',
                    'Freiburg',
                    'Heidelberg'
                ]
            ],
            [
                'name' => 'Berlin',
                'code' => 'BE',
                'cities' => [
                    'Berlin'
                ]
            ],
        ];

        foreach ($states as $stateData) {
            $region = Regions::create([
                'name' => $stateData['name'],
                'code' => $stateData['code'],
                'country_id' => $germany->id
            ]);

            foreach ($stateData['cities'] as $cityName) {
                City::create([
                    'name' => $cityName,
                    'region_id' => $region->id
                ]);
            }
        }
    }

    private function seedFranceData(Country $france): void
    {
        $regions = [
            [
                'name' => 'Île-de-France',
                'code' => 'IDF',
                'cities' => [
                    'Paris',
                    'Boulogne-Billancourt',
                    'Saint-Denis',
                    'Argenteuil',
                    'Montreuil'
                ]
            ],
            [
                'name' => 'Provence-Alpes-Côte d\'Azur',
                'code' => 'PAC',
                'cities' => [
                    'Marseille',
                    'Nice',
                    'Toulon',
                    'Aix-en-Provence',
                    'Antibes'
                ]
            ],
            [
                'name' => 'Auvergne-Rhône-Alpes',
                'code' => 'ARA',
                'cities' => [
                    'Lyon',
                    'Grenoble',
                    'Saint-Étienne',
                    'Villeurbanne',
                    'Clermont-Ferrand'
                ]
            ],
            [
                'name' => 'Occitanie',
                'code' => 'OCC',
                'cities' => [
                    'Toulouse',
                    'Montpellier',
                    'Nîmes',
                    'Perpignan',
                    'Béziers'
                ]
            ],
        ];

        foreach ($regions as $regionData) {
            $region = Regions::create([
                'name' => $regionData['name'],
                'code' => $regionData['code'],
                'country_id' => $france->id
            ]);

            foreach ($regionData['cities'] as $cityName) {
                City::create([
                    'name' => $cityName,
                    'region_id' => $region->id
                ]);
            }
        }
    }
}
