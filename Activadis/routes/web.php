<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ExternalActivityController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SignupController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/activities');

// Publiek voor externen: externe activiteiten bekijken en je zonder account aanmelden
Route::prefix('extern')->name('external.')->group(function () {
    Route::get('/', [ExternalActivityController::class, 'index'])->name('index');
    Route::get('/afmelden/{token}', [ExternalActivityController::class, 'cancel'])->name('cancel');
    Route::delete('/afmelden/{token}', [ExternalActivityController::class, 'destroy'])->middleware('throttle:10,1')->name('destroy');
    Route::get('/{activity}', [ExternalActivityController::class, 'show'])->whereNumber('activity')->name('show');
    Route::post('/{activity}', [ExternalActivityController::class, 'store'])->whereNumber('activity')->middleware('throttle:10,1')->name('store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('activities', ActivityController::class)->only(['create', 'store', 'edit', 'update', 'destroy'])->middleware('admin');
    Route::resource('activities', ActivityController::class)->only(['index', 'show']);

    Route::post('/activities/{activity}/signup', [SignupController::class, 'store'])->name('activities.signup');
    Route::patch('/activities/{activity}/signup', [SignupController::class, 'update'])->name('activities.signup.update');
    Route::delete('/activities/{activity}/signup', [SignupController::class, 'destroy'])->name('activities.signoff');

    // Gebruikersbeheer, alleen voor beheerders
    Route::middleware('admin')->group(function () {
        Route::resource('users', UserController::class)->only(['index', 'create', 'store', 'destroy']);
        Route::post('/users/{user}/invite', [UserController::class, 'resendInvite'])->name('users.invite');
    });
});

require __DIR__.'/auth.php';
