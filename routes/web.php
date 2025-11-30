<?php

use App\Http\Controllers\TaskController;

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Dashboard route removed - redirecting to tasks instead
// Authenticated routes
Route::middleware('auth')->group(function () {
    // Task routes
    Route::resource('tasks', TaskController::class)->except(['show']);
    Route::post('tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');
    Route::get('/tasks/statistics', [TaskController::class, 'statistics'])->name('tasks.statistics');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
