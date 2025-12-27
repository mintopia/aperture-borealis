<?php

namespace App\Http\Resources;

use App\Models\DeviceCode;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DeviceCode
 */
class TokenResource extends JsonResource
{
    public static $wrap = null;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'access_token' => $this->access_token,
            'refresh_token' => $this->refresh_token,
            'token_type' => 'bearer',
            'expires_in' => round(CarbonImmutable::now()->diffInSeconds($this->access_token_expires_at)),
            'user' => (object)[
                'id' => $this->external_id,
                'nickname' => $this->nickname,
                'email' => $this->email,
                'avatar_url' => $this->avatar_url,
            ],
        ];
    }
}
