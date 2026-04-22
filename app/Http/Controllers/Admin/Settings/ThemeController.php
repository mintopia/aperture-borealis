<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ThemeController extends Controller
{
    public function show()
    {
        return Inertia::render('Admin/Settings/Theme', [
            'settings' => [
                'accent_hue' => Setting::fetch('accent_hue', '55'),
                'color_mode' => Setting::fetch('color_mode', 'dark'),
                'site_title' => Setting::fetch('site_title', ''),
                'custom_css' => Setting::fetch('custom_css', ''),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'accent_hue' => 'required|string',
            'color_mode' => 'required|in:light,dark,system',
            'site_title' => 'nullable|string|max:255',
            'custom_css' => 'nullable|string|max:10000',
        ]);

        foreach (['accent_hue', 'color_mode', 'site_title', 'custom_css'] as $key) {
            $setting = Setting::where('code', $key)->first();
            if ($setting) {
                $setting->value = $request->input($key, '');
                $setting->save();
            }
        }

        return back()->with('success', 'Theme settings updated.');
    }
}
