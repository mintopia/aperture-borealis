<?php

namespace App\Http\Controllers;

use App\Enums\DeviceCodeStatus;
use App\Http\Requests\AuthRequest;
use App\Http\Requests\OAuth2\DeviceRequest;
use App\Http\Requests\OAuth2\TokenRequest;
use App\Http\Resources\DeviceCodeResource;
use App\Http\Resources\TokenResource;
use App\Models\DeviceCode;
use App\Models\SocialProvider;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Two\InvalidStateException;

class OAuthController extends Controller
{
    public function device(DeviceRequest $request)
    {
        $device = new DeviceCode();
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
            return response()->json((object)[
                'error' => 'access_denied',
            ], 403);
        }

        if (CarbonImmutable::now()->isAfter($deviceCode->expires_at)) {
            return response()->json((object)[
                'error' => 'token_expired',
            ], 403);
        }

        if ($deviceCode->access_token === null) {
            return response()->json((object)[
                'error' => 'authorization_pending',
            ], 403);
        }
    }

    public function auth(AuthRequest $request)
    {
        if ($request->has('code')) {
            $deviceCode = $this->getDeviceCode($request->input('code'));
            if ($deviceCode !== null) {
                $request->session()->put('device_code', $deviceCode->user_code);
                return $deviceCode->provider->redirect(false);
            }
        }

        return view('auth.code', [
            'length' => config('borealis.usercode.length'),
        ]);
    }

    public function return(Request $request)
    {
        $code = $request->session()->get('device_code');
        $deviceCode = $this->getDeviceCode($code);
        if (!$deviceCode) {
            $request->session()->forget('device_code');
            return response()
                ->redirectToRoute('auth')
                ->with('errorMessage', 'Unable to process login');
        }

        try {
            $deviceCode->provider->getProvider()->code($deviceCode);
        }  catch(Exception $ex) {
            $deviceCode->status = DeviceCodeStatus::dcsFailed;
            $deviceCode->save();
        }
        $request->session()->forget('device_code');
        if ($deviceCode->status !== DeviceCodeStatus::dcsSuccessful) {
            return response()
                ->redirectToRoute('auth')
                ->with('errorMessage', 'Unable to process login');
        }
        return view ('auth.return', [
            'deviceCode' => $deviceCode,
        ]);
    }

    protected function getDeviceCode(?string $code): ?DeviceCode
    {
        if ($code === null) {
            return null;
        }

        $deviceCode = DeviceCode::whereUserCode($code)->first();
        if (!$deviceCode) {
            session()->now('errorMessage', 'Code could not be found');
            return null;
        }

        if ($deviceCode->status !== DeviceCodeStatus::dcsPending) {
            session()->now('errorMessage', 'Code is not valid');
            return null;
        }

        if ($deviceCode->expires_at->isBefore(CarbonImmutable::now())) {
            session()->now('errorMessage', 'Code has expired');
            return null;
        }

        return $deviceCode;
    }
}
