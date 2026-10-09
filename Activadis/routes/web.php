<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SignupController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/activities');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('activities', ActivityController::class)->only(['create', 'store'])->middleware('admin');
    Route::resource('activities', ActivityController::class)->only(['index', 'show']);

    Route::post('/activities/{activity}/signup', [SignupController::class, 'store'])->name('activities.signup');
    Route::delete('/activities/{activity}/signup', [SignupController::class, 'destroy'])->name('activities.signoff');
});

require __DIR__.'/auth.php';
