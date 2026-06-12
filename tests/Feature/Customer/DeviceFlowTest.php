<?php

namespace Tests\Feature\Customer;

use App\Enums\DeviceCodeStatus;
use App\Http\Controllers\Customer\DeviceFlowController;
use App\Models\DeviceCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DeviceFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        Route::middleware('web')->group(function () {
            Route::get('/auth', [DeviceFlowController::class, 'code']);
            Route::post('/auth', [DeviceFlowController::class, 'submitCode']);
            Route::get('/auth/providers', [DeviceFlowController::class, 'providers']);
            Route::get('/auth/success', [DeviceFlowController::class, 'success']);
            Route::get('/auth/error', [DeviceFlowController::class, 'error']);
        });
    }

    public function test_code_page_renders(): void
    {
        $this->get('/auth')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Customer/Code'));
    }

    public function test_get_with_valid_code_skips_entry_and_redirects_to_providers(): void
    {
        $deviceCode = DeviceCode::factory()->create([
            'status' => DeviceCodeStatus::dcsPending,
        ]);

        $this->get('/auth?code='.$deviceCode->user_code)
            ->assertRedirect('/auth/providers')
            ->assertSessionHas('device_code_id', $deviceCode->id);
    }

    public function test_get_with_lowercase_code_is_normalised(): void
    {
        $deviceCode = DeviceCode::factory()->create([
            'status' => DeviceCodeStatus::dcsPending,
            'user_code' => 'AB12',
        ]);

        $this->get('/auth?code=ab12')
            ->assertRedirect('/auth/providers')
            ->assertSessionHas('device_code_id', $deviceCode->id);
    }

    public function test_get_with_invalid_code_falls_back_to_code_page(): void
    {
        $this->get('/auth?code=ZZZZ')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Customer/Code'))
            ->assertSessionMissing('device_code_id');
    }

    public function test_get_with_expired_code_falls_back_to_code_page(): void
    {
        $deviceCode = DeviceCode::factory()->create([
            'status' => DeviceCodeStatus::dcsPending,
        ]);

        $deviceCode->expires_at = now()->subMinute();
        $deviceCode->save();

        $this->get('/auth?code='.$deviceCode->user_code)
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Customer/Code'))
            ->assertSessionMissing('device_code_id');
    }

    public function test_get_with_wrong_length_code_falls_back_to_code_page(): void
    {
        $this->get('/auth?code=ZZ')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Customer/Code'))
            ->assertSessionMissing('device_code_id');
    }

    public function test_submit_valid_code_redirects_to_providers(): void
    {
        $deviceCode = DeviceCode::factory()->create([
            'status' => DeviceCodeStatus::dcsPending,
        ]);

        $this->post('/auth', ['code' => $deviceCode->user_code])
            ->assertRedirect('/auth/providers');
    }

    public function test_submit_invalid_code_returns_error(): void
    {
        $this->post('/auth', ['code' => 'ZZZZ'])
            ->assertSessionHasErrors('code');
    }

    public function test_submit_expired_code_returns_error(): void
    {
        $deviceCode = DeviceCode::factory()->create([
            'status' => DeviceCodeStatus::dcsPending,
        ]);

        // Manually expire the code
        $deviceCode->expires_at = now()->subMinute();
        $deviceCode->save();

        $this->post('/auth', ['code' => $deviceCode->user_code])
            ->assertSessionHasErrors('code');
    }

    public function test_submit_already_used_code_returns_error(): void
    {
        $deviceCode = DeviceCode::factory()->create([
            'status' => DeviceCodeStatus::dcsSuccessful,
        ]);

        $this->post('/auth', ['code' => $deviceCode->user_code])
            ->assertSessionHasErrors('code');
    }

    public function test_providers_page_renders_for_valid_session(): void
    {
        $deviceCode = DeviceCode::factory()->create([
            'status' => DeviceCodeStatus::dcsPending,
        ]);

        $this->withSession(['device_code_id' => $deviceCode->id])
            ->get('/auth/providers')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Customer/Providers'));
    }

    public function test_providers_page_redirects_without_session(): void
    {
        $this->get('/auth/providers')
            ->assertRedirect('/auth');
    }

    public function test_providers_page_redirects_for_expired_code(): void
    {
        $deviceCode = DeviceCode::factory()->create([
            'status' => DeviceCodeStatus::dcsPending,
        ]);

        $deviceCode->expires_at = now()->subMinute();
        $deviceCode->save();

        $this->withSession(['device_code_id' => $deviceCode->id])
            ->get('/auth/providers')
            ->assertRedirect('/auth/error');
    }

    public function test_success_page_renders(): void
    {
        $this->get('/auth/success')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Customer/Success'));
    }

    public function test_error_page_renders(): void
    {
        $this->get('/auth/error')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Customer/Error'));
    }

    public function test_error_page_includes_reason(): void
    {
        $this->withSession(['_flash' => ['new' => ['reason'], 'old' => []], 'reason' => 'expired'])
            ->get('/auth/error')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Customer/Error')
                ->where('reason', 'expired')
            );
    }
}
