<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LocationController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/locations/countries', [LocationController::class, 'countries']);
Route::get('/locations/regions', [LocationController::class, 'regions']);
Route::get('/locations/cities', [LocationController::class, 'cities']);
Route::get('/locations/barangays', [LocationController::class, 'barangays']);
