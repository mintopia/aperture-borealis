<?php

use App\Http\Controllers\Ipv6DetectionController;
use Illuminate\Support\Facades\Route;

Route::get('/ipv6', [Ipv6DetectionController::class, 'detect']);
Route::get('/.well-known/jwks.json', [Ipv6DetectionController::class, 'jwks']);
