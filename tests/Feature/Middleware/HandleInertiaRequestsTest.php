<?php

namespace Tests\Feature\Middleware;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HandleInertiaRequestsTest extends TestCase
{
    use RefreshDatabase;

    public function test_shared_props_include_theme_data(): void
    {
        $this->seed();

        $middleware = new \App\Http\Middleware\HandleInertiaRequests();
        $request = \Illuminate\Http\Request::create('/');
        $request->setLaravelSession(session()->driver());

        $shared = $middleware->share($request);

        $this->assertArrayHasKey('theme', $shared);
        $this->assertArrayHasKey('legal', $shared);
        $this->assertArrayHasKey('flash', $shared);
        $this->assertArrayHasKey('auth', $shared);
    }
}
