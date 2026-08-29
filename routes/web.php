<?php

use App\Http\Controllers\Pages\ClientController;
use App\Http\Controllers\Pages\DashboardController;
use App\Http\Controllers\Pages\LotController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('clients', ClientController::class);
    Route::resource('lots', LotController::class);
});

require __DIR__.'/settings.php';
