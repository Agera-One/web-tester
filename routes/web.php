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
Route::get('/dashboard/live-data', [DashboardController::class, 'liveData'])->middleware(['auth', 'is_active'])->name('dashboard.live-data');

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
    Route::post('/tests/{suite}/toggle', [App\Http\Controllers\TestCatalogController::class, 'toggle'])->name('tests.toggle')->middleware('role:admin,developer');
    Route::post('/tests/cases/{case}/toggle', [App\Http\Controllers\TestCatalogController::class, 'toggleCase'])->name('tests.cases.toggle')->middleware('role:admin,developer');
    Route::get('/tests/edit', [TestController::class, 'edit'])->name('tests.edit');
    Route::put('/tests/update', [TestController::class, 'update'])->name('tests.update');
    Route::delete('/tests/destroy', [TestController::class, 'destroy'])->name('tests.destroy');

    // Eksekusi Run Playwright
    Route::get('/runs', [App\Http\Controllers\RunController::class, 'index'])->name('runs.index');
    Route::get('/runs/create', [App\Http\Controllers\RunController::class, 'create'])->name('runs.create');
    Route::post('/runs', [App\Http\Controllers\RunController::class, 'store'])->name('runs.store');
    Route::get('/runs/{id}', [App\Http\Controllers\RunController::class, 'show'])->name('runs.show');
    Route::get('/runs/{id}/status', [App\Http\Controllers\RunController::class, 'status'])->name('runs.status');
    Route::post('/runs/{id}/cancel', [App\Http\Controllers\RunController::class, 'cancel'])->name('runs.cancel');
    Route::get('/runs/{id}/report/{file?}', [App\Http\Controllers\RunController::class, 'report'])->where('file', '.*')->name('runs.report');
    
    // Artefak Hasil Pengujian & Tren Case (F6 & F7)
    Route::get('/results/{id}/artifact/{type}', [App\Http\Controllers\ResultController::class, 'artifact'])->name('results.artifact');
    Route::get('/cases/{id}/history', [App\Http\Controllers\ResultController::class, 'history'])->name('cases.history');
    
    // Penjadwalan Otomatis (Scheduler)
    Route::resource('schedules', App\Http\Controllers\ScheduleController::class);
    Route::post('/schedules/{schedule}/toggle', [App\Http\Controllers\ScheduleController::class, 'toggle'])->name('schedules.toggle');
    Route::post('/schedules/{schedule}/run-now', [App\Http\Controllers\ScheduleController::class, 'runNow'])->name('schedules.run-now');

    // Pengaturan Akun (Profile)
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [App\Http\Controllers\ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::delete('/profile/avatar', [App\Http\Controllers\ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');
    Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');

});

require __DIR__.'/auth.php';