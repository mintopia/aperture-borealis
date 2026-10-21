<?php

use App\Http\Controllers\Ipv6DetectionController;
use Illuminate\Support\Facades\Route;

Route::get('/ipv6', [Ipv6DetectionController::class, 'detect']);
Route::options('/ipv6', fn () => response('', 204, [
    'Access-Control-Allow-Origin' => '*',
    'Access-Control-Allow-Methods' => 'GET, OPTIONS',
    'Access-Control-Allow-Headers' => '*',
    'Access-Control-Max-Age' => '86400',
]));

Route::get('/.well-known/jwks.json', [Ipv6DetectionController::class, 'jwks']);
Route::options('/.well-known/jwks.json', fn () => response('', 204, [
    'Access-Control-Allow-Origin' => '*',
    'Access-Control-Allow-Methods' => 'GET, OPTIONS',
    'Access-Control-Allow-Headers' => '*',
    'Access-Control-Max-Age' => '86400',
]));
