<?php

namespace App\Http\Resources;

use App\Models\DeviceCode;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DeviceCode
 */
class DeviceCodeResource extends JsonResource
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
            'device_code' => $this->device_code,
            'user_code' => $this->user_code,
            'verification_uri' => $this->getVerificationUri(),
            'verification_uri_complete' => $this->getVerificationUri(true),
            'expires_in' => round(CarbonImmutable::now()->diffInSeconds($this->expires_at)),
            'interval' => $this->client->interval,
        ];
    }
}
