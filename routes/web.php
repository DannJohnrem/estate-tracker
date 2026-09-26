<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Pages\AgentController;
use App\Http\Controllers\Pages\ClientController;
use App\Http\Controllers\Pages\DashboardController;
use App\Http\Controllers\Pages\LotController;
use App\Http\Controllers\Pages\PaymentController;
use App\Http\Controllers\Pages\ProjectController;
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

    // Payments module (payments/create must come before payments/{payment})
    Route::get('payments', [PaymentController::class, 'index'])
        ->name('payments.index')->middleware('can:payments.view');
    Route::get('payments/create', [PaymentController::class, 'create'])
        ->name('payments.create')->middleware('can:payments.create');
    Route::post('payments', [PaymentController::class, 'store'])
        ->name('payments.store')->middleware('can:payments.create');
    Route::get('payments/{payment}', [PaymentController::class, 'show'])
        ->name('payments.show')->middleware('can:payments.view')->whereUuid('payment');
    Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])
        ->name('payments.destroy')->middleware('can:payments.delete')->whereUuid('payment');

    // Quick record from the Lot page
    Route::post('lots/{lot}/payments', [PaymentController::class, 'storeForLot'])
        ->name('lots.payments.store')->middleware('can:payments.create');

    Route::resource('projects', ProjectController::class)
    ->only(['index', 'show'])
    ->middleware('can:projects.view');

    // Agents module (agents/create and agents/{agent}/edit must come before agents/{agent})
    Route::middleware(['permission:agents.view'])->group(function () {
        Route::get('/agents', [AgentController::class, 'index'])->name('agents.index');
    });
    Route::middleware(['permission:agents.create'])->group(function () {
        Route::get('/agents/create', [AgentController::class, 'create'])->name('agents.create');
        Route::post('/agents', [AgentController::class, 'store'])->name('agents.store');
    });
    Route::middleware(['permission:agents.edit'])->group(function () {
        Route::get('/agents/{agent}/edit', [AgentController::class, 'edit'])->name('agents.edit');
        Route::put('/agents/{agent}', [AgentController::class, 'update'])->name('agents.update');
    });
    Route::middleware(['permission:agents.view'])->group(function () {
        Route::get('/agents/{agent}', [AgentController::class, 'show'])->name('agents.show');
    });
    Route::middleware(['permission:agents.delete'])->group(function () {
        Route::delete('/agents/{agent}', [AgentController::class, 'destroy'])->name('agents.destroy');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
