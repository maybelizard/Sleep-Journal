<?php

use App\Http\Controllers\SleepEntryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HabitController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('sleep-entries', SleepEntryController::class)
        ->except('show')
        ->parameters(['sleep-entries' => 'sleepEntry']);
    Route::resource('habits', HabitController::class)->only(['index', 'store', 'destroy']);
});

require __DIR__.'/auth.php';
