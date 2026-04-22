<?php

namespace Tests\Feature\Admin\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        // Register temporary routes for testing
        Route::middleware('web')->group(function () {
            Route::get('/admin/login', [\App\Http\Controllers\Admin\Auth\LoginController::class, 'showLogin'])->name('admin.login');
            Route::post('/admin/login', [\App\Http\Controllers\Admin\Auth\LoginController::class, 'passwordLogin']);
            Route::post('/admin/logout', [\App\Http\Controllers\Admin\Auth\LogoutController::class, 'logout'])->name('admin.logout');
            Route::get('/admin', function () {
                return \Inertia\Inertia::render('Admin/Dashboard');
            })->name('admin.dashboard');
        });

        // Refresh the URL generator's route name lookup cache
        Route::getRoutes()->refreshNameLookups();
        $this->app['url']->setRoutes(Route::getRoutes());
    }

    public function test_login_page_renders(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Login'));
    }

    public function test_password_login_with_valid_admin_credentials(): void
    {
        $user = User::factory()->create([
            'is_admin' => true,
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ])->assertRedirect('/admin');
    }

    public function test_password_login_fails_for_non_admin(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->post('/admin/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ])->assertRedirect()
            ->assertSessionHasErrors('email');
    }

    public function test_password_login_fails_with_invalid_credentials(): void
    {
        $this->post('/admin/login', [
            'email' => 'nobody@example.com',
            'password' => 'wrong',
        ])->assertRedirect()
            ->assertSessionHasErrors('email');
    }

    public function test_logout_clears_session(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $this->actingAs($user)
            ->post('/admin/logout')
            ->assertRedirect('/admin/login');

        $this->assertGuest();
    }
}
