<?php

namespace App\Models;

use App\Models\Traits\ToString;
use App\Services\Contracts\SocialProviderContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperSocialProvider
 */
class SocialProvider extends Model
{
    use ToString;
    protected array $_settings = [];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function redirect(bool $login = true)
    {
        $uri = $login ? $this->getLoginReturnURI() : $this->getAuthReturnURI();
        return $this->getProvider($uri)->redirect();
    }

    public function getProvider(?string $redirectUrl = null): SocialProviderContract
    {
        if (app()->bound($this->provider_class)) {
            return app()->make($this->provider_class, ['provider' => $this, 'redirectUrl' => $redirectUrl]);
        }
        return new $this->provider_class($this, $redirectUrl);
    }

    public function user(bool $login = true): ?User
    {
        $uri = $login ? $this->getLoginReturnURI() : $this->getAuthReturnURI();
        return $this->getProvider($uri)->user();
    }

    public function getLoginReturnURI(): string
    {
        return route('login.return', ['socialprovider' => $this->code]);
    }

    public function getAuthReturnURI(): string
    {
        return route('auth.return', ['socialprovider' => $this->code]);
    }

    public function configMapping(): array
    {
        return $this->getProvider()->configMapping();
    }

    public function getSetting(string $code): mixed
    {
        if (isset($this->_settings[$code])) {
            return $this->_settings[$code];
        }
        $setting = $this->settings()->whereCode($code)->first();
        if (!$setting) {
            $this->_settings[$code] = null;
            return null;
        }
        $this->_settings[$code] = $setting->value;
        return $setting->value;
    }

    public function settings(): HasMany
    {
        return $this->hasMany(SocialProviderSetting::class);
    }

    protected function toStringName(): string
    {
        return $this->code;
    }
}
