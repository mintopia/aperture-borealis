<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateJwtKeysCommand extends Command
{
    protected $signature = 'jwt:generate-keys {--force : Overwrite existing keys}';

    protected $description = 'Generate RSA key pair for IPv6 JWT signing';

    public function handle(): int
    {
        $dir = config('borealis.jwt.key_directory');
        $privatePath = config('borealis.jwt.private_key');
        $publicPath = config('borealis.jwt.public_key');

        if (file_exists($privatePath) && ! $this->option('force')) {
            $this->error('Keys already exist. Use --force to overwrite.');

            return self::FAILURE;
        }

        if (! is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        $key = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if ($key === false) {
            $this->error('Failed to generate RSA key pair.');

            return self::FAILURE;
        }

        openssl_pkey_export($key, $privatePem);
        $details = openssl_pkey_get_details($key);

        file_put_contents($privatePath, $privatePem);
        chmod($privatePath, 0600);

        file_put_contents($publicPath, $details['key']);
        chmod($publicPath, 0644);

        $this->info('JWT key pair generated:');
        $this->line("  Private: {$privatePath}");
        $this->line("  Public:  {$publicPath}");

        return self::SUCCESS;
    }
}
