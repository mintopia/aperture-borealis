<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Ipv6JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Ipv6DetectionController extends Controller
{
    public function detect(Request $request, Ipv6JwtService $jwtService): JsonResponse
    {
        $ip = $request->ip();

        $jwt = $jwtService->sign($ip);

        return response()->json(['token' => $jwt]);
    }

    public function jwks(Ipv6JwtService $jwtService): JsonResponse
    {
        return response()->json($jwtService->jwks(), 200, [
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
