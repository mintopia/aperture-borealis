<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Authentik\Provider as AuthentikProvider;
use SocialiteProviders\Discord\Provider as DiscordProvider;
use SocialiteProviders\LaravelPassport\Provider as LaravelPassportProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Twitch\Provider as TwitchProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->setupViews();
        $this->setupAuthentication();
        $this->bindSocialiteEvents();
    }

    protected function setupAuthentication(): void
    {
        Auth::viaRequest('client', function (Request $request) {
            $client = Client::whereEnabled(true)
                ->where('client_id', (string) $request->input('client_id'))
                ->first();
            if ($client && $client->client_secret === $request->input('client_secret')) {
                return $client;
            }
            return null;
        });
    }

    protected function setupViews(): void
    {
        Blade::directive('setting', function (string $expression, $default = null) {
            return "<?php echo App\Models\Setting::fetch($expression, $default); ?>";
        });

        view()->composer(['layouts.app', 'layouts.login'], function ($view) {
            $currentTheme = Theme::whereActive(true)->first();
            $darkMode = false;
            if ($currentTheme) {
                $darkMode = $currentTheme->dark_mode;
            }
            $view->with('currentTheme', $currentTheme);
            $view->with('darkMode', $darkMode);
        });
    }

    protected function bindSocialiteEvents(): void
    {
        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('discord', DiscordProvider::class);
            $event->extendSocialite('twitch', TwitchProvider::class);
            $event->extendSocialite('laravelpassport', LaravelPassportProvider::class);
            $event->extendSocialite('authentik', AuthentikProvider::class);
        });
    }
}
