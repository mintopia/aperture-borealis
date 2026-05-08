<?php

namespace App\Services\SocialProviders;

use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider as SocialiteGoogleProvider;

class GoogleProvider extends AbstractSocialProvider
{
    protected string $name = 'Google';

    protected string $code = 'google';

    protected string $socialiteProviderCode = 'google';

    public function getSocialiteProvider()
    {
        return Socialite::buildProvider(SocialiteGoogleProvider::class, [
            'client_id' => $this->provider->getSetting('client_id'),
            'client_secret' => $this->provider->getSetting('client_secret'),
            'redirect' => $this->redirectUrl,
        ]);
    }
}
