<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('paket-beasiswa', \App\Http\Controllers\Api\PaketBeasiswaController::class)->names('api.paket-beasiswa');
Route::apiResource('mentor', \App\Http\Controllers\Api\MentorController::class)->names('api.mentor');
Route::apiResource('artikel', \App\Http\Controllers\Api\ArtikelController::class)->names('api.artikel');

Route::prefix('dashboard/charts')->name('api.dashboard.charts.')->group(function () {
    Route::get('/mentor-vs-peserta', [\App\Http\Controllers\Api\DashboardChartController::class, 'mentorVsPeserta'])->name('mentor-vs-peserta');
    Route::get('/top-beasiswa', [\App\Http\Controllers\Api\DashboardChartController::class, 'topBeasiswa'])->name('top-beasiswa');
    Route::get('/total-penjualan', [\App\Http\Controllers\Api\DashboardChartController::class, 'totalPenjualan'])->name('total-penjualan');
    Route::get('/total-pendapatan', [\App\Http\Controllers\Api\DashboardChartController::class, 'totalPendapatan'])->name('total-pendapatan');
});
