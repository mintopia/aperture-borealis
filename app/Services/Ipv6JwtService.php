<?php

declare(strict_types=1);

namespace App\Services;

use Firebase\JWT\JWT;
use RuntimeException;

class Ipv6JwtService
{
    private string $privateKeyPath;

    private string $publicKeyPath;

    private string $kid;

    public function __construct()
    {
        $this->privateKeyPath = config('borealis.jwt.private_key');
        $this->publicKeyPath = config('borealis.jwt.public_key');
        $this->kid = config('borealis.jwt.kid');
    }

    public function sign(string $ip): string
    {
        $privateKey = $this->loadKey($this->privateKeyPath);

        return JWT::encode(
            [
                'sub' => $ip,
                'iat' => time(),
                'exp' => time() + 60,
            ],
            $privateKey,
            'RS256',
            $this->kid,
        );
    }

    /**
     * @return array{keys: list<array<string, string>>}
     */
    public function jwks(): array
    {
        $publicKey = openssl_pkey_get_public($this->loadKey($this->publicKeyPath));

        if ($publicKey === false) {
            throw new RuntimeException('Failed to parse public key');
        }

        $details = openssl_pkey_get_details($publicKey);

        if ($details === false) {
            throw new RuntimeException('Failed to get public key details');
        }

        return [
            'keys' => [[
                'kty' => 'RSA',
                'kid' => $this->kid,
                'use' => 'sig',
                'alg' => 'RS256',
                'n' => rtrim(strtr(base64_encode($details['rsa']['n']), '+/', '-_'), '='),
                'e' => rtrim(strtr(base64_encode($details['rsa']['e']), '+/', '-_'), '='),
            ]],
        ];
    }

    public function hasKeys(): bool
    {
        return file_exists($this->privateKeyPath) && file_exists($this->publicKeyPath);
    }

    private function loadKey(string $path): string
    {
        if (! file_exists($path)) {
            throw new RuntimeException("Key file not found: {$path}. Run php artisan jwt:generate-keys");
        }

        return file_get_contents($path);
    }
}
