<?php

namespace App\Models;

use App\Casts\SettingValue;
use App\Enums\SettingType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * @mixin IdeHelperSocialProviderSetting
 */
class SocialProviderSetting extends Model implements Sortable
{
    use SortableTrait;

    protected function casts(): array
    {
        return [
            'value' => SettingValue::class,
            'type' => SettingType::class,
        ];
    }

    public function buildSortQuery(): Builder
    {
        return static::query()->where('social_provider_id', $this->provider_id);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(SocialProvider::class, 'social_provider_id');
    }

    public function isRequired(): bool
    {
        return str_contains($this->validation ?? '', 'required');
    }
}
