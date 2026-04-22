<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_admin_defaults_to_false(): void
    {
        $user = User::factory()->create();
        $this->assertFalse($user->is_admin);
    }

    public function test_is_admin_can_be_set_to_true(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $this->assertTrue($user->is_admin);
    }

    public function test_password_is_nullable(): void
    {
        $user = User::factory()->create(['password' => null]);
        $this->assertNull($user->password);
    }
}
