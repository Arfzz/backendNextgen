<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('paket-beasiswa', \App\Http\Controllers\Api\PaketBeasiswaController::class)->names('api.paket-beasiswa');
Route::apiResource('mentor', \App\Http\Controllers\Api\MentorController::class)->names('api.mentor');
Route::apiResource('artikel', \App\Http\Controllers\Api\ArtikelController::class)->names('api.artikel');

