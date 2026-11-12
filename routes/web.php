<?php

use App\Http\Controllers\Api\PizarronActivityController;
use App\Http\Controllers\CustomerRegistrationController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\RoleLoginController;
use App\Livewire\OrderTracking;
use App\Livewire\QuickOrder;
use App\Livewire\RiderPanel;
use Illuminate\Support\Facades\Route;

// Página principal del cliente
Route::get('/', QuickOrder::class)->name('home');
Route::get('/negocio/{business}', QuickOrder::class)->whereNumber('business')->name('business.menu');
Route::redirect('/acceso', '/login')->name('access');

Route::get('/login', [RoleLoginController::class, 'showGeneral'])->name('login');
Route::post('/login', [RoleLoginController::class, 'authenticateGeneral'])
    ->middleware('throttle:6,1')
    ->name('login.store');
Route::redirect('/rider/login', '/login')->name('rider.login');
Route::post('/rider/login', [RoleLoginController::class, 'authenticate'])->defaults('role', 'rider')->middleware('throttle:6,1')->name('rider.login.store');
Route::redirect('/customer/login', '/login')->name('customer.login');
Route::post('/customer/login', [RoleLoginController::class, 'authenticate'])->defaults('role', 'customer')->middleware('throttle:6,1')->name('customer.login.store');
Route::post('/logout', [RoleLoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('guest')->group(function () {
    Route::get('/customer/register', [CustomerRegistrationController::class, 'create'])->name('customer.register');
    Route::post('/customer/register', [CustomerRegistrationController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('customer.register.store');

    Route::get('/forgot-password', [PasswordResetController::class, 'createLinkRequest'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
        ->middleware('throttle:6,1')
        ->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'createResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])
        ->middleware('throttle:6,1')
        ->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

// Página de rastreo para el cliente
Route::get('/rastreo/{id}', OrderTracking::class)->middleware('auth')->name('order.tracking');
Route::get('/rider', RiderPanel::class)->middleware('auth')->name('rider.panel');

// Pizarrón activity (simple web alias for the API)
Route::get('/pizarron/activity', [PizarronActivityController::class, 'index']);
