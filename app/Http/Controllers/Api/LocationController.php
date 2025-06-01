<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Regions;
use App\Models\City;
use App\Models\Barangay;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LocationController extends Controller
{
    public function countries(): JsonResponse
    {
        $countries = Country::select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($countries);
    }

    public function regions(Request $request): JsonResponse
    {
        $request->validate([
            'country_id' => 'required|exists:countries,id'
        ]);

        $regions = Regions::where('country_id', $request->country_id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($regions);
    }

    public function cities(Request $request): JsonResponse
    {
        $request->validate([
            'region_id' => 'required|exists:regions,id'
        ]);

        $cities = City::where('region_id', $request->region_id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($cities);
    }

    public function barangays(Request $request): JsonResponse
    {
        $request->validate([
            'city_id' => 'required|exists:cities,id'
        ]);

        $barangays = Barangay::where('city_id', $request->city_id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($barangays);
    }
}
