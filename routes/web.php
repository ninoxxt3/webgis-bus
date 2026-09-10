<?php

use App\Http\Controllers\WebGISController;
use Illuminate\Support\Facades\Route;

// Halaman Utama Dashboard
Route::get('/', [WebGISController::class, 'index'])->name('dashboard');

// Endpoint API RESTful
Route::prefix('api')->group(function () {
    Route::get('/ringkasan', [WebGISController::class, 'getRingkasan'])->name('api.ringkasan');
    Route::get('/geojson/rute', [WebGISController::class, 'getRuteGeoJson'])->name('api.geojson.rute');
    Route::get('/geojson/titik-ujung', [WebGISController::class, 'getTitikUjungGeoJson'])->name('api.geojson.titik-ujung');
    Route::get('/gps-mentah', [WebGISController::class, 'getGpsMentah'])->name('api.gps-mentah');
    Route::get('/gps-preprocessed', [WebGISController::class, 'getGpsPreprocessed'])->name('api.gps-preprocessed');
});
