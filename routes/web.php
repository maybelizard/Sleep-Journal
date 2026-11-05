<?php

use App\Http\Controllers\SleepEntryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HabitController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::resource('sleep-entries', SleepEntryController::class)
        ->except('show')
        ->parameters(['sleep-entries' => 'sleepEntry']);
    Route::resource('habits', HabitController::class)->only(['index', 'store', 'destroy']);
});

require __DIR__.'/auth.php';
