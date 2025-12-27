<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SocialProviderController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::get('/logout', [UserController::class, 'logout'])->name('logout');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::post('/profile', [UserController::class, 'profileUpdate'])->name('profile.update');

    Route::resource('users', UserController::class);
    Route::resource('clients',  ClientController::class);
    Route::resource('socialproviders', SocialProviderController::class);
    Route::resource('settings', SettingController::class);
    Route::resource('themes',  ThemeController::class);
});

Route::middleware('guest')->group(function () {
    Route::get('login', [UserController::class, 'login'])->name('login');
    Route::get('login/{socialprovider:code}', [UserController::class, 'loginRedirect'])->name('login.redirect');
    Route::get('login/{socialprovider:code}/return', [UserController::class, 'loginReturn'])->name('login.return');

    Route::get('auth', [OAuthController::class, 'auth'])->name('auth');
    Route::get('auth/{socialprovider:code}/return', [OAuthController::class, 'return'])->name('auth.return');
});
