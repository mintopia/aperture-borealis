<?php

namespace App\Providers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

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
        $this->setupAuthentication();
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
}
