<?php

namespace Database\Factories;

use App\Enums\DeviceCodeStatus;
use App\Models\Client;
use App\Models\DeviceCode;
use App\Models\SocialProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeviceCode>
 */
class DeviceCodeFactory extends Factory
{
    protected $model = DeviceCode::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'social_provider_id' => SocialProvider::first()?->id ?? 1,
            'status' => DeviceCodeStatus::dcsPending,
        ];
    }
}
