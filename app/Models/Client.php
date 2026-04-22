<?php

namespace App\Models;

use App\Observers\ClientObserver;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperClient
 */
#[ObservedBy([ClientObserver::class])]
class Client extends Model implements Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name',
        'enabled',
        'interval',
        'expires_in',
        'client_id',
        'client_secret',
    ];

    protected function casts(): array
    {
        return [
            'client_secret' => 'encrypted',
        ];
    }
    public function codes(): HasMany
    {
        return $this->hasMany(DeviceCode::class);
    }

    public function getAuthIdentifierName()
    {
        return 'client_id';
    }

    public function getAuthIdentifier()
    {
        return $this->client_id;
    }

    public function getAuthPasswordName()
    {
        return 'client_secret';
    }

    public function getAuthPassword()
    {
        return $this->client_secret;
    }

    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value)
    {
        return null;
    }

    public function getRememberTokenName()
    {
        return null;
    }
}
