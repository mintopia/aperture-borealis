<?php

namespace App\Http\Controllers\Api;

use App\Enums\DeviceCodeStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\OAuth2\DeviceRequest;
use App\Http\Requests\OAuth2\TokenRequest;
use App\Http\Resources\DeviceCodeResource;
use App\Http\Resources\TokenResource;
use App\Models\DeviceCode;
use App\Models\SocialProvider;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;

class OAuthController extends Controller
{
    public function device(DeviceRequest $request)
    {
        $device = new DeviceCode;
        $device->client()->associate(Auth::user());
        $provider = SocialProvider::whereEnabled(true)
            ->whereCode($request->input('scope'))
            ->first();
        $device->provider()->associate($provider);
        $device->save();

        return new DeviceCodeResource($device);
    }

    public function token(TokenRequest $request)
    {
        $deviceCode = Auth::user()
            ->codes()
            ->whereDeviceCode($request->input('device_code'))
            ->firstOrFail();

        if ($deviceCode->status === DeviceCodeStatus::dcsSuccessful) {
            return new TokenResource($deviceCode);
        }

        if ($deviceCode->status === DeviceCodeStatus::dcsFailed) {
            return response()->json((object) [
                'error' => 'access_denied',
            ], 403);
        }

        if (CarbonImmutable::now()->isAfter($deviceCode->expires_at)) {
            return response()->json((object) [
                'error' => 'token_expired',
            ], 403);
        }

        if ($deviceCode->access_token === null) {
            return response()->json((object) [
                'error' => 'authorization_pending',
            ], 403);
        }
    }
}
