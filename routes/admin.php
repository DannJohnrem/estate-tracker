<?php

use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'approved'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::middleware('permission:users.view')->group(function () {
            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::patch('users/{user}/status', [UserController::class, 'updateStatus'])->name('users.update-status');
            Route::patch('users/{user}/roles', [UserController::class, 'updateRoles'])->name('users.update-roles');
        });

        Route::middleware('permission:users.delete')->group(function () {
            Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        });

        Route::middleware('permission:roles.view')->group(function () {
            Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
            Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
        });

        Route::middleware('permission:roles.create')->group(function () {
            Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
            Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
        });

        Route::middleware('permission:roles.edit')->group(function () {
            Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
            Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        });

        Route::middleware('permission:roles.delete')->group(function () {
            Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
        });
    });
