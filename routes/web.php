<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Pages\ClientController;
use App\Http\Controllers\Pages\DashboardController;
use App\Http\Controllers\Pages\LotController;
use App\Http\Controllers\Pages\PaymentController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

Route::middleware(['auth', 'verified', 'approved'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('clients', ClientController::class);
    Route::resource('lots', LotController::class);
    Route::post('lots/{lot}/payments', [PaymentController::class, 'store'])->name('lots.payments.store');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
