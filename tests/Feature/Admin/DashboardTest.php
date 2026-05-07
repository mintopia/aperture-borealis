<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Middleware\EnsureAdmin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        // Register temporary routes since actual routes are wired in Task 20
        Route::middleware('web')->group(function () {
            Route::get('/admin/login', fn () => Inertia::render('Admin/Login'))->name('admin.login');
        });
        Route::middleware(['web', EnsureAdmin::class])->group(function () {
            Route::get('/admin', [DashboardController::class, 'index'])->name('admin.dashboard');
        });
        Route::getRoutes()->refreshNameLookups();
        $this->app['url']->setRoutes(Route::getRoutes());
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $this->get('/admin')
            ->assertRedirect('/admin/login');
    }

    public function test_non_admin_gets_403(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get('/admin')
            ->assertStatus(403);
    }

    public function test_admin_can_view_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Dashboard')
                ->has('stats')
                ->has('recentAuths')
            );
    }
}
