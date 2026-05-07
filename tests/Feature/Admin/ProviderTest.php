<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\ProviderController;
use App\Http\Middleware\EnsureAdmin;
use App\Models\SocialProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Tests\TestCase;

class ProviderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        Route::middleware('web')->group(function () {
            Route::get('/admin/login', fn () => Inertia::render('Admin/Login'))->name('admin.login');
        });
        Route::middleware(['web', EnsureAdmin::class])->group(function () {
            Route::get('/admin/providers', [ProviderController::class, 'index']);
            Route::put('/admin/providers/{provider}', [ProviderController::class, 'update']);
        });
        Route::getRoutes()->refreshNameLookups();
        $this->app['url']->setRoutes(Route::getRoutes());
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $this->get('/admin/providers')
            ->assertRedirect('/admin/login');
    }

    public function test_non_admin_gets_403(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get('/admin/providers')
            ->assertStatus(403);
    }

    public function test_admin_can_view_providers(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $this->actingAs($user)
            ->get('/admin/providers')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Providers/Index')
                ->has('providers')
            );
    }

    public function test_admin_can_toggle_provider(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $provider = SocialProvider::first();

        $this->actingAs($user)
            ->put("/admin/providers/{$provider->id}", [
                'enabled' => ! $provider->enabled,
            ])
            ->assertRedirect();
    }
}
