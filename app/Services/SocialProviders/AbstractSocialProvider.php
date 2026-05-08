<?php

namespace App\Services\SocialProviders;

use App\Enums\DeviceCodeStatus;
use App\Enums\SettingType;
use App\Exceptions\SocialProviderException;
use App\Models\DeviceCode;
use App\Models\SocialProvider;
use App\Models\SocialProviderSetting;
use App\Models\User;
use App\Services\Contracts\SocialProviderContract;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;

abstract class AbstractSocialProvider implements SocialProviderContract
{
    protected string $name;

    protected string $code;

    protected string $socialiteProviderCode;

    public function __construct(protected ?SocialProvider $provider = null, protected ?string $redirectUrl = null)
    {
        $this->resolveRedirectUrl();
    }

    protected function resolveRedirectUrl(?string $redirectUrl = null): void
    {
        if ($this->redirectUrl !== null) {
            return;
        }
        if (Auth::guest() && $this->provider && $this->provider->auth_enabled) {
            $this->redirectUrl = route('admin.login.provider.callback', ['provider' => $this->code]);
        } else {
            $this->redirectUrl = route('auth.provider.callback', ['provider' => $this->code]);
        }
    }

    public function install(): SocialProvider
    {
        $this->provider = SocialProvider::whereCode($this->code)->first();
        if ($this->provider) {
            // Ensure settings exist/are up-to-date for existing provider (idempotent)
            $this->installSettings();

            return $this->provider;
        }

        $provider = new SocialProvider;
        $this->provider = $provider;
        $provider->name = $this->name;
        $provider->code = $this->code;
        $provider->provider_class = get_called_class();
        $provider->enabled = false;

        DB::transaction(function () use ($provider) {
            $provider->save();
            $this->installSettings();
        });

        $provider->save();

        return $this->provider;
    }

    public function installSettings(): void
    {
        foreach ($this->configMapping() as $code => $config) {
            $setting = $this->provider->settings()->whereCode($code)->first();
            if (! $setting) {
                $setting = new SocialProviderSetting;
                $setting->provider()->associate($this->provider);
                $setting->code = $code;
                // Only set value initially
                $setting->value = $config->value ?? null;
            }
            $setting->name = $config->name;
            $setting->validation = $config->validation ?? null;
            $setting->encrypted = $config->encrypted ?? false;
            $setting->description = $config->description ?? null;
            $setting->type = $config->type ?? SettingType::stString;
            if ($setting->isDirty()) {
                $setting->save();
            }
        }
    }

    public function configMapping(): array
    {
        return [
            'client_id' => (object) [
                'name' => 'Client ID',
                'validation' => 'required|string',
            ],
            'client_secret' => (object) [
                'name' => 'Client Secret',
                'validation' => 'required|string',
                'encrypted' => true,
            ],
        ];
    }

    public function redirect(): RedirectResponse
    {
        return $this->getSocialiteProvider()->redirect();
    }

    public function getSocialiteProvider()
    {
        return Socialite::driver($this->socialiteProviderCode);
    }

    public function code(DeviceCode $deviceCode): DeviceCode
    {
        $remoteUser = $this->getSocialiteProvider()->user();
        $this->updateDeviceCode($deviceCode, $remoteUser);

        return $deviceCode;
    }

    public function user(): User
    {
        $remoteUser = $this->getSocialiteProvider()->user();

        // Find the account
        $user = $this->provider->users()->whereExternalId($remoteUser->getId())->first();
        if ($user && ($user->id !== $remoteUser->id)) {
            throw new SocialProviderException('Account is already associated with another user');
        }

        // Find the email
        if (! $user) {
            $user = User::whereEmail($remoteUser->getEmail())->first();
        }

        if (! $user) {
            throw new SocialProviderException('You do not have access to this application');
        }

        if ($user->external_id !== null && $user->external_id !== $remoteUser->getId()) {
            throw new SocialProviderException('Account is already associated with another external_id');
        }

        if ($user === null) {
            $localUser = new User;
            $localUser->nickname = $this->resolveNickname($remoteUser);
            $localUser->save();
        }

        $this->updateUser($user, $remoteUser);
        $user->save();

        return $user;
    }

    protected function resolveNickname(SocialiteUser $remoteUser): ?string
    {
        return $remoteUser->getNickname() ?? $remoteUser->getName();
    }

    protected function updateUser(User $user, SocialiteUser $remoteUser): void
    {
        if ($remoteUser instanceof \Laravel\Socialite\One\User) {
            throw new SocialProviderException('Unable to update local user based on a Socialite One User');
        }
        $user->refresh_token = $remoteUser->refreshToken;
        $user->access_token = $remoteUser->token;
        $user->access_token_expires_at = CarbonImmutable::now()->addSeconds($remoteUser->expiresIn);
        $user->nickname = $this->resolveNickname($remoteUser);
    }

    protected function updateDeviceCode(DeviceCode $deviceCode, SocialiteUser $remoteUser): void
    {
        if ($remoteUser instanceof \Laravel\Socialite\One\User) {
            throw new SocialProviderException('Unable to updat device code based on a Socialite One User');
        }
        $deviceCode->access_token = $remoteUser->token;
        $deviceCode->refresh_token = $remoteUser->refreshToken;
        $deviceCode->access_token_expires_at = CarbonImmutable::now()->addSeconds($remoteUser->expiresIn);
        $deviceCode->nickname = $this->resolveNickname($remoteUser);
        $deviceCode->email = $remoteUser->getEmail();
        $deviceCode->external_id = $remoteUser->getId();
        $deviceCode->avatar_url = $remoteUser->getAvatar();
        $deviceCode->status = DeviceCodeStatus::dcsSuccessful;
        $deviceCode->save();
    }
}
