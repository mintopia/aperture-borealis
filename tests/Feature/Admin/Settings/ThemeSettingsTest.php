<?php

namespace Tests\Feature\Admin\Settings;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ThemeSettingsTest extends TestCase
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
            Route::get('/admin/settings/theme', [\App\Http\Controllers\Admin\Settings\ThemeController::class, 'show']);
            Route::put('/admin/settings/theme', [\App\Http\Controllers\Admin\Settings\ThemeController::class, 'update']);
        });
        Route::getRoutes()->refreshNameLookups();
        $this->app['url']->setRoutes(Route::getRoutes());
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $this->get('/admin/settings/theme')
            ->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_theme_settings(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $this->actingAs($user)
            ->get('/admin/settings/theme')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Settings/Theme')
                ->has('settings')
            );
    }

    public function test_admin_can_update_theme_settings(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $this->actingAs($user)
            ->put('/admin/settings/theme', [
                'accent_hue' => '230',
                'color_mode' => 'light',
                'site_title' => 'My Borealis',
                'custom_css' => '',
            ])
            ->assertRedirect();

        $this->assertEquals('230', Setting::where('code', 'accent_hue')->first()->value);
        $this->assertEquals('light', Setting::where('code', 'color_mode')->first()->value);
    }
}
