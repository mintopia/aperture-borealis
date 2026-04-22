<?php

namespace Tests\Feature\Admin\Settings;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class GeneralSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        Route::middleware('web')->group(function () {
            Route::get('/admin/login', fn () => \Inertia\Inertia::render('Admin/Login'))->name('admin.login');
        });
        Route::middleware(['web', \App\Http\Middleware\EnsureAdmin::class])->group(function () {
            Route::get('/admin/settings/general', [\App\Http\Controllers\Admin\Settings\GeneralController::class, 'show']);
            Route::put('/admin/settings/general', [\App\Http\Controllers\Admin\Settings\GeneralController::class, 'update']);
        });
        Route::getRoutes()->refreshNameLookups();
        $this->app['url']->setRoutes(Route::getRoutes());
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $this->get('/admin/settings/general')
            ->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_general_settings(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $this->actingAs($user)
            ->get('/admin/settings/general')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Settings/General')
                ->has('settings')
            );
    }

    public function test_admin_can_update_general_settings(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $this->actingAs($user)
            ->put('/admin/settings/general', [
                'terms_url' => 'https://example.com/terms',
                'privacy_url' => 'https://example.com/privacy',
                'device_code_expiry' => '600',
            ])
            ->assertRedirect();

        $this->assertEquals('https://example.com/terms', Setting::where('code', 'terms_url')->first()->value);
    }
}
