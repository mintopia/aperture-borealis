<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialProvider;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    public function showLogin()
    {
        $providers = SocialProvider::where('enabled', true)->get()->map(fn ($p) => [
            'code' => $p->code,
            'name' => $p->name,
        ]);

        return Inertia::render('Admin/Login', [
            'providers' => $providers,
        ]);
    }

    public function passwordLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
            'is_admin' => true,
        ])) {
            return back()->withErrors(['email' => 'Invalid credentials.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function socialRedirect(string $provider)
    {
        $socialProvider = SocialProvider::where('code', $provider)->where('enabled', true)->firstOrFail();

        return Socialite::driver($socialProvider->code)->redirect();
    }

    public function socialCallback(Request $request, string $provider)
    {
        $socialProvider = SocialProvider::where('code', $provider)->where('enabled', true)->firstOrFail();

        try {
            $socialUser = Socialite::driver($socialProvider->code)->user();
        } catch (\Exception $e) {
            return redirect()->route('admin.login')->with('error', 'Authentication failed. Please try again.');
        }

        $user = User::where('external_id', $socialUser->getId())
            ->where('social_provider_id', $socialProvider->id)
            ->first();

        if (! $user) {
            $user = User::create([
                'nickname' => $socialUser->getNickname() ?? $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'external_id' => $socialUser->getId(),
                'social_provider_id' => $socialProvider->id,
                'access_token' => $socialUser->token,
                'refresh_token' => $socialUser->refreshToken,
                'access_token_expires_at' => $socialUser->expiresIn ? now()->addSeconds($socialUser->expiresIn) : null,
            ]);
        } else {
            $user->update([
                'access_token' => $socialUser->token,
                'refresh_token' => $socialUser->refreshToken,
                'access_token_expires_at' => $socialUser->expiresIn ? now()->addSeconds($socialUser->expiresIn) : null,
            ]);
        }

        if (! $user->is_admin) {
            return redirect()->route('admin.login')->with('error', "You don't have admin access.");
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }
}
