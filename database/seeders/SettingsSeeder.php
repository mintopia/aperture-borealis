<?php

namespace Database\Seeders;

use App\Enums\SettingType;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'name' => (object)[
                'name' => 'Site Name',
                'default' => 'Borealis',
                'validation' => 'required|string|max:200|min:2',
            ],
            'accent_hue' => (object)[
                'name' => 'Accent Hue',
                'default' => '55',
                'validation' => 'required|integer|min:0|max:360',
            ],
            'color_mode' => (object)[
                'name' => 'Color Mode',
                'default' => 'dark',
                'validation' => 'required|string|in:light,dark,system',
            ],
            'site_title' => (object)[
                'name' => 'Site Title',
                'default' => '',
                'validation' => 'sometimes|nullable|string|max:200',
            ],
            'custom_css' => (object)[
                'name' => 'Custom CSS',
                'default' => '',
                'validation' => 'sometimes|nullable|string',
            ],
            'terms_url' => (object)[
                'name' => 'Terms URL',
                'default' => '',
                'validation' => 'sometimes|nullable|string|url:http,https',
            ],
            'privacy_url' => (object)[
                'name' => 'Privacy URL',
                'default' => '',
                'validation' => 'sometimes|nullable|string|url:http,https',
            ],
            'device_code_expiry' => (object)[
                'name' => 'Device Code Expiry (seconds)',
                'default' => '300',
                'validation' => 'required|integer|min:60|max:3600',
            ],
        ];
        foreach ($settings as $code => $setting) {
            $this->updateSetting($code, $setting);
        }
    }

    protected function updateSetting(string $code, object $data): void
    {
        $setting = Setting::whereCode($code)->first();
        if (!$setting) {
            $setting = new Setting();
            $setting->code = $code;
            $setting->value = $data->default ?? null;
        }
        $setting->hidden = $data->hidden ?? false;
        $setting->name = $data->name;
        $setting->description = $data->description ?? null;
        $setting->encrypted = $data->encrypted ?? false;
        $setting->validation = $data->validation ?? '';
        $setting->type = $data->type ?? SettingType::stString;
        $setting->save();
    }
}
