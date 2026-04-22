<?php

namespace Tests\Feature\Api;

use App\Enums\DeviceCodeStatus;
use App\Models\Client;
use App\Models\DeviceCode;
use App\Models\SocialProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_device_endpoint_requires_client_auth(): void
    {
        $this->postJson('/oauth2/device')
            ->assertUnauthorized();
    }

    public function test_device_endpoint_returns_device_code(): void
    {
        $client = Client::factory()->create(['enabled' => true]);
        $provider = SocialProvider::where('enabled', true)->first();

        if (!$provider) {
            $provider = SocialProvider::first();
            $provider->enabled = true;
            $provider->save();
        }

        $response = $this->postJson('/oauth2/device', [
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'scope' => $provider->code,
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'device_code',
                'user_code',
                'verification_uri',
                'expires_in',
                'interval',
            ]);
    }

    public function test_token_endpoint_returns_pending_for_unresolved_code(): void
    {
        $client = Client::factory()->create(['enabled' => true]);
        $deviceCode = DeviceCode::factory()->create([
            'client_id' => $client->id,
            'status' => DeviceCodeStatus::dcsPending,
        ]);

        $response = $this->postJson('/oauth2/token', [
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'grant_type' => 'urn:ietf:params:oauth:grant-type:device_code',
            'device_code' => $deviceCode->device_code,
        ]);

        $response->assertStatus(403);
    }
}
