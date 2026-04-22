<?php
use App\Http\Controllers\Api\OAuthController;
use Illuminate\Support\Facades\Route;

Route::post('device', [OAuthController::class, 'device'])->name('device');
Route::post('token', [OAuthController::class, 'token'])->name('token');
