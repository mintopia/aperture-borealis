<?php

namespace App\Services\SocialProviders;

use App\Models\SocialProvider;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Authentik\Provider;
use SocialiteProviders\Manager\Config;

class AuthentikProvider extends AbstractSocialProvider
{
    protected string $name = 'Authentik';
    protected string $code = 'authentik';
    protected string $socialiteProviderCode = 'authentik';
    public function __construct(?SocialProvider $provider = null, ?string $redirectUrl = null)
    {
        parent::__construct($provider, $redirectUrl);
        if ($provider != null) {
            $this->name = $provider->name;
        }
    }

    public function configMapping(): array
    {
        return array_merge(
            parent::configMapping(),
            [
                'host' => (object)[
                    'name' => 'Authentik Base URL',
                    'validation' => 'required|string',
                ],
            ],
        );
    }

    protected function getSocialiteProvider()
    {
        $config = new Config(
            $this->provider->getSetting('client_id'),
            $this->provider->getSetting('client_secret'),
            $this->redirectUrl,
            ['host' => $this->provider->getSetting('host')]
        );
        return Socialite::buildProvider(Provider::class, $config->get())
            ->setConfig($config)->with(['prompt' => 'none']);
    }
}
