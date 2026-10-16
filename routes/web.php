<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EnvironmentController;
use App\Http\Controllers\TestScenarioController; // Tambahkan baris ini
use App\Http\Controllers\TestController;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\DashboardController;

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'is_active'])->name('dashboard');

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
    Route::delete('/test-scenarios/{testScenario}/histories', [App\Http\Controllers\TestScenarioController::class, 'destroyHistories'])->name('test-scenarios.histories.destroy');
    Route::get('/test-scenarios/{testScenario}/steps/{stepIndex}/edit', [App\Http\Controllers\TestScenarioController::class, 'editStep'])->name('test-scenarios.steps.edit');
    Route::put('/test-scenarios/{testScenario}/steps/{stepIndex}', [App\Http\Controllers\TestScenarioController::class, 'updateStep'])->name('test-scenarios.steps.update');
    Route::delete('/test-scenarios/{testScenario}/steps/{stepIndex}', [App\Http\Controllers\TestScenarioController::class, 'destroyStep'])->name('test-scenarios.steps.destroy');

    // Katalog Test Playwright
    Route::get('/tests', [App\Http\Controllers\TestCatalogController::class, 'index'])->name('tests.index');
    Route::get('/tests/create', [TestController::class, 'create'])->name('tests.create');
    Route::post('/tests', [TestController::class, 'store'])->name('tests.store');
    Route::post('/tests/sync', [App\Http\Controllers\TestCatalogController::class, 'sync'])->name('tests.sync');

    // Eksekusi Run Playwright
    Route::get('/runs', [App\Http\Controllers\RunController::class, 'index'])->name('runs.index');
    Route::get('/runs/create', [App\Http\Controllers\RunController::class, 'create'])->name('runs.create');
    Route::post('/runs', [App\Http\Controllers\RunController::class, 'store'])->name('runs.store');
    Route::get('/runs/{id}', [App\Http\Controllers\RunController::class, 'show'])->name('runs.show');
    Route::get('/runs/{id}/status', [App\Http\Controllers\RunController::class, 'status'])->name('runs.status');
    Route::post('/runs/{id}/cancel', [App\Http\Controllers\RunController::class, 'cancel'])->name('runs.cancel');
    // Penjadwalan Otomatis (Scheduler)
    Route::resource('schedules', App\Http\Controllers\ScheduleController::class);
    Route::post('/schedules/{schedule}/toggle', [App\Http\Controllers\ScheduleController::class, 'toggle'])->name('schedules.toggle');
    Route::post('/schedules/{schedule}/run-now', [App\Http\Controllers\ScheduleController::class, 'runNow'])->name('schedules.run-now');
});

require __DIR__.'/auth.php';