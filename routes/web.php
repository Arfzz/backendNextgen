<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaketBeasiswaController;

Route::get('/', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

Route::resource('paket-beasiswa', PaketBeasiswaController::class);
Route::resource('mentor', \App\Http\Controllers\MentorController::class);
Route::resource('artikel', \App\Http\Controllers\ArtikelController::class);
