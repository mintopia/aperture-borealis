<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\ClientController;
use App\Http\Middleware\EnsureAdmin;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Tests\TestCase;

class ClientTest extends TestCase
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
            Route::get('/admin/clients', [ClientController::class, 'index']);
            Route::get('/admin/clients/create', [ClientController::class, 'create']);
            Route::post('/admin/clients', [ClientController::class, 'store']);
            Route::get('/admin/clients/{client}', [ClientController::class, 'show']);
            Route::put('/admin/clients/{client}', [ClientController::class, 'update']);
            Route::delete('/admin/clients/{client}', [ClientController::class, 'destroy']);
        });
        Route::getRoutes()->refreshNameLookups();
        $this->app['url']->setRoutes(Route::getRoutes());
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $this->get('/admin/clients')
            ->assertRedirect('/admin/login');
    }

    public function test_non_admin_gets_403(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user)->get('/admin/clients')->assertStatus(403);
    }

    public function test_admin_can_view_clients(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user)
            ->get('/admin/clients')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Clients/Index')->has('clients'));
    }

    public function test_admin_can_view_create_form(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user)
            ->get('/admin/clients/create')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Clients/Create'));
    }

    public function test_admin_can_create_client(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user)
            ->post('/admin/clients', ['name' => 'Test Client', 'enabled' => true])
            ->assertRedirect();
        $this->assertDatabaseHas('clients', ['name' => 'Test Client']);
    }

    public function test_admin_can_view_client(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $client = Client::factory()->create();
        $this->actingAs($user)
            ->get("/admin/clients/{$client->id}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Clients/Show')->has('client'));
    }

    public function test_admin_can_update_client(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $client = Client::factory()->create();
        $this->actingAs($user)
            ->put("/admin/clients/{$client->id}", ['name' => 'Updated Client', 'enabled' => false])
            ->assertRedirect();
        $this->assertDatabaseHas('clients', ['id' => $client->id, 'name' => 'Updated Client']);
    }

    public function test_admin_can_delete_client(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $client = Client::factory()->create();
        $this->actingAs($user)
            ->delete("/admin/clients/{$client->id}")
            ->assertRedirect();
        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }
}
