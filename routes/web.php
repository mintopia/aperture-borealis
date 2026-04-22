<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\LogoutController;
use App\Http\Controllers\Admin\Auth\PasskeyController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProviderController;
use App\Http\Controllers\Admin\Settings\GeneralController;
use App\Http\Controllers\Admin\Settings\ThemeController;
use App\Http\Controllers\Customer\DeviceFlowController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

// Customer device flow
Route::prefix('/auth')->group(function () {
    Route::get('/', [DeviceFlowController::class, 'code'])->name('auth');
    Route::post('/', [DeviceFlowController::class, 'submitCode']);
    Route::get('/providers', [DeviceFlowController::class, 'providers']);
    Route::get('/success', [DeviceFlowController::class, 'success']);
    Route::get('/error', [DeviceFlowController::class, 'error']);
    Route::get('/{provider}/redirect', [DeviceFlowController::class, 'providerRedirect'])->name('auth.provider.redirect');
    Route::get('/{provider}/callback', [DeviceFlowController::class, 'providerCallback'])->name('auth.provider.callback');
});

// Admin auth (public)
Route::prefix('/admin')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [LoginController::class, 'passwordLogin']);
    Route::get('/login/{provider}/redirect', [LoginController::class, 'socialRedirect'])->name('admin.login.provider.redirect');
    Route::get('/login/{provider}/callback', [LoginController::class, 'socialCallback'])->name('admin.login.provider.callback');
    Route::post('/login/passkey/options', [PasskeyController::class, 'assertionOptions']);
    Route::post('/login/passkey/verify', [PasskeyController::class, 'verify']);
});

// Admin protected
Route::prefix('/admin')->middleware(EnsureAdmin::class)->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::post('/logout', [LogoutController::class, 'logout'])->name('admin.logout');

    Route::get('/providers', [ProviderController::class, 'index']);
    Route::put('/providers/{provider}', [ProviderController::class, 'update']);

    Route::get('/clients', [ClientController::class, 'index']);
    Route::get('/clients/create', [ClientController::class, 'create']);
    Route::post('/clients', [ClientController::class, 'store']);
    Route::get('/clients/{client}', [ClientController::class, 'show']);
    Route::put('/clients/{client}', [ClientController::class, 'update']);
    Route::delete('/clients/{client}', [ClientController::class, 'destroy']);

    Route::get('/settings/theme', [ThemeController::class, 'show'])->name('admin.settings.theme');
    Route::put('/settings/theme', [ThemeController::class, 'update']);
    Route::get('/settings/general', [GeneralController::class, 'show'])->name('admin.settings.general');
    Route::put('/settings/general', [GeneralController::class, 'update']);

    Route::post('/passkey/register/options', [PasskeyController::class, 'registerOptions']);
    Route::post('/passkey/register', [PasskeyController::class, 'register']);
});
