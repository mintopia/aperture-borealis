<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialProvider;
use App\Models\SocialProviderSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProviderController extends Controller
{
    public function index()
    {
        $providers = SocialProvider::with('settings')->get()->map(fn ($provider) => [
            'id' => $provider->id,
            'name' => $provider->name,
            'code' => $provider->code,
            'enabled' => $provider->enabled,
            'settings' => $provider->settings->sortBy('order')->values()->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'code' => $s->code,
                'value' => $s->hidden ? '' : $s->value,
                'encrypted' => $s->encrypted,
                'hidden' => $s->hidden,
            ]),
        ]);

        return Inertia::render('Admin/Providers/Index', [
            'providers' => $providers,
        ]);
    }

    public function update(Request $request, SocialProvider $provider)
    {
        $request->validate([
            'enabled' => 'sometimes|boolean',
            'settings' => 'sometimes|array',
            'settings.*.id' => 'required_with:settings|exists:social_provider_settings,id',
            'settings.*.value' => 'required_with:settings|string',
        ]);

        if ($request->has('enabled')) {
            $provider->enabled = $request->boolean('enabled');
            $provider->save();
        }

        if ($request->has('settings')) {
            foreach ($request->input('settings') as $settingData) {
                $setting = SocialProviderSetting::findOrFail($settingData['id']);
                $setting->value = $settingData['value'];
                $setting->save();
            }
        }

        return back()->with('success', 'Provider updated.');
    }
}
