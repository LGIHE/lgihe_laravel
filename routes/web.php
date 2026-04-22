<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\PasswordSetupController;

Route::get('/', function () {
    return view('welcome');
});

// Password Setup Routes
Route::get('/set-password', [PasswordSetupController::class, 'showSetupForm'])
    ->name('password.setup.form');

Route::post('/set-password', [PasswordSetupController::class, 'setupPassword'])
    ->name('password.setup.submit');

Route::get('/password-setup-success', [PasswordSetupController::class, 'showSuccessPage'])
    ->middleware('auth')
    ->name('password.setup.success');
