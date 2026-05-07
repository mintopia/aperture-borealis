<?php

namespace App\Console\Commands;

use App\Enums\SettingType;
use App\Models\SocialProvider;
use App\Models\SocialProviderSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Console\Helper\TableSeparator;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\table;
use function Laravel\Prompts\text;

class SetupProviderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'borealis:setup-provider';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure social providers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->displayProviders();
        $provider = $this->getProvider();
        if ($provider === null) {
            return self::SUCCESS;
        }
        DB::transaction(function () use ($provider) {
            $this->updateSettings($provider);
            if (confirm('Do you want to save these changes?')) {
                DB::commit();
                $this->output->info('Changes have been saved');
            } else {
                DB::rollBack();
                $this->output->info('Changes have not been saved');
            }
        });

        $this->displayProviders();

        return self::SUCCESS;
    }

    protected function updateSettings($provider): void
    {
        $settings = $provider->settings()->orderBy('order', 'ASC')->get();
        foreach ($settings as $setting) {
            $setting->value = $this->updateSetting($setting);
            $setting->save();
        }
        $provider->enabled = confirm('Enable this provider?');
        $provider->save();
    }

    protected function updateSetting(SocialProviderSetting $setting): mixed
    {
        if ($setting->type === SettingType::stBoolean) {
            return confirm(
                label: $setting->name,
                default: $setting->value ?? false,
                hint: $setting->description ?? '',
            );
        } elseif ($setting->encrypted) {
            $newValue = password(
                label: $setting->name,
                hint: $setting->description ?? '',
            );
            if ($newValue === '') {
                return $setting->value;
            }

            return $newValue;
        } else {
            return text(
                label: $setting->name,
                default: $setting->value ?? '',
                hint: $setting->description ?? '',
            );
        }
    }

    protected function getProvider(): ?SocialProvider
    {
        $providers = SocialProvider::orderBy('name', 'ASC')->get();
        $code = select(
            label: 'Social Provider',
            options: $providers->mapWithKeys(function (SocialProvider $provider) {
                return [$provider->code => $provider->name];
            })->put('none', 'None'),
            hint: 'Select the social provider to configure'
        );
        if ($code === 'none') {
            return null;
        }

        return $providers->where('code', $code)->first();
    }

    protected function displayProviders(): void
    {
        $providers = SocialProvider::orderBy('name', 'ASC')->get();
        $headers = [
            'Name',
            'Enabled',
            'Settings',
            'Redirect URLs',
        ];
        $rows = [];
        foreach ($providers as $i => $provider) {
            if ($i > 0) {
                $rows[] = new TableSeparator;
            }
            $rows[] = [
                $provider->name,
                $provider->enabled ? '<fg=green>Yes</>' : '<fg=red>No</>',
                $provider
                    ->settings()
                    ->orderBy('order', 'ASC')
                    ->get()
                    ->map(function (SocialProviderSetting $setting) {
                        $value = $setting->value;
                        if ($setting->encrypted) {
                            $maskLength = 6;
                            if (strlen($value < 12)) {
                                $maskLength = max(floor(strlen($value / 4)), 0);
                            }
                            $value = Str::mask($value, '*', $maskLength);
                        }
                        if ($value === null) {
                            $value = '<fg=gray>None</>';
                        }

                        return "<fg=gray>{$setting->name}:</> {$value}";
                    })->filter()->implode(PHP_EOL),
                implode(PHP_EOL, [
                    route('admin.login.provider.callback', ['provider' => $provider->code]),
                    route('auth.provider.callback', ['provider' => $provider->code]),
                ]),
            ];
        }
        table($headers, $rows);
    }
}
