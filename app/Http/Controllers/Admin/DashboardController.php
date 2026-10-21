<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DeviceCodeStatus;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\DeviceCode;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_clients' => Client::count(),
                'active_clients' => Client::where('enabled', true)->count(),
                'total_auths' => DeviceCode::where('status', DeviceCodeStatus::dcsSuccessful)->count(),
                'pending_codes' => DeviceCode::where('status', DeviceCodeStatus::dcsPending)
                    ->where('expires_at', '>', now())
                    ->count(),
            ],
            'recentAuths' => DeviceCode::where('status', DeviceCodeStatus::dcsSuccessful)
                ->with(['provider', 'client'])
                ->latest()
                ->take(10)
                ->get()
                ->map(fn ($dc) => [
                    'id' => $dc->id,
                    'nickname' => $dc->nickname,
                    'email' => $dc->email,
                    'provider' => $dc->provider?->name,
                    'client' => $dc->client?->name,
                    'created_at' => $dc->created_at->diffForHumans(),
                ]),
        ]);
    }
}
