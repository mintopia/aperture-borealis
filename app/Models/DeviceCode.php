<?php

namespace App\Models;

use App\Enums\DeviceCodeStatus;
use App\Observers\DeviceCodeObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

/**
 * @mixin IdeHelperDeviceCode
 */
#[ObservedBy([DeviceCodeObserver::class])]
class DeviceCode extends Model
{
    public function provider(): BelongsTo
    {
        return $this->belongsTo(SocialProvider::class, 'social_provider_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function makeDeviceCode(): void
    {
        $this->device_code = Uuid::uuid4()->toString();
    }

    public function makeUserCode(): void
    {
        $charset = config('borealis.usercode.charset');
        $length = config('borealis.usercode.length');
        $this->user_code = '';
        for ($i = 0; $i < $length; $i++) {
            $this->user_code .= Str::charAt($charset, mt_rand(0, strlen($charset) - 1));
        }
    }

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'access_token_expires_at' => 'datetime',
            'expires_at' => 'datetime',
            'status' => DeviceCodeStatus::class,
        ];
    }

    public function getVerificationUri(bool $complete = false)
    {
        $params = [];
        if ($complete) {
            $params['code'] = $this->user_code;
        }
        return route('auth', $params);
    }

    public function getAvatarUrl(): string
    {
        if ($this->avatar_url !== null) {
            return $this->avatar_url;
        }
        $hash = hash('sha256', strtolower(trim($this->email) ?? ''));
        return "https://www.gravatar.com/avatar/{$hash}?d=mp";
    }
}
