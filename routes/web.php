<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EnvironmentController;
use App\Http\Controllers\TestScenarioController; // Tambahkan baris ini

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'is_active'])->name('dashboard');

// Rute untuk manajemen pengguna (Khusus Admin)
Route::middleware(['auth', 'is_active', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class);
});

// Rute untuk manajemen environment (Admin & Developer)
Route::middleware(['auth', 'is_active', 'role:admin,developer'])->group(function () {
    Route::resource('environments', EnvironmentController::class);
});

// Rute untuk Skenario Pengujian (Admin, Developer, & QA)
Route::middleware(['auth', 'is_active', 'role:admin,developer,qa'])->group(function () {
    Route::resource('test-scenarios', TestScenarioController::class);
    Route::post('/test-scenarios/{testScenario}/steps', [App\Http\Controllers\TestScenarioController::class, 'storeStep'])->name('test-scenarios.steps.store');
    Route::post('/test-scenarios/{testScenario}/run', [App\Http\Controllers\TestScenarioController::class, 'runScenario'])->name('test-scenarios.run');
    Route::get('/test-scenarios/{testScenario}/steps/{stepIndex}/edit', [App\Http\Controllers\TestScenarioController::class, 'editStep'])->name('test-scenarios.steps.edit');
    Route::put('/test-scenarios/{testScenario}/steps/{stepIndex}', [App\Http\Controllers\TestScenarioController::class, 'updateStep'])->name('test-scenarios.steps.update');
    Route::delete('/test-scenarios/{testScenario}/steps/{stepIndex}', [App\Http\Controllers\TestScenarioController::class, 'destroyStep'])->name('test-scenarios.steps.destroy');
});

require __DIR__.'/auth.php';