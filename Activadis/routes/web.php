<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SetPasswordController;

Route::redirect('/', '/activities');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('activities', ActivityController::class)->only(['create', 'store'])->middleware('admin');
    Route::resource('activities', ActivityController::class)->only(['index', 'show']);
    Route::resource('users', UserController::class)->only(['index', 'show', 'create', 'store', 'destroy'])->middleware('admin');

    // 
    Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');


    });
    // gebruiker gemaakt mail
    Route::get('set-password/{token}', [SetPasswordController::class, 'create'])
        ->name('password.set');

    Route::post('set-password', [SetPasswordController::class, 'store'])
        ->name('password.set.store');
});

require __DIR__.'/auth.php';
