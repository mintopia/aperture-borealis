<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Traits\ToString;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @mixin IdeHelperUser
 */
class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use ToString;

    protected $fillable = [
        'nickname',
        'email',
        'social_provider_id',
        'external_id',
        'access_token',
        'refresh_token',
        'access_token_expires_at',
        'last_login_at',
        'is_admin',
        'password',
    ];

    protected $hidden = [
        'password',
        'access_token',
        'refresh_token',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'access_token_expires_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_admin' => 'boolean',
        ];
    }

    protected function toStringName(): string
    {
        return $this->nickname ?? '';
    }
}
