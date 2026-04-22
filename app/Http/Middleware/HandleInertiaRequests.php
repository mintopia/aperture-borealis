<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'nickname' => $user->nickname,
                    'email' => $user->email,
                    'is_admin' => (bool) $user->is_admin,
                    'avatar_url' => $user->avatar_url ?? null,
                ] : null,
            ],
            'theme' => [
                'accent_hue' => (int) Setting::fetch('accent_hue', 55),
                'color_mode' => Setting::fetch('color_mode', 'dark'),
                'site_title' => Setting::fetch('site_title', ''),
                'custom_css' => Setting::fetch('custom_css', ''),
            ],
            'legal' => [
                'terms_url' => Setting::fetch('terms_url', ''),
                'privacy_url' => Setting::fetch('privacy_url', ''),
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ]);
    }
}
