<?php

namespace Tests\Feature\Middleware;

use App\Http\Middleware\EnsureAdmin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class EnsureAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/admin/login', fn () => 'login-page')->name('admin.login');

        Route::middleware(['web', EnsureAdmin::class])
            ->get('/test-admin', fn () => 'admin-ok');
    }

    public function test_unauthenticated_user_is_redirected_to_admin_login(): void
    {
        $this->get('/test-admin')
            ->assertRedirect('/admin/login');
    }

    public function test_non_admin_user_gets_403(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get('/test-admin')
            ->assertStatus(403);
    }

    public function test_admin_user_can_access(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $this->actingAs($user)
            ->get('/test-admin')
            ->assertOk()
            ->assertSee('admin-ok');
    }
}
