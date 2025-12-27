<?php

namespace App\Models;

use App\Observers\ThemeObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperTheme
 */

#[ObservedBy([ThemeObserver::class])]
class Theme extends Model
{
    public function rgb(string $property): string
    {
        $colour = $this->{$property};
        if (!preg_match('/^#(?:(?:[0-9a-f]{3}){1,2}|(?:[0-9a-f]{4}){1,2})$/i', $colour)) {
            return '0, 0, 0';
        }

        $hex = str_replace('#', '', $colour);
        if (strlen($hex) === 3) {
            $hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(substr($hex, 1, 1), 2) . str_repeat(substr($hex, 2, 1), 2);
        }
        $rgb = [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
        return implode(', ', $rgb);
    }
}
