<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GeneralController extends Controller
{
    public function show()
    {
        return Inertia::render('Admin/Settings/General', [
            'settings' => [
                'terms_url' => Setting::fetch('terms_url', ''),
                'privacy_url' => Setting::fetch('privacy_url', ''),
                'device_code_expiry' => Setting::fetch('device_code_expiry', '300'),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'terms_url' => 'nullable|url|max:500',
            'privacy_url' => 'nullable|url|max:500',
            'device_code_expiry' => 'required|integer|min:60|max:3600',
        ]);

        foreach (['terms_url', 'privacy_url', 'device_code_expiry'] as $key) {
            $setting = Setting::where('code', $key)->first();
            if ($setting) {
                $setting->value = $request->input($key, '');
                $setting->save();
            }
        }

        return back()->with('success', 'General settings updated.');
    }
}
