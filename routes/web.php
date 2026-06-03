<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaketBeasiswaController;
use App\Http\Controllers\TestimonialController;

use App\Http\Controllers\WebAuthController;

// Admin Login Routes
Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

// Protected Admin Routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('paket-beasiswa', PaketBeasiswaController::class);
    Route::resource('mentor', \App\Http\Controllers\MentorController::class);
    Route::resource('artikel', \App\Http\Controllers\ArtikelController::class);
    Route::resource('users', \App\Http\Controllers\UserController::class);

    // Order / Payment CMS
    Route::get('orders', [\App\Http\Controllers\OrderCmsController::class, 'index'])->name('orders.index');
    Route::get('orders/export-pdf', [\App\Http\Controllers\OrderCmsController::class, 'exportPdf'])->name('orders.exportPdf');
    Route::get('orders/{id}/invoice', [\App\Http\Controllers\OrderCmsController::class, 'printInvoice'])->name('orders.printInvoice');
    Route::get('orders/{id}', [\App\Http\Controllers\OrderCmsController::class, 'show'])->name('orders.show');
    Route::post('orders/{id}/status', [\App\Http\Controllers\OrderCmsController::class, 'updateStatus'])->name('orders.updateStatus');
});

// Public testimonial form page (2-step: login → form)
Route::get('/testimonial/beri-testimoni', function () {
    return view('testimonial.form');
})->name('testimonial.form');

// Step 1: authenticate peserta and return their mentor/class info
Route::post('/testimonial/login', [\App\Http\Controllers\TestimonialFormController::class, 'login'])->name('testimonial.login');

// Step 2: submit testimonial
Route::post('/testimonial/submit', [\App\Http\Controllers\TestimonialFormController::class, 'submit'])->name('testimonial.submit');

Route::resource('testimonial', TestimonialController::class)->only(['index', 'update', 'destroy'])->middleware('auth');
