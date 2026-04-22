<?php

namespace App\Http\Controllers\Customer;

use App\Enums\DeviceCodeStatus;
use App\Http\Controllers\Controller;
use App\Models\DeviceCode;
use App\Models\SocialProvider;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DeviceFlowController extends Controller
{
    public function code()
    {
        return Inertia::render('Customer/Code');
    }

    public function submitCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:4',
        ]);

        $deviceCode = DeviceCode::where('user_code', strtoupper($request->code))
            ->where('status', DeviceCodeStatus::dcsPending)
            ->where('expires_at', '>', now())
            ->first();

        if (!$deviceCode) {
            return back()->withErrors(['code' => 'Invalid or expired code. Please try again.']);
        }

        $request->session()->put('device_code_id', $deviceCode->id);

        return redirect('/auth/providers');
    }

    public function providers(Request $request)
    {
        $deviceCodeId = $request->session()->get('device_code_id');

        if (!$deviceCodeId) {
            return redirect('/auth');
        }

        $deviceCode = DeviceCode::find($deviceCodeId);

        if (!$deviceCode || $deviceCode->status !== DeviceCodeStatus::dcsPending || $deviceCode->expires_at <= now()) {
            $request->session()->forget('device_code_id');
            return redirect('/auth/error')->with('reason', 'expired');
        }

        $providers = SocialProvider::where('enabled', true)->get();

        return Inertia::render('Customer/Providers', [
            'providers' => $providers->map(fn ($p) => [
                'code' => $p->code,
                'name' => $p->name,
            ]),
        ]);
    }

    public function providerRedirect(Request $request, string $provider)
    {
        $deviceCodeId = $request->session()->get('device_code_id');

        if (!$deviceCodeId) {
            return redirect('/auth');
        }

        $deviceCode = DeviceCode::find($deviceCodeId);

        if (!$deviceCode || $deviceCode->status !== DeviceCodeStatus::dcsPending || $deviceCode->expires_at <= now()) {
            $request->session()->forget('device_code_id');
            return redirect('/auth/error')->with('reason', 'expired');
        }

        $socialProvider = SocialProvider::where('code', $provider)->where('enabled', true)->firstOrFail();

        return $socialProvider->redirect(false);
    }

    public function providerCallback(Request $request, string $provider)
    {
        $deviceCodeId = $request->session()->get('device_code_id');

        if (!$deviceCodeId) {
            return redirect('/auth/error')->with('reason', 'session');
        }

        $deviceCode = DeviceCode::find($deviceCodeId);

        if (!$deviceCode || $deviceCode->status !== DeviceCodeStatus::dcsPending || $deviceCode->expires_at <= now()) {
            $request->session()->forget('device_code_id');
            return redirect('/auth/error')->with('reason', 'invalid');
        }

        try {
            $socialProvider = SocialProvider::where('code', $provider)->where('enabled', true)->firstOrFail();
            $socialProvider->getProvider()->code($deviceCode);

            $request->session()->forget('device_code_id');
            return redirect('/auth/success');
        } catch (\Exception $e) {
            $deviceCode->status = DeviceCodeStatus::dcsFailed;
            $deviceCode->save();
            $request->session()->forget('device_code_id');
            return redirect('/auth/error')->with('reason', 'auth_failed');
        }
    }

    public function success()
    {
        return Inertia::render('Customer/Success');
    }

    public function error(Request $request)
    {
        return Inertia::render('Customer/Error', [
            'reason' => session('reason', 'unknown'),
        ]);
    }
}
