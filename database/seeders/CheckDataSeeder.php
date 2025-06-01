<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\Regions;
use App\Models\Province;
use App\Models\City;
use App\Models\Barangay;

class CheckDataSeeder extends Seeder
{
    public function run(): void
    {
        $philippines = Country::where('code', 'PH')->first();

        if ($philippines) {
            echo "Philippine Regions:\n";
            $regions = Regions::where('country_id', $philippines->id)->get(['id', 'name', 'code']);

            foreach ($regions as $region) {
                echo "{$region->code} - {$region->name}\n";

                // Check provinces
                $provinces = Province::where('region_id', $region->id)->take(3)->get(['id', 'name']);
                foreach ($provinces as $province) {
                    echo "  Province: {$province->name}\n";

                    // Check cities
                    $cities = City::where('province_id', $province->id)->take(2)->get(['id', 'name']);
                    foreach ($cities as $city) {
                        echo "    City: {$city->name}\n";

                        // Check barangays
                        $barangays = Barangay::where('city_id', $city->id)->take(2)->get(['name']);
                        foreach ($barangays as $barangay) {
                            echo "      Barangay: {$barangay->name}\n";
                        }
                    }
                }
            }

            // Show totals
            $regionCount = Regions::where('country_id', $philippines->id)->count();
            $provinceCount = Province::whereHas('region', function ($query) use ($philippines) {
                $query->where('country_id', $philippines->id);
            })->count();
            $cityCount = City::whereHas('province.region', function ($query) use ($philippines) {
                $query->where('country_id', $philippines->id);
            })->count();
            $barangayCount = Barangay::whereHas('city.province.region', function ($query) use ($philippines) {
                $query->where('country_id', $philippines->id);
            })->count();

            echo "\nTotals:\n";
            echo "Regions: {$regionCount}\n";
            echo "Provinces: {$provinceCount}\n";
            echo "Cities/Municipalities: {$cityCount}\n";
            echo "Barangays: {$barangayCount}\n";
        }
    }
}
