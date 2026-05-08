<?php

declare(strict_types=1);

namespace Tests\Feature;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Tests\TestCase;

class Ipv6DetectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! file_exists(config('borealis.jwt.private_key'))) {
            $this->artisan('jwt:generate-keys')->assertSuccessful();
        }
    }

    public function test_ipv6_endpoint_returns_signed_jwt(): void
    {
        $response = $this->get('/ipv6', ['REMOTE_ADDR' => '2001:db8::1']);

        $response->assertOk();
        $response->assertJsonStructure(['token']);

        $jwt = $response->json('token');
        $this->assertNotEmpty($jwt);

        $jwksResponse = $this->getJson('/.well-known/jwks.json');
        $jwksData = $jwksResponse->json();
        $keys = JWK::parseKeySet($jwksData);
        $decoded = JWT::decode($jwt, $keys);

        $this->assertSame('2001:db8::1', $decoded->sub);
        $this->assertObjectHasProperty('iat', $decoded);
        $this->assertObjectHasProperty('exp', $decoded);
    }

    public function test_jwks_endpoint_returns_valid_keyset(): void
    {
        $response = $this->getJson('/.well-known/jwks.json');

        $response->assertOk();
        $response->assertJsonStructure([
            'keys' => [
                ['kty', 'kid', 'use', 'alg', 'n', 'e'],
            ],
        ]);

        $data = $response->json();
        $this->assertSame('RSA', $data['keys'][0]['kty']);
        $this->assertSame('RS256', $data['keys'][0]['alg']);
        $this->assertSame('sig', $data['keys'][0]['use']);
    }

    public function test_jwks_has_cache_headers(): void
    {
        $response = $this->getJson('/.well-known/jwks.json');

        $response->assertHeader('Cache-Control', 'max-age=3600, public');
    }

    public function test_jwt_is_verifiable_by_aperture_flow(): void
    {
        $response = $this->get('/ipv6', ['REMOTE_ADDR' => '2001:db8::42']);
        $jwt = $response->json('token');

        $jwksResponse = $this->getJson('/.well-known/jwks.json');
        $keys = JWK::parseKeySet($jwksResponse->json());
        $decoded = JWT::decode($jwt, $keys);

        $this->assertSame('2001:db8::42', $decoded->sub);
        $this->assertTrue(
            filter_var($decoded->sub, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false
        );
    }

    public function test_generate_keys_command(): void
    {
        $dir = config('borealis.jwt.key_directory');
        $privatePath = config('borealis.jwt.private_key');
        $publicPath = config('borealis.jwt.public_key');

        if (file_exists($privatePath)) {
            unlink($privatePath);
        }
        if (file_exists($publicPath)) {
            unlink($publicPath);
        }

        $this->artisan('jwt:generate-keys')
            ->assertSuccessful()
            ->expectsOutputToContain('JWT key pair generated');

        $this->assertFileExists($privatePath);
        $this->assertFileExists($publicPath);
    }

    public function test_generate_keys_refuses_overwrite_without_force(): void
    {
        $this->artisan('jwt:generate-keys', ['--force' => true])
            ->assertSuccessful();

        $this->artisan('jwt:generate-keys')
            ->assertFailed()
            ->expectsOutputToContain('already exist');
    }

    public function test_generate_keys_overwrites_with_force(): void
    {
        $this->artisan('jwt:generate-keys', ['--force' => true])->assertSuccessful();

        $originalKey = file_get_contents(config('borealis.jwt.private_key'));

        $this->artisan('jwt:generate-keys', ['--force' => true])
            ->assertSuccessful();

        $newKey = file_get_contents(config('borealis.jwt.private_key'));
        $this->assertNotSame($originalKey, $newKey);
    }
}
