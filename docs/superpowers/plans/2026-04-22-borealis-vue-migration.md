# Borealis Vue 3 + Inertia Migration — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Migrate Borealis from Blade + Bootstrap/Tabler to Vue 3 + Inertia.js + Tailwind CSS 4 on Laravel 13, preserving all backend behavior.

**Architecture:** Clean-cut replacement: old Blade/Bootstrap/Tabler frontend is removed in one pass and replaced with Vue 3 + Inertia pages styled with Tailwind CSS 4 using Aperture's Dispatch design tokens. Controllers are reorganized into Customer/, Admin/, and Api/ namespaces. Admin auth supports social login, password, and passkeys.

**Tech Stack:** Laravel 13, PHP 8.3+, Vue 3, Inertia.js 2, Tailwind CSS 4, Vite, laragear/webauthn

---

## References

- **Spec:** `docs/superpowers/specs/2025-07-25-borealis-vue-migration-design.md` (898 lines)
- **Visual prototype:** `docs/mockups/borealis-prototype.html` (1899 lines)
- **Aperture UI components:** `/home/workspace/aperture/resources/js/Components/UI/`
- **Aperture composables:** `/home/workspace/aperture/resources/js/Composables/`
- **Aperture CSS:** `/home/workspace/aperture/resources/css/themes/dispatch.css`
- **Aperture fonts:** `/home/workspace/aperture/resources/fonts/`

## Current Borealis State

- Laravel 12, PHP ^8.2, PHPUnit ^11.5.3
- Blade + Bootstrap 5.3 + Tabler UI — NO Vue, NO Inertia
- Dependencies: bootstrap, @tabler/core, @tabler/icons-webfont, sass-embedded, @popperjs/core, axios
- Vite 7 with laravel-vite-plugin, tailwindcss 4 + @tailwindcss/vite (present but UNUSED)
- Sass: resources/sass/app.scss imports tabler SCSS + icons
- CSS: resources/css/app.css just imports the sass file
- JS: resources/js/app.js imports tabler JS + bootstrap.js (axios + popper)
- No custom middleware directory
- Controllers: HomeController, ClientController (partial CRUD), UserController (auth), OAuthController (device flow), SocialProviderController (empty), SettingController (empty), ThemeController (empty), Api/V1/AuthController (stubs)
- Models: User, Client, DeviceCode, SocialProvider, SocialProviderSetting, Setting, Theme
- Observers: ClientObserver, DeviceCodeObserver, SettingObserver, ThemeObserver
- Routes: web.php (auth via sanctum middleware), routes/oauth2.php (device + token)
- Tests: Only example stubs (Feature/ExampleTest.php, Unit/ExampleTest.php)
- PHPUnit 11.5.3, tests use sqlite :memory:
- No .env CACHE_PREFIX or SESSION_COOKIE set
- AppServiceProvider: registers 'client' auth guard, Socialite bindings, Blade directives, view composers

---

## Task 1: Laravel 13 Upgrade + Boost

**Files:**
- Modify: `composer.json` — change php to `^8.3`, laravel/framework to `^13.0`, phpunit/phpunit to `^12.0`
- Modify: `.env.example` — add CACHE_PREFIX=borealis_cache_ and SESSION_COOKIE=borealis_session

**Steps:**

- [ ] Update composer.json constraints: php to `^8.3`, laravel/framework to `^13.0`, phpunit/phpunit to `^12.0`

- [ ] Run composer update:
```bash
composer update
```

- [ ] Install Laravel Boost:
```bash
composer require laravel/boost --dev
```

- [ ] Run boost installer:
```bash
php artisan boost:install
```

- [ ] Add environment variables to `.env.example`:
```dotenv
CACHE_PREFIX=borealis_cache_
SESSION_COOKIE=borealis_session
```

- [ ] Verify Laravel version:
```bash
php artisan --version
# Expected: Laravel Framework 13.x.x
```

- [ ] Commit:
```bash
git add -A && git commit -m "chore: upgrade to Laravel 13 + Boost

- PHP ^8.3 minimum
- PHPUnit ^12.0
- Add CACHE_PREFIX and SESSION_COOKIE env vars

Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

## Task 2: NPM Package Changes + Vite Config

**Files:**
- Modify: `package.json` (via npm commands)
- Modify: `vite.config.js`

**Steps:**

- [ ] Install new packages:
```bash
npm install vue @inertiajs/vue3 @vitejs/plugin-vue
```

- [ ] Remove old packages:
```bash
npm uninstall bootstrap @tabler/core @tabler/icons-webfont sass-embedded @popperjs/core
```

- [ ] Rewrite `vite.config.js` with the following content:

```javascript
import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    return {
        plugins: [
            laravel({
                input: [
                    'resources/css/app.css',
                    'resources/js/app.js',
                ],
                refresh: true,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
            tailwindcss(),
        ],
        resolve: {
            alias: {
                '@': '/resources/js',
            },
        },
        server: {
            host: '0.0.0.0',
            watch: {
                ignored: ['**/storage/framework/views/**'],
            },
        },
    };
});
```

- [ ] Commit:
```bash
git add -A && git commit -m "chore: replace Bootstrap/Tabler with Vue 3 + Inertia npm packages

- Install vue, @inertiajs/vue3, @vitejs/plugin-vue
- Remove bootstrap, @tabler/core, @tabler/icons-webfont, sass-embedded, @popperjs/core
- Rewrite vite.config.js with Vue plugin and @ alias

Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

## Task 3: Fonts + CSS Token System

**Files:**
- Create: `resources/fonts/` — copy from `/home/workspace/aperture/resources/fonts/` (all font files: Bricolage Grotesque, Hanken Grotesk, JetBrains Mono with their CSS files)
- Create: `resources/css/themes/dispatch.css` — exact copy from Aperture
- Modify: `resources/css/app.css` — rewrite to Tailwind 4 imports
- Remove: `resources/sass/` directory

**Steps:**

- [ ] Copy fonts from Aperture:
```bash
cp -r /home/workspace/aperture/resources/fonts resources/fonts
```

- [ ] Create themes directory and copy dispatch.css:
```bash
mkdir -p resources/css/themes
cp /home/workspace/aperture/resources/css/themes/dispatch.css resources/css/themes/dispatch.css
```

- [ ] Rewrite `resources/css/app.css`:

```css
@import 'tailwindcss';
@import '../fonts/bricolage-grotesque/bricolage-grotesque.css';
@import '../fonts/hanken-grotesk/hanken-grotesk.css';
@import '../fonts/jetbrains-mono/jetbrains-mono.css';
@import './themes/dispatch.css';

@theme {
    --font-heading: 'Bricolage Grotesque', ui-sans-serif, system-ui, sans-serif;
    --font-body: 'Hanken Grotesk', ui-sans-serif, system-ui, sans-serif;
    --font-mono: 'JetBrains Mono', ui-monospace, monospace;
}

@layer base {
    html {
        font-family: var(--font-body);
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
    h1, h2, h3, h4, h5, h6 {
        font-family: var(--font-heading);
    }
    code, pre, kbd, samp {
        font-family: var(--font-mono);
    }
}
```

- [ ] Remove sass directory:
```bash
rm -rf resources/sass
```

- [ ] Commit:
```bash
git add -A && git commit -m "feat: add Dispatch design tokens + font system

- Copy Aperture fonts (Bricolage Grotesque, Hanken Grotesk, JetBrains Mono)
- Copy dispatch.css theme tokens
- Rewrite app.css for Tailwind 4 with font @theme definitions
- Remove legacy sass directory

Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

## Task 4: Inertia Setup + Root Template + JS Entrypoint

**Files:**
- Create: `resources/views/app.blade.php` (Inertia root — NOTE: no existing app.blade.php to conflict with)
- Modify: `resources/js/app.js` — rewrite for Vue + Inertia
- Remove: `resources/js/bootstrap.js`

**Steps:**

- [ ] Install Inertia server-side:
```bash
composer require inertiajs/inertia-laravel
```

- [ ] Create `resources/views/app.blade.php`:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dispatch" data-mode="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Borealis') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body class="bg-[var(--color-bg)] text-[var(--color-text)]">
    @inertia
</body>
</html>
```

- [ ] Rewrite `resources/js/app.js`:

```javascript
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

createInertiaApp({
    title: (title) => (title ? `${title} - Borealis` : 'Borealis'),
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});
```

- [ ] Remove bootstrap.js:
```bash
rm resources/js/bootstrap.js
```

- [ ] Create a placeholder page for build verification — `resources/js/Pages/Customer/Code.vue`:

```vue
<template>
    <div>Borealis — Build OK</div>
</template>
```

```bash
mkdir -p resources/js/Pages/Customer
```

- [ ] Run vite build to verify:
```bash
npx vite build
# Expected: build completes without errors
```

- [ ] Commit:
```bash
git add -A && git commit -m "feat: set up Inertia.js + Vue 3 entrypoint

- Install inertiajs/inertia-laravel
- Create Inertia root template (app.blade.php)
- Rewrite app.js for Vue 3 + Inertia createInertiaApp
- Remove legacy bootstrap.js
- Add placeholder Code.vue for build verification

Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

## Task 5: Theme Composables

**Files:**
- Create: `resources/js/Composables/useAccentHue.js`
- Create: `resources/js/Composables/useTheme.js`

**Steps:**

- [ ] Create composables directory:
```bash
mkdir -p resources/js/Composables
```

- [ ] Create `resources/js/Composables/useAccentHue.js`:

```javascript
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

export const ACCENT_PRESETS = [
    { name: 'Pink', hue: 350, l: 72, c: 0.19 },
    { name: 'Coral', hue: 20, l: 73, c: 0.17 },
    { name: 'Tangerine', hue: 55, l: 76, c: 0.16 },
    { name: 'Lime', hue: 135, l: 80, c: 0.18 },
    { name: 'Teal', hue: 185, l: 76, c: 0.12 },
    { name: 'Sky', hue: 230, l: 72, c: 0.14 },
    { name: 'Violet', hue: 295, l: 70, c: 0.18 },
    { name: 'Magenta', hue: 325, l: 70, c: 0.2 },
];

const DEFAULT_HUE = 55;

function findPreset(hue) {
    return ACCENT_PRESETS.find((p) => p.hue === hue);
}

export function applyAccentHue(hue, mode = 'dark') {
    const root = document.documentElement;
    const preset = findPreset(hue);

    let l, c;
    if (preset) {
        l = preset.l;
        c = preset.c;
    } else {
        l = 72;
        c = 0.19;
    }

    if (mode === 'light') {
        const lightL = Math.max(l - 21, 40);
        const lightC = c + 0.02;
        root.style.setProperty('--color-primary', `oklch(${lightL}% ${lightC} ${hue})`);
        root.style.setProperty('--color-primary-hover', `oklch(${lightL - 7}% ${lightC + 0.02} ${hue})`);
        root.style.setProperty('--color-accent', `oklch(${lightL}% ${lightC} ${hue})`);
        root.style.setProperty('--color-accent-hover', `oklch(${lightL - 7}% ${lightC + 0.02} ${hue})`);
        root.style.setProperty('--color-accent-dim', `oklch(${lightL}% ${lightC} ${hue} / 0.1)`);
        root.style.setProperty('--color-accent-text', `oklch(99% 0.005 ${hue})`);
        root.style.setProperty('--color-glow', `oklch(${lightL}% ${lightC} ${hue} / 0.15)`);
    } else {
        root.style.setProperty('--color-primary', `oklch(${l}% ${c} ${hue})`);
        root.style.setProperty('--color-primary-hover', `oklch(${l - 7}% ${c + 0.03} ${hue})`);
        root.style.setProperty('--color-accent', `oklch(${l}% ${c} ${hue})`);
        root.style.setProperty('--color-accent-hover', `oklch(${l - 7}% ${c + 0.03} ${hue})`);
        root.style.setProperty('--color-accent-dim', `oklch(${l}% ${c} ${hue} / 0.14)`);
        root.style.setProperty('--color-accent-text', `oklch(98% 0.01 ${hue})`);
        root.style.setProperty('--color-glow', `oklch(${l}% ${c} ${hue} / 0.25)`);
    }
}

export function useAccentHue() {
    const page = usePage();
    const sharedTheme = page.props.theme || {};
    const accentHue = ref(sharedTheme.accent_hue ?? DEFAULT_HUE);

    function setAccentHue(hue, mode = 'dark') {
        accentHue.value = hue;
        applyAccentHue(hue, mode);
    }

    onMounted(() => {
        const mode = document.documentElement.getAttribute('data-mode') || 'dark';
        applyAccentHue(accentHue.value, mode);
    });

    return {
        accentHue,
        setAccentHue,
        presets: ACCENT_PRESETS,
    };
}
```

- [ ] Create `resources/js/Composables/useTheme.js`:

```javascript
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { applyAccentHue } from './useAccentHue';

const VALID_MODES = ['light', 'dark', 'system'];

function resolveMode(mode) {
    if (mode === 'system') {
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    return mode;
}

export function useTheme() {
    const page = usePage();
    const sharedTheme = page.props.theme || {};

    const colorMode = ref(sharedTheme.color_mode || localStorage.getItem('themeMode') || 'dark');
    const effectiveMode = ref(resolveMode(colorMode.value));
    const accentHue = ref(sharedTheme.accent_hue ?? 55);

    function applyTheme() {
        const el = document.documentElement;
        effectiveMode.value = resolveMode(colorMode.value);
        el.setAttribute('data-theme', 'dispatch');
        el.setAttribute('data-mode', effectiveMode.value);
        applyAccentHue(accentHue.value, effectiveMode.value);
    }

    function setMode(newMode) {
        if (VALID_MODES.includes(newMode)) {
            colorMode.value = newMode;
            localStorage.setItem('themeMode', newMode);
            applyTheme();
        }
    }

    function setAccentHue(hue) {
        accentHue.value = hue;
        applyTheme();
    }

    watch([colorMode, accentHue], () => applyTheme(), { immediate: true });

    if (colorMode.value === 'system') {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => applyTheme());
    }

    return {
        colorMode,
        effectiveMode,
        accentHue,
        setMode,
        setAccentHue,
    };
}
```

- [ ] Commit:
```bash
git add -A && git commit -m "feat: add theme composables (useTheme, useAccentHue)

- Accent hue presets with OKLCH color calculation
- Theme mode support (light/dark/system)
- System preference media query listener
- LocalStorage persistence for user preference

Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

## Task 6: Database Migrations + Seeds

**Files:**
- Create: `database/migrations/2026_04_22_000001_add_admin_fields_to_users_table.php`
- Create: `database/migrations/2026_04_22_000002_drop_themes_table.php`
- Modify: `database/seeders/DatabaseSeeder.php` — remove ThemesSeeder call
- Modify: `database/seeders/SettingsSeeder.php` — add new settings
- Remove: `database/seeders/ThemesSeeder.php`
- Test: `tests/Feature/MigrationTest.php`

**Steps:**

- [ ] Write test first — `tests/Feature/MigrationTest.php`:

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_table_has_admin_fields(): void
    {
        $this->assertTrue(Schema::hasColumn('users', 'is_admin'));
        $this->assertTrue(Schema::hasColumn('users', 'password'));
    }

    public function test_themes_table_does_not_exist(): void
    {
        $this->assertFalse(Schema::hasTable('themes'));
    }
}
```

- [ ] Run test to verify it fails:
```bash
php artisan test --filter=MigrationTest
# Expected: failures — is_admin column doesn't exist, themes table still exists
```

- [ ] Create `database/migrations/2026_04_22_000001_add_admin_fields_to_users_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('last_login_at');
            $table->string('password')->nullable()->after('is_admin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_admin', 'password']);
        });
    }
};
```

- [ ] Create `database/migrations/2026_04_22_000002_drop_themes_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('themes');
    }

    public function down(): void
    {
        Schema::create('themes', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('readonly')->default(false);
            $table->boolean('active')->default(false);
            $table->boolean('dark_mode')->default(true);
            $table->string('primary')->nullable();
            $table->string('secondary')->nullable();
            $table->string('nav_background')->nullable();
            $table->longText('css')->nullable();
            $table->timestamps();
        });
    }
};
```

- [ ] Update `database/seeders/SettingsSeeder.php` — add new settings using `Setting::firstOrCreate()` for idempotency. New settings to add:
  - `accent_hue` (stString, default `'55'`)
  - `color_mode` (stString, default `'dark'`)
  - `site_title` (stString, default `''`)
  - `custom_css` (stString, default `''`)
  - `terms_url` (stString, default `''`)
  - `privacy_url` (stString, default `''`)
  - `device_code_expiry` (stString, default `'300'`)

Use the same pattern as existing settings in the file. Use `Setting::firstOrCreate(['code' => ...], [...])` for idempotency.

- [ ] Remove `database/seeders/ThemesSeeder.php` and its call from `database/seeders/DatabaseSeeder.php`

- [ ] Run tests to verify they pass:
```bash
php artisan test --filter=MigrationTest
# Expected: all tests pass
```

- [ ] Commit:
```bash
git add -A && git commit -m "feat: add admin fields migration + drop themes table

- Add is_admin (bool) and password (nullable) columns to users
- Drop themes table (replaced by Dispatch CSS tokens)
- Add new settings: accent_hue, color_mode, site_title, custom_css, terms/privacy URLs
- Remove ThemesSeeder

Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

## Task 7: User Model + Theme Cleanup

**Files:**
- Modify: `app/Models/User.php` — add is_admin (cast to bool), password to fillable and hidden
- Remove: `app/Models/Theme.php`
- Remove: `app/Observers/ThemeObserver.php`
- Modify: `app/Providers/AppServiceProvider.php` — remove Theme observer registration
- Test: `tests/Unit/UserModelTest.php`

**Steps:**

- [ ] Write test — `tests/Unit/UserModelTest.php`:

```php
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
```

**Note:** You may need to create/update `database/factories/UserFactory.php` if it doesn't exist. Check `database/factories/` first. If there's no UserFactory, create one.

- [ ] Run test to verify it fails:
```bash
php artisan test --filter=UserModelTest
# Expected: failures — is_admin not in casts yet
```

- [ ] Update `app/Models/User.php`:
  - Add `'is_admin'` to `$fillable`
  - Add `'password'` to `$fillable` and `$hidden`
  - Add `'is_admin' => 'boolean'` to `$casts`

- [ ] Remove `app/Models/Theme.php`

- [ ] Remove `app/Observers/ThemeObserver.php`

- [ ] Update `app/Providers/AppServiceProvider.php`:
  - Remove Theme observer registration
  - Remove any view composer that references Theme model

- [ ] Run tests:
```bash
php artisan test --filter=UserModelTest
# Expected: all 3 tests pass
```

- [ ] Commit:
```bash
git add -A && git commit -m "feat: add admin fields to User model + remove Theme artifacts

- Add is_admin (boolean cast) and password to User model
- Remove Theme model and ThemeObserver
- Clean Theme references from AppServiceProvider

Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

## Task 8: EnsureAdmin Middleware

**Files:**
- Create: `app/Http/Middleware/EnsureAdmin.php`
- Test: `tests/Feature/Middleware/EnsureAdminTest.php`

**Steps:**

- [ ] Write test — `tests/Feature/Middleware/EnsureAdminTest.php`:

```php
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

        Route::middleware(['web', 'auth', EnsureAdmin::class])
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
```

- [ ] Run test to verify it fails:
```bash
php artisan test --filter=EnsureAdminTest
```

- [ ] Create `app/Http/Middleware/EnsureAdmin.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('admin.login');
        }

        if (!$request->user()->is_admin) {
            abort(403);
        }

        return $next($request);
    }
}
```

- [ ] Run tests:
```bash
php artisan test --filter=EnsureAdminTest
# Expected: all 3 tests pass
```

- [ ] Commit:
```bash
git add -A && git commit -m "feat: add EnsureAdmin middleware

- Redirects unauthenticated users to admin login
- Returns 403 for non-admin authenticated users
- Passes through for admin users

Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

## Task 9: HandleInertiaRequests Middleware

**Files:**
- Create: `app/Http/Middleware/HandleInertiaRequests.php`
- Modify: `bootstrap/app.php` — register middleware
- Test: `tests/Feature/Middleware/HandleInertiaRequestsTest.php`

**Steps:**

- [ ] Write test — `tests/Feature/Middleware/HandleInertiaRequestsTest.php`:

```php
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
        $request->setLaravelSession(session());

        $shared = $middleware->share($request);

        $this->assertArrayHasKey('theme', $shared);
        $this->assertArrayHasKey('legal', $shared);
        $this->assertArrayHasKey('flash', $shared);
        $this->assertArrayHasKey('auth', $shared);
    }
}
```

- [ ] Run test to verify it fails:
```bash
php artisan test --filter=HandleInertiaRequestsTest
```

- [ ] Create `app/Http/Middleware/HandleInertiaRequests.php`:

```php
<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'nickname' => $user->nickname,
                    'email' => $user->email,
                    'is_admin' => (bool) $user->is_admin,
                    'avatar_url' => $user->avatar_url ?? null,
                ] : null,
            ],
            'theme' => [
                'accent_hue' => (int) Setting::fetch('accent_hue', 55),
                'color_mode' => Setting::fetch('color_mode', 'dark'),
                'site_title' => Setting::fetch('site_title', ''),
                'custom_css' => Setting::fetch('custom_css', ''),
            ],
            'legal' => [
                'terms_url' => Setting::fetch('terms_url', ''),
                'privacy_url' => Setting::fetch('privacy_url', ''),
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ]);
    }
}
```

**Note:** Check how `Setting::fetch()` works in the current Setting model. If it doesn't exist, add a static helper:

```php
public static function fetch(string $code, $default = null)
{
    return cache()->rememberForever("settings.{$code}", function () use ($code, $default) {
        $setting = static::where('code', $code)->first();
        return $setting ? $setting->value : $default;
    }) ?? $default;
}
```

- [ ] Register middleware in `bootstrap/app.php` — add `HandleInertiaRequests::class` to the web middleware group

- [ ] Run tests:
```bash
php artisan test --filter=HandleInertiaRequestsTest
# Expected: pass
```

- [ ] Commit:
```bash
git add -A && git commit -m "feat: add HandleInertiaRequests middleware

- Share auth user, theme settings, legal URLs, flash messages
- Register in web middleware group
- Uses Setting::fetch() with caching

Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

## Task 10: Shared UI Components (Copy from Aperture)

**Files — create all in `resources/js/Components/UI/`:**
- Create: `SectionHeader.vue`
- Create: `StatCard.vue`
- Create: `DataTable.vue`
- Create: `StatusPill.vue`
- Create: `FormField.vue`
- Create: `MetadataStrip.vue`
- Create: `EmptyState.vue`
- Create: `ConfirmModal.vue`

**Steps:**

- [ ] Create directory:
```bash
mkdir -p resources/js/Components/UI
```

- [ ] Copy each component from Aperture and adapt:
  - Remove Aperture-specific imports (like Ziggy route helper in DataTable — Borealis doesn't use Ziggy)
  - Keep all Dispatch token classes unchanged
  - Keep all `data-testid` attributes
  - Keep all accessibility attributes

Copy these files from `/home/workspace/aperture/resources/js/Components/UI/`:
- `SectionHeader.vue` — copy as-is
- `StatCard.vue` — copy as-is
- `DataTable.vue` — copy as-is (uses Inertia router, which Borealis will have). Remove any Ziggy `route()` calls if present.
- `StatusPill.vue` — copy as-is
- `FormField.vue` — copy as-is
- `MetadataStrip.vue` — copy as-is
- `EmptyState.vue` — copy as-is
- `ConfirmModal.vue` — copy as-is

For DataTable.vue specifically: replace any `route('...')` calls with plain string URLs since Borealis doesn't use Ziggy.

- [ ] Commit:
```bash
git add -A && git commit -m "feat: add shared UI components from Aperture

- SectionHeader, StatCard, DataTable, StatusPill
- FormField, MetadataStrip, EmptyState, ConfirmModal
- All use Dispatch design tokens
- Adapted from Aperture (removed Ziggy dependencies)

Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

## Task 11: Borealis-Specific Components

**Files:**
- Create: `resources/js/Components/Admin/Sidebar.vue`
- Create: `resources/js/Components/Admin/SettingsNav.vue`
- Create: `resources/js/Components/Customer/ProviderButton.vue`
- Create: `resources/js/Components/Customer/CodeInput.vue`

**Steps:**

- [ ] Create directories:
```bash
mkdir -p resources/js/Components/Admin resources/js/Components/Customer
```

- [ ] Create `resources/js/Components/Admin/Sidebar.vue`:

```vue
<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const navItems = [
    { label: 'Dashboard', href: '/admin', icon: '⌂' },
    { label: 'Providers', href: '/admin/providers', icon: '⚡' },
    { label: 'Clients', href: '/admin/clients', icon: '🔑' },
];

const settingsItems = [
    { label: 'Theme', href: '/admin/settings/theme' },
    { label: 'General', href: '/admin/settings/general' },
];

function isActive(href) {
    const currentPath = page.url;
    if (href === '/admin') return currentPath === '/admin';
    return currentPath.startsWith(href);
}
</script>

<template>
    <aside data-testid="admin-sidebar" class="flex w-[220px] flex-shrink-0 flex-col border-r border-[var(--color-border)] bg-[var(--color-surface)]">
        <div class="flex h-12 items-center px-5 border-b border-[var(--color-border)]">
            <span class="font-heading text-[15px] font-bold text-[var(--color-text)]">Borealis</span>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-1">
            <Link
                v-for="item in navItems"
                :key="item.href"
                :href="item.href"
                :class="[
                    'flex items-center gap-2.5 rounded-md px-3 py-[7px] text-[13px] font-medium transition-colors',
                    isActive(item.href)
                        ? 'bg-[var(--color-accent-dim)] text-[var(--color-primary)]'
                        : 'text-[var(--color-text-secondary)] hover:bg-[var(--color-surface-hover)] hover:text-[var(--color-text)]',
                ]"
            >
                <span class="text-sm">{{ item.icon }}</span>
                {{ item.label }}
            </Link>

            <div class="pt-4">
                <p class="px-3 pb-2 text-[10px] font-semibold tracking-[0.08em] text-[var(--color-text-muted)] uppercase">Settings</p>
                <Link
                    v-for="item in settingsItems"
                    :key="item.href"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-2.5 rounded-md px-3 py-[7px] text-[13px] font-medium transition-colors',
                        isActive(item.href)
                            ? 'bg-[var(--color-accent-dim)] text-[var(--color-primary)]'
                            : 'text-[var(--color-text-secondary)] hover:bg-[var(--color-surface-hover)] hover:text-[var(--color-text)]',
                    ]"
                >
                    {{ item.label }}
                </Link>
            </div>
        </nav>

        <div class="border-t border-[var(--color-border)] px-3 py-3">
            <Link
                href="/admin/logout"
                method="post"
                as="button"
                class="flex w-full items-center gap-2 rounded-md px-3 py-[7px] text-[13px] font-medium text-[var(--color-text-secondary)] transition-colors hover:bg-[var(--color-surface-hover)] hover:text-[var(--color-text)]"
            >
                Logout
            </Link>
        </div>
    </aside>
</template>
```

- [ ] Create `resources/js/Components/Admin/SettingsNav.vue`:

```vue
<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const items = [
    { label: 'Theme', href: '/admin/settings/theme' },
    { label: 'General', href: '/admin/settings/general' },
];

function isActive(href) {
    return page.url === href;
}
</script>

<template>
    <nav data-testid="settings-nav" class="w-[180px] flex-shrink-0 space-y-1">
        <Link
            v-for="item in items"
            :key="item.href"
            :href="item.href"
            :class="[
                'block rounded-md px-3 py-[7px] text-[13px] font-medium transition-colors',
                isActive(item.href)
                    ? 'bg-[var(--color-accent-dim)] text-[var(--color-primary)]'
                    : 'text-[var(--color-text-secondary)] hover:bg-[var(--color-surface-hover)] hover:text-[var(--color-text)]',
            ]"
        >
            {{ item.label }}
        </Link>
    </nav>
</template>
```

- [ ] Create `resources/js/Components/Customer/ProviderButton.vue`:

```vue
<script setup>
defineProps({
    provider: { type: Object, required: true },
    href: { type: String, required: true },
    loading: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
});

const brandColors = {
    discord: { bg: 'bg-[#5865F2]', hover: 'hover:bg-[#4752C4]' },
    steam: { bg: 'bg-[#171A21]', hover: 'hover:bg-[#2A475E]' },
    twitch: { bg: 'bg-[#9146FF]', hover: 'hover:bg-[#772CE8]' },
    authentik: { bg: 'bg-[#FD4B2D]', hover: 'hover:bg-[#E04328]' },
    laravelpassport: { bg: 'bg-[#FF2D20]', hover: 'hover:bg-[#E0261B]' },
};
</script>

<template>
    <a
        :href="href"
        :class="[
            'flex w-full items-center justify-center gap-3 rounded-lg px-6 py-3.5 text-[15px] font-semibold text-white transition-colors',
            brandColors[provider.code]?.bg ?? 'bg-[var(--color-primary)]',
            brandColors[provider.code]?.hover ?? 'hover:bg-[var(--color-primary-hover)]',
            (loading || disabled) ? 'pointer-events-none opacity-50' : '',
        ]"
        data-testid="provider-button"
    >
        <span v-if="loading">Connecting…</span>
        <span v-else>{{ provider.name }}</span>
    </a>
</template>
```

- [ ] Create `resources/js/Components/Customer/CodeInput.vue`:

```vue
<script setup>
import { ref, nextTick, onMounted } from 'vue';

const props = defineProps({
    length: { type: Number, default: 4 },
    error: { type: String, default: '' },
});

const emit = defineEmits(['complete']);

const inputs = ref([]);
const values = ref(Array(props.length).fill(''));

onMounted(() => {
    inputs.value[0]?.focus();
});

function onInput(index, event) {
    const val = event.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    values.value[index] = val.charAt(0) || '';
    event.target.value = values.value[index];

    if (values.value[index] && index < props.length - 1) {
        nextTick(() => inputs.value[index + 1]?.focus());
    }

    const code = values.value.join('');
    if (code.length === props.length) {
        emit('complete', code);
    }
}

function onKeydown(index, event) {
    if (event.key === 'Backspace' && !values.value[index] && index > 0) {
        values.value[index - 1] = '';
        nextTick(() => inputs.value[index - 1]?.focus());
    }
}

function onPaste(event) {
    event.preventDefault();
    const pasted = (event.clipboardData?.getData('text') || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
    for (let i = 0; i < props.length && i < pasted.length; i++) {
        values.value[i] = pasted[i];
    }
    const focusIndex = Math.min(pasted.length, props.length - 1);
    nextTick(() => inputs.value[focusIndex]?.focus());

    const code = values.value.join('');
    if (code.length === props.length) {
        emit('complete', code);
    }
}
</script>

<template>
    <div data-testid="code-input">
        <div class="flex justify-center gap-3">
            <input
                v-for="(_, index) in values"
                :key="index"
                :ref="(el) => (inputs[index] = el)"
                type="text"
                maxlength="1"
                inputmode="text"
                autocomplete="off"
                :value="values[index]"
                :class="[
                    'h-16 w-14 rounded-lg border-2 bg-[var(--color-input-bg)] text-center font-mono text-2xl font-bold text-[var(--color-text)] outline-none transition-colors',
                    error
                        ? 'border-[var(--color-danger)]'
                        : 'border-[var(--color-border)] focus:border-[var(--color-primary)]',
                ]"
                @input="onInput(index, $event)"
                @keydown="onKeydown(index, $event)"
                @paste="onPaste"
            />
        </div>
        <p v-if="error" class="mt-3 text-center text-sm text-[var(--color-danger)]">
            {{ error }}
        </p>
    </div>
</template>
```

- [ ] Commit:
```bash
git add -A && git commit -m "feat: add Borealis-specific components

- Admin Sidebar with navigation and settings links
- Admin SettingsNav sub-navigation
- Customer ProviderButton with brand colors
- Customer CodeInput with auto-advance and paste support

Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

## Task 12: Layouts

**Files:**
- Create: `resources/js/Layouts/CustomerLayout.vue`
- Create: `resources/js/Layouts/AdminLayout.vue`
- Create: `resources/js/Layouts/SettingsLayout.vue`

**Steps:**

- [ ] Create directory:
```bash
mkdir -p resources/js/Layouts
```

- [ ] Create `resources/js/Layouts/CustomerLayout.vue`:

```vue
<script setup>
import { usePage, Head } from '@inertiajs/vue3';
import { useTheme } from '@/Composables/useTheme';

const page = usePage();
const { effectiveMode } = useTheme();

const theme = page.props.theme || {};
const legal = page.props.legal || {};
</script>

<template>
    <div data-testid="customer-layout" class="flex min-h-screen flex-col items-center justify-center bg-[var(--color-bg)] px-4 py-8">
        <Head :title="$page.props.title ?? ''" />

        <div class="w-full max-w-[420px]">
            <div v-if="theme.site_title" class="mb-8 text-center">
                <h1 class="font-heading text-xl font-bold text-[var(--color-text)]">
                    {{ theme.site_title }}
                </h1>
            </div>

            <slot />
        </div>

        <footer v-if="legal.terms_url || legal.privacy_url" class="mt-auto pt-8">
            <div class="flex gap-4 text-[11px] text-[var(--color-text-muted)]">
                <a v-if="legal.terms_url" :href="legal.terms_url" target="_blank" class="hover:text-[var(--color-text-secondary)]">
                    Terms of Service
                </a>
                <a v-if="legal.privacy_url" :href="legal.privacy_url" target="_blank" class="hover:text-[var(--color-text-secondary)]">
                    Privacy Policy
                </a>
            </div>
        </footer>

        <component v-if="theme.custom_css" :is="'style'" v-text="theme.custom_css" />
    </div>
</template>
```

- [ ] Create `resources/js/Layouts/AdminLayout.vue`:

```vue
<script setup>
import { usePage, Head } from '@inertiajs/vue3';
import { useTheme } from '@/Composables/useTheme';
import Sidebar from '@/Components/Admin/Sidebar.vue';

const page = usePage();
useTheme();
</script>

<template>
    <div data-testid="admin-layout" class="flex min-h-screen bg-[var(--color-bg)]">
        <Head :title="$page.props.title ?? ''" />

        <Sidebar />

        <div class="flex min-w-0 flex-1 flex-col">
            <header
                data-testid="admin-header"
                class="sticky top-0 z-50 flex h-12 items-center justify-between border-b border-[var(--color-border)] bg-[var(--color-surface)] px-6"
            >
                <div />
                <div class="flex items-center gap-4">
                    <div v-if="page.props.auth.user" class="text-[13px] text-[var(--color-text-secondary)]">
                        {{ page.props.auth.user.nickname }}
                    </div>
                </div>
            </header>

            <div v-if="page.props.flash?.success || page.props.flash?.error" class="px-10 pt-4">
                <div v-if="page.props.flash.success" class="rounded-md border border-[var(--color-success)]/20 bg-[var(--color-success)]/10 px-4 py-3 text-[13px] text-[var(--color-success)]">
                    {{ page.props.flash.success }}
                </div>
                <div v-if="page.props.flash.error" class="rounded-md border border-[var(--color-danger)]/20 bg-[var(--color-danger)]/10 px-4 py-3 text-[13px] text-[var(--color-danger)]">
                    {{ page.props.flash.error }}
                </div>
            </div>

            <main class="mx-auto w-full max-w-[1400px] flex-1 px-10 pt-8 pb-16">
                <slot />
            </main>
        </div>
    </div>
</template>
```

- [ ] Create `resources/js/Layouts/SettingsLayout.vue`:

```vue
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SettingsNav from '@/Components/Admin/SettingsNav.vue';
</script>

<template>
    <AdminLayout>
        <div class="-mx-10 -mt-8 -mb-16 flex min-h-full">
            <div class="border-r border-[var(--color-border)] px-6 pt-8">
                <SettingsNav />
            </div>
            <div class="flex-1 px-10 pt-8 pb-16">
                <slot />
            </div>
        </div>
    </AdminLayout>
</template>
```

- [ ] Commit:
```bash
git add -A && git commit -m "feat: add CustomerLayout, AdminLayout, SettingsLayout

- CustomerLayout: centered dark shell with legal footer
- AdminLayout: sidebar + header + flash messages
- SettingsLayout: wraps AdminLayout with settings sub-nav

Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

## Task 13: Api\OAuthController (Move + Tests)

**Files:**
- Create: `app/Http/Controllers/Api/OAuthController.php` — move from existing OAuthController, preserve device() and token() methods
- Test: `tests/Feature/Api/OAuthControllerTest.php`

**Steps:**

- [ ] Read the current OAuthController at `app/Http/Controllers/OAuthController.php` to understand the device() and token() methods

- [ ] Write tests — `tests/Feature/Api/OAuthControllerTest.php`:

```php
<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use App\Models\DeviceCode;
use App\Models\SocialProvider;
use App\Enums\DeviceCodeStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_device_endpoint_requires_client_auth(): void
    {
        $this->postJson('/oauth2/device')
            ->assertUnauthorized();
    }

    public function test_device_endpoint_returns_device_code(): void
    {
        $client = Client::factory()->create(['enabled' => true]);
        $provider = SocialProvider::where('enabled', true)->first();

        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode($client->client_id . ':' . $client->client_secret),
        ])->postJson('/oauth2/device', [
            'scope' => $provider->code,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['device_code', 'user_code', 'verification_uri', 'expires_in', 'interval'],
            ]);
    }

    public function test_token_endpoint_returns_pending_for_unresolved_code(): void
    {
        $client = Client::factory()->create(['enabled' => true]);
        $deviceCode = DeviceCode::factory()->create([
            'client_id' => $client->id,
            'status' => DeviceCodeStatus::dcsPending,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode($client->client_id . ':' . $client->client_secret),
        ])->postJson('/oauth2/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:device_code',
            'device_code' => $deviceCode->device_code,
        ]);

        $response->assertStatus(428);
    }
}
```

**Note:** You'll need to check if Client and DeviceCode factories exist. If not, create them. Also check the current `auth:client` guard implementation.

- [ ] Create `app/Http/Controllers/Api/OAuthController.php` by moving the existing controller's `device()` and `token()` logic into the new namespace. Keep the exact same logic — just change the namespace.

- [ ] Update `routes/oauth2.php` to point to the new controller namespace `App\Http\Controllers\Api\OAuthController`

- [ ] Run tests:
```bash
php artisan test --filter=OAuthControllerTest
# Expected: all tests pass
```

- [ ] Commit:
```bash
git add -A && git commit -m "refactor: move OAuthController to Api namespace

- Move device() and token() to App\Http\Controllers\Api\OAuthController
- Update oauth2.php routes
- Add API contract tests

Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

## Task 14: Customer DeviceFlowController + Pages

**Files:**
- Create: `app/Http/Controllers/Customer/DeviceFlowController.php`
- Modify: `resources/js/Pages/Customer/Code.vue` (replace placeholder)
- Create: `resources/js/Pages/Customer/Providers.vue`
- Create: `resources/js/Pages/Customer/Success.vue`
- Create: `resources/js/Pages/Customer/Error.vue`
- Test: `tests/Feature/Customer/DeviceFlowTest.php`

**Steps:**

- [ ] Write tests — `tests/Feature/Customer/DeviceFlowTest.php`:

```php
<?php

namespace Tests\Feature\Customer;

use App\Models\Client;
use App\Models\DeviceCode;
use App\Models\SocialProvider;
use App\Enums\DeviceCodeStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_code_page_renders(): void
    {
        $this->get('/auth')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Customer/Code'));
    }

    public function test_submit_valid_code_redirects_to_providers(): void
    {
        $client = Client::factory()->create(['enabled' => true]);
        $deviceCode = DeviceCode::factory()->create([
            'client_id' => $client->id,
            'status' => DeviceCodeStatus::dcsPending,
        ]);

        $this->post('/auth', ['code' => $deviceCode->user_code])
            ->assertRedirect('/auth/providers');
    }

    public function test_submit_invalid_code_returns_error(): void
    {
        $this->post('/auth', ['code' => 'ZZZZ'])
            ->assertRedirect()
            ->assertSessionHasErrors('code');
    }

    public function test_providers_page_renders_for_valid_session(): void
    {
        $client = Client::factory()->create(['enabled' => true]);
        $deviceCode = DeviceCode::factory()->create([
            'client_id' => $client->id,
            'status' => DeviceCodeStatus::dcsPending,
        ]);

        $this->withSession(['device_code_id' => $deviceCode->id])
            ->get('/auth/providers')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Customer/Providers'));
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
}
```

- [ ] Run tests to verify they fail:
```bash
php artisan test --filter=DeviceFlowTest
```

- [ ] Create `app/Http/Controllers/Customer/DeviceFlowController.php`:

```php
<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DeviceCode;
use App\Models\SocialProvider;
use App\Enums\DeviceCodeStatus;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DeviceFlowController extends Controller
{
    public function code()
    {
        return Inertia::render('Customer/Code');
    }

    public function submitCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:4',
        ]);

        $deviceCode = DeviceCode::where('user_code', strtoupper($request->code))
            ->where('status', DeviceCodeStatus::dcsPending)
            ->where('expires_at', '>', now())
            ->first();

        if (!$deviceCode) {
            return back()->withErrors(['code' => 'Invalid or expired code. Please try again.']);
        }

        $request->session()->put('device_code_id', $deviceCode->id);

        return redirect('/auth/providers');
    }

    public function providers(Request $request)
    {
        $deviceCodeId = $request->session()->get('device_code_id');

        if (!$deviceCodeId) {
            return redirect('/auth');
        }

        $deviceCode = DeviceCode::find($deviceCodeId);

        if (!$deviceCode || $deviceCode->status !== DeviceCodeStatus::dcsPending || $deviceCode->expires_at <= now()) {
            $request->session()->forget('device_code_id');
            return redirect('/auth/error')->with('reason', 'expired');
        }

        $providers = SocialProvider::where('enabled', true)->get();

        return Inertia::render('Customer/Providers', [
            'providers' => $providers->map(fn ($p) => [
                'code' => $p->code,
                'name' => $p->name,
            ]),
        ]);
    }

    public function providerRedirect(Request $request, string $provider)
    {
        $deviceCodeId = $request->session()->get('device_code_id');

        if (!$deviceCodeId) {
            return redirect('/auth');
        }

        $socialProvider = SocialProvider::where('code', $provider)->where('enabled', true)->firstOrFail();

        // Use the same Socialite resolution pattern as the existing codebase
        // Read app/Services/ and AppServiceProvider to match the current approach
        return \Laravel\Socialite\Facades\Socialite::driver($socialProvider->code)->redirect();
    }

    public function providerCallback(Request $request, string $provider)
    {
        $deviceCodeId = $request->session()->get('device_code_id');

        if (!$deviceCodeId) {
            return redirect('/auth/error')->with('reason', 'session');
        }

        $deviceCode = DeviceCode::find($deviceCodeId);

        if (!$deviceCode || $deviceCode->status !== DeviceCodeStatus::dcsPending) {
            return redirect('/auth/error')->with('reason', 'invalid');
        }

        try {
            $socialProvider = SocialProvider::where('code', $provider)->where('enabled', true)->firstOrFail();
            $socialUser = \Laravel\Socialite\Facades\Socialite::driver($socialProvider->code)->user();

            $deviceCode->update([
                'status' => DeviceCodeStatus::dcsSuccessful,
                'social_provider_id' => $socialProvider->id,
                'external_id' => $socialUser->getId(),
                'email' => $socialUser->getEmail(),
                'nickname' => $socialUser->getNickname() ?? $socialUser->getName(),
                'avatar_url' => $socialUser->getAvatar(),
                'access_token' => $socialUser->token,
                'refresh_token' => $socialUser->refreshToken,
                'access_token_expires_at' => $socialUser->expiresIn ? now()->addSeconds($socialUser->expiresIn) : null,
            ]);

            $request->session()->forget('device_code_id');
            return redirect('/auth/success');
        } catch (\Exception $e) {
            $deviceCode->update(['status' => DeviceCodeStatus::dcsFailed]);
            $request->session()->forget('device_code_id');
            return redirect('/auth/error')->with('reason', 'auth_failed');
        }
    }

    public function success()
    {
        return Inertia::render('Customer/Success');
    }

    public function error(Request $request)
    {
        return Inertia::render('Customer/Error', [
            'reason' => session('reason', 'unknown'),
        ]);
    }
}
```

**Note:** The `providerRedirect` and `providerCallback` methods need to adapt from the existing OAuthController's `auth()` and `return()` methods. Read the current OAuthController first and match the existing Socialite integration pattern. The code above is a starting template — adjust based on how providers are currently resolved (via service container, SocialProviderContract implementations, etc.).

- [ ] Create Vue pages. Reference the prototype HTML at `docs/mockups/borealis-prototype.html` for exact layout, copy, and styling. Use CustomerLayout for all pages.

Replace `resources/js/Pages/Customer/Code.vue`:

```vue
<script setup>
import { useForm } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import CodeInput from '@/Components/Customer/CodeInput.vue';

defineOptions({ layout: CustomerLayout });

const form = useForm({ code: '' });

function onCodeComplete(code) {
    form.code = code;
    form.post('/auth');
}
</script>

<template>
    <div class="text-center">
        <h2 class="font-heading text-2xl font-bold text-[var(--color-text)] mb-2">
            Link Your Account
        </h2>
        <p class="text-[var(--color-text-secondary)] text-sm mb-8">
            Enter the code shown on your screen
        </p>

        <CodeInput
            :error="form.errors.code"
            @complete="onCodeComplete"
        />

        <div v-if="form.processing" class="mt-6 text-[var(--color-text-muted)] text-sm">
            Verifying…
        </div>
    </div>
</template>
```

Create `resources/js/Pages/Customer/Providers.vue`:

```vue
<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import ProviderButton from '@/Components/Customer/ProviderButton.vue';

defineOptions({ layout: CustomerLayout });

defineProps({
    providers: { type: Array, default: () => [] },
});
</script>

<template>
    <div class="text-center">
        <h2 class="font-heading text-2xl font-bold text-[var(--color-text)] mb-2">
            Choose Provider
        </h2>
        <p class="text-[var(--color-text-secondary)] text-sm mb-8">
            Select an account to link
        </p>

        <div class="space-y-3">
            <ProviderButton
                v-for="provider in providers"
                :key="provider.code"
                :provider="provider"
                :href="`/auth/${provider.code}/redirect`"
            />
        </div>

        <div v-if="!providers.length" class="py-8 text-[var(--color-text-muted)] text-sm">
            No providers are currently configured.
        </div>
    </div>
</template>
```

Create `resources/js/Pages/Customer/Success.vue`:

```vue
<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

defineOptions({ layout: CustomerLayout });
</script>

<template>
    <div class="text-center">
        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-[var(--color-success)]/10">
            <span class="text-3xl">✓</span>
        </div>

        <h2 class="font-heading text-2xl font-bold text-[var(--color-text)] mb-2">
            Account Linked
        </h2>
        <p class="text-[var(--color-text-secondary)] text-sm">
            You can close this window and return to your application.
        </p>
    </div>
</template>
```

Create `resources/js/Pages/Customer/Error.vue`:

```vue
<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

defineOptions({ layout: CustomerLayout });

defineProps({
    reason: { type: String, default: 'unknown' },
});

const messages = {
    expired: 'The code has expired. Please request a new one.',
    session: 'Your session was lost. Please start over.',
    invalid: 'The code is no longer valid.',
    auth_failed: 'Authentication with the provider failed. Please try again.',
    unknown: 'Something went wrong. Please try again.',
};
</script>

<template>
    <div class="text-center">
        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-[var(--color-danger)]/10">
            <span class="text-3xl">✕</span>
        </div>

        <h2 class="font-heading text-2xl font-bold text-[var(--color-text)] mb-2">
            Something Went Wrong
        </h2>
        <p class="text-[var(--color-text-secondary)] text-sm mb-8">
            {{ messages[reason] || messages.unknown }}
        </p>

        <a
            href="/auth"
            class="inline-block rounded-lg bg-[var(--color-primary)] px-6 py-3 text-[15px] font-semibold text-[var(--color-accent-text)] transition-colors hover:bg-[var(--color-primary-hover)]"
        >
            Try Again
        </a>
    </div>
</template>
```

- [ ] Run tests:
```bash
php artisan test --filter=DeviceFlowTest
# Expected: all tests pass
```

- [ ] Commit:
```bash
git add -A && git commit -m "feat: add customer device flow controller + Vue pages

- DeviceFlowController with code entry, provider selection, callbacks
- Code.vue with CodeInput component
- Providers.vue with ProviderButton components
- Success.vue and Error.vue result pages

Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

## Task 15: Admin Auth Controllers + Login Page

**Files:**
- Create: `app/Http/Controllers/Admin/Auth/LoginController.php`
- Create: `app/Http/Controllers/Admin/Auth/PasskeyController.php`
- Create: `app/Http/Controllers/Admin/Auth/LogoutController.php`
- Create: `resources/js/Pages/Admin/Login.vue`
- Test: `tests/Feature/Admin/Auth/LoginTest.php`

**Steps:**

- [ ] Install webauthn:
```bash
composer require laragear/webauthn
```

- [ ] Publish and run webauthn migrations:
```bash
php artisan vendor:publish --provider="Laragear\WebAuthn\WebAuthnServiceProvider" --tag="migrations"
php artisan migrate
```

- [ ] Write tests — `tests/Feature/Admin/Auth/LoginTest.php`:

```php
<?php

namespace Tests\Feature\Admin\Auth;

use App\Models\User;
use App\Models\SocialProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
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
```

- [ ] Create `app/Http/Controllers/Admin/Auth/LoginController.php`:

```php
<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialProvider;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    public function showLogin()
    {
        $providers = SocialProvider::where('enabled', true)->get()->map(fn ($p) => [
            'code' => $p->code,
            'name' => $p->name,
        ]);

        return Inertia::render('Admin/Login', [
            'providers' => $providers,
        ]);
    }

    public function passwordLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
            'is_admin' => true,
        ])) {
            return back()->withErrors(['email' => 'Invalid credentials.']);
        }

        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function socialRedirect(string $provider)
    {
        $socialProvider = SocialProvider::where('code', $provider)->where('enabled', true)->firstOrFail();
        return Socialite::driver($socialProvider->code)->redirect();
    }

    public function socialCallback(Request $request, string $provider)
    {
        $socialProvider = SocialProvider::where('code', $provider)->where('enabled', true)->firstOrFail();

        try {
            $socialUser = Socialite::driver($socialProvider->code)->user();
        } catch (\Exception $e) {
            return redirect()->route('admin.login')->with('error', 'Authentication failed. Please try again.');
        }

        $user = User::where('external_id', $socialUser->getId())
            ->where('social_provider_id', $socialProvider->id)
            ->first();

        if (!$user) {
            $user = User::create([
                'nickname' => $socialUser->getNickname() ?? $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'external_id' => $socialUser->getId(),
                'social_provider_id' => $socialProvider->id,
                'access_token' => $socialUser->token,
                'refresh_token' => $socialUser->refreshToken,
                'access_token_expires_at' => $socialUser->expiresIn ? now()->addSeconds($socialUser->expiresIn) : null,
            ]);
        }

        if (!$user->is_admin) {
            return redirect()->route('admin.login')->with('error', "You don't have admin access.");
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }
}
```

- [ ] Create `app/Http/Controllers/Admin/Auth/LogoutController.php`:

```php
<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
```

- [ ] Create `app/Http/Controllers/Admin/Auth/PasskeyController.php`:

```php
<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laragear\WebAuthn\Http\Requests\AssertedRequest;
use Laragear\WebAuthn\Http\Requests\AssertionRequest;
use Laragear\WebAuthn\Http\Requests\AttestationRequest;
use Laragear\WebAuthn\Http\Requests\AttestedRequest;

class PasskeyController extends Controller
{
    public function assertionOptions(AssertionRequest $request)
    {
        return $request->toVerify();
    }

    public function verify(AssertedRequest $request)
    {
        $user = $request->login();

        if (!$user || !$user->is_admin) {
            abort(403, "You don't have admin access.");
        }

        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function registerOptions(AttestationRequest $request)
    {
        return $request->toCreate();
    }

    public function register(AttestedRequest $request)
    {
        $request->save();

        return back()->with('success', 'Passkey registered successfully.');
    }
}
```

**Note:** The PasskeyController uses laragear/webauthn's request classes. Read the package documentation to verify exact class names and methods. The User model must use the `WebAuthnAuthenticatable` trait.

- [ ] Create `resources/js/Pages/Admin/Login.vue` — reference `docs/mockups/borealis-prototype.html` for exact layout and styling. The page should include:
  - Email/password form
  - Social provider buttons
  - Passkey login button with WebAuthn browser API integration
  - Use CustomerLayout (centered, no sidebar)

- [ ] Run tests:
```bash
php artisan test --filter=LoginTest
# Expected: all tests pass
```

- [ ] Commit:
```bash
git add -A && git commit -m "feat: add admin auth (password, social, passkey)

- LoginController with password and social login
- PasskeyController with WebAuthn assertion/attestation
- LogoutController
- Admin Login.vue page
- Install laragear/webauthn

Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

## Task 16: Admin Dashboard

**Files:**
- Create: `app/Http/Controllers/Admin/DashboardController.php`
- Create: `resources/js/Pages/Admin/Dashboard.vue`
- Test: `tests/Feature/Admin/DashboardTest.php`

**Steps:**

- [ ] Write tests — `tests/Feature/Admin/DashboardTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Client;
use App\Models\DeviceCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
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
```

- [ ] Run tests to verify fail:
```bash
php artisan test --filter=DashboardTest
```

- [ ] Create `app/Http/Controllers/Admin/DashboardController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\DeviceCode;
use App\Models\SocialProvider;
use App\Enums\DeviceCodeStatus;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_clients' => Client::count(),
                'active_clients' => Client::where('enabled', true)->count(),
                'total_auths' => DeviceCode::where('status', DeviceCodeStatus::dcsSuccessful)->count(),
                'pending_codes' => DeviceCode::where('status', DeviceCodeStatus::dcsPending)
                    ->where('expires_at', '>', now())
                    ->count(),
            ],
            'recentAuths' => DeviceCode::where('status', DeviceCodeStatus::dcsSuccessful)
                ->with(['provider', 'client'])
                ->latest()
                ->take(10)
                ->get()
                ->map(fn ($dc) => [
                    'id' => $dc->id,
                    'nickname' => $dc->nickname,
                    'email' => $dc->email,
                    'provider' => $dc->provider?->name,
                    'client' => $dc->client?->name,
                    'created_at' => $dc->created_at->diffForHumans(),
                ]),
        ]);
    }
}
```

- [ ] Create `resources/js/Pages/Admin/Dashboard.vue` — reference prototype for exact layout with StatCards, SectionHeader, DataTable. Use AdminLayout. Include:
  - Four stat cards (Total Clients, Active Clients, Total Auths, Pending Codes)
  - Recent authentications table with nickname, email, provider, client, time columns

- [ ] Run tests:
```bash
php artisan test --filter=DashboardTest
# Expected: all 3 tests pass
```

- [ ] Commit:
```bash
git add -A && git commit -m "feat: add admin dashboard with stats and recent auths

- DashboardController with client/auth statistics
- Dashboard.vue with StatCards and DataTable
- Access control via EnsureAdmin middleware

Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

## Task 17: Admin Providers

**Files:**
- Create: `app/Http/Controllers/Admin/ProviderController.php`
- Create: `resources/js/Pages/Admin/Providers/Index.vue`
- Test: `tests/Feature/Admin/ProviderTest.php`

**Steps:**

- [ ] Write tests — `tests/Feature/Admin/ProviderTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\SocialProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
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
                'enabled' => !$provider->enabled,
            ])
            ->assertRedirect();
    }
}
```

- [ ] Run tests to verify fail:
```bash
php artisan test --filter=ProviderTest
```

- [ ] Create `app/Http/Controllers/Admin/ProviderController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialProvider;
use App\Models\SocialProviderSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProviderController extends Controller
{
    public function index()
    {
        $providers = SocialProvider::with('settings')->get()->map(fn ($provider) => [
            'id' => $provider->id,
            'name' => $provider->name,
            'code' => $provider->code,
            'enabled' => $provider->enabled,
            'settings' => $provider->settings->map(fn ($s) => [
                'id' => $s->id,
                'key' => $s->key,
                'value' => $s->value,
            ]),
        ]);

        return Inertia::render('Admin/Providers/Index', [
            'providers' => $providers,
        ]);
    }

    public function update(Request $request, SocialProvider $provider)
    {
        $request->validate([
            'enabled' => 'sometimes|boolean',
            'settings' => 'sometimes|array',
            'settings.*.id' => 'required_with:settings|exists:social_provider_settings,id',
            'settings.*.value' => 'required_with:settings|string',
        ]);

        if ($request->has('enabled')) {
            $provider->update(['enabled' => $request->boolean('enabled')]);
        }

        if ($request->has('settings')) {
            foreach ($request->input('settings') as $setting) {
                SocialProviderSetting::where('id', $setting['id'])->update([
                    'value' => $setting['value'],
                ]);
            }
        }

        return back()->with('success', 'Provider updated.');
    }
}
```

- [ ] Create `resources/js/Pages/Admin/Providers/Index.vue` — reference prototype. Use AdminLayout. Include provider cards with enable/disable toggles and expandable settings sections.

- [ ] Run tests:
```bash
php artisan test --filter=ProviderTest
# Expected: all tests pass
```

- [ ] Commit:
```bash
git add -A && git commit -m "feat: add admin provider management

- ProviderController with index/update
- Provider Index.vue with enable toggles and settings
- Access control via EnsureAdmin middleware

Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

## Task 18: Admin Clients

**Files:**
- Create: `app/Http/Controllers/Admin/ClientController.php`
- Create: `resources/js/Pages/Admin/Clients/Index.vue`
- Create: `resources/js/Pages/Admin/Clients/Show.vue`
- Create: `resources/js/Pages/Admin/Clients/Create.vue`
- Test: `tests/Feature/Admin/ClientTest.php`

**Steps:**

- [ ] Write tests — `tests/Feature/Admin/ClientTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $this->get('/admin/clients')
            ->assertRedirect('/admin/login');
    }

    public function test_non_admin_gets_403(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get('/admin/clients')
            ->assertStatus(403);
    }

    public function test_admin_can_view_clients(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $this->actingAs($user)
            ->get('/admin/clients')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Clients/Index')
                ->has('clients')
            );
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
            ->post('/admin/clients', [
                'name' => 'Test Client',
                'enabled' => true,
            ])
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
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Clients/Show')
                ->has('client')
            );
    }

    public function test_admin_can_update_client(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $client = Client::factory()->create();

        $this->actingAs($user)
            ->put("/admin/clients/{$client->id}", [
                'name' => 'Updated Client',
                'enabled' => false,
            ])
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
```

- [ ] Run tests to verify fail:
```bash
php artisan test --filter=ClientTest
```

- [ ] Create `app/Http/Controllers/Admin/ClientController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::latest()->get()->map(fn ($client) => [
            'id' => $client->id,
            'name' => $client->name,
            'client_id' => $client->client_id,
            'enabled' => $client->enabled,
            'created_at' => $client->created_at->diffForHumans(),
        ]);

        return Inertia::render('Admin/Clients/Index', [
            'clients' => $clients,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Clients/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'enabled' => 'boolean',
        ]);

        Client::create([
            'name' => $request->name,
            'client_id' => Str::uuid()->toString(),
            'client_secret' => Str::random(64),
            'enabled' => $request->boolean('enabled', true),
        ]);

        return redirect('/admin/clients')->with('success', 'Client created.');
    }

    public function show(Client $client)
    {
        return Inertia::render('Admin/Clients/Show', [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'client_id' => $client->client_id,
                'client_secret' => $client->client_secret,
                'enabled' => $client->enabled,
                'created_at' => $client->created_at->toIso8601String(),
                'updated_at' => $client->updated_at->toIso8601String(),
            ],
        ]);
    }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'enabled' => 'sometimes|boolean',
        ]);

        $client->update($request->only(['name', 'enabled']));

        return back()->with('success', 'Client updated.');
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect('/admin/clients')->with('success', 'Client deleted.');
    }
}
```

**Note:** Check the existing `ClientRequest` form request and `ClientObserver` for validation rules and auto-generation logic. The `store()` method above generates client_id/client_secret — verify this matches the existing observer pattern. If the `ClientObserver` handles this, omit it from the controller.

- [ ] Create Vue pages — Index.vue, Show.vue, Create.vue. Use AdminLayout. Reference prototype for layouts.

- [ ] Run tests:
```bash
php artisan test --filter=ClientTest
# Expected: all tests pass
```

- [ ] Commit:
```bash
git add -A && git commit -m "feat: add admin client CRUD

- ClientController with index/create/store/show/update/destroy
- Clients Index, Show, Create Vue pages
- Full CRUD tests with admin access control

Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

## Task 19: Admin Settings

**Files:**
- Create: `app/Http/Controllers/Admin/Settings/ThemeController.php`
- Create: `app/Http/Controllers/Admin/Settings/GeneralController.php`
- Create: `resources/js/Pages/Admin/Settings/Theme.vue`
- Create: `resources/js/Pages/Admin/Settings/General.vue`
- Test: `tests/Feature/Admin/Settings/ThemeSettingsTest.php`
- Test: `tests/Feature/Admin/Settings/GeneralSettingsTest.php`

**Steps:**

- [ ] Write tests — `tests/Feature/Admin/Settings/ThemeSettingsTest.php`:

```php
<?php

namespace Tests\Feature\Admin\Settings;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
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
```

- [ ] Write tests — `tests/Feature/Admin/Settings/GeneralSettingsTest.php`:

```php
<?php

namespace Tests\Feature\Admin\Settings;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeneralSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
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
```

- [ ] Run tests to verify fail:
```bash
php artisan test --filter=ThemeSettingsTest --filter=GeneralSettingsTest
```

- [ ] Create `app/Http/Controllers/Admin/Settings/ThemeController.php`:

```php
<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ThemeController extends Controller
{
    public function show()
    {
        return Inertia::render('Admin/Settings/Theme', [
            'settings' => [
                'accent_hue' => Setting::fetch('accent_hue', '55'),
                'color_mode' => Setting::fetch('color_mode', 'dark'),
                'site_title' => Setting::fetch('site_title', ''),
                'custom_css' => Setting::fetch('custom_css', ''),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'accent_hue' => 'required|string',
            'color_mode' => 'required|in:light,dark,system',
            'site_title' => 'nullable|string|max:255',
            'custom_css' => 'nullable|string|max:10000',
        ]);

        foreach (['accent_hue', 'color_mode', 'site_title', 'custom_css'] as $key) {
            $setting = Setting::where('code', $key)->first();
            if ($setting) {
                $setting->update(['value' => $request->input($key, '')]);
                cache()->forget("settings.{$key}");
            }
        }

        return back()->with('success', 'Theme settings updated.');
    }
}
```

- [ ] Create `app/Http/Controllers/Admin/Settings/GeneralController.php`:

```php
<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GeneralController extends Controller
{
    public function show()
    {
        return Inertia::render('Admin/Settings/General', [
            'settings' => [
                'terms_url' => Setting::fetch('terms_url', ''),
                'privacy_url' => Setting::fetch('privacy_url', ''),
                'device_code_expiry' => Setting::fetch('device_code_expiry', '300'),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'terms_url' => 'nullable|url|max:500',
            'privacy_url' => 'nullable|url|max:500',
            'device_code_expiry' => 'required|integer|min:60|max:3600',
        ]);

        foreach (['terms_url', 'privacy_url', 'device_code_expiry'] as $key) {
            $setting = Setting::where('code', $key)->first();
            if ($setting) {
                $setting->update(['value' => $request->input($key, '')]);
                cache()->forget("settings.{$key}");
            }
        }

        return back()->with('success', 'General settings updated.');
    }
}
```

- [ ] Create Vue pages — Theme.vue and General.vue. Use SettingsLayout. Reference prototype for layouts. Theme.vue should include accent hue preset picker with color swatches and live preview. General.vue should include URL inputs and expiry field.

- [ ] Run tests:
```bash
php artisan test --filter=ThemeSettingsTest --filter=GeneralSettingsTest
# Expected: all tests pass
```

- [ ] Commit:
```bash
git add -A && git commit -m "feat: add admin settings (theme + general)

- ThemeController: accent_hue, color_mode, site_title, custom_css
- GeneralController: terms_url, privacy_url, device_code_expiry
- Theme.vue with accent hue picker and live preview
- General.vue with URL inputs and expiry setting
- Full test coverage

Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

## Task 20: Route Wiring

**Files:**
- Modify: `routes/web.php` — complete rewrite per spec section 8
- Modify: `routes/oauth2.php` — update controller namespace

**Steps:**

- [ ] Rewrite `routes/web.php`:

```php
<?php

use App\Http\Controllers\Customer\DeviceFlowController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProviderController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\Settings\ThemeController;
use App\Http\Controllers\Admin\Settings\GeneralController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\PasskeyController;
use App\Http\Controllers\Admin\Auth\LogoutController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

// Customer device flow
Route::prefix('/auth')->group(function () {
    Route::get('/', [DeviceFlowController::class, 'code']);
    Route::post('/', [DeviceFlowController::class, 'submitCode']);
    Route::get('/providers', [DeviceFlowController::class, 'providers']);
    Route::get('/{provider}/redirect', [DeviceFlowController::class, 'providerRedirect']);
    Route::get('/{provider}/callback', [DeviceFlowController::class, 'providerCallback']);
    Route::get('/success', [DeviceFlowController::class, 'success']);
    Route::get('/error', [DeviceFlowController::class, 'error']);
});

// Admin auth (public)
Route::prefix('/admin')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [LoginController::class, 'passwordLogin']);
    Route::get('/login/{provider}/redirect', [LoginController::class, 'socialRedirect']);
    Route::get('/login/{provider}/callback', [LoginController::class, 'socialCallback']);
    Route::post('/login/passkey/options', [PasskeyController::class, 'assertionOptions']);
    Route::post('/login/passkey/verify', [PasskeyController::class, 'verify']);
});

// Admin protected
Route::prefix('/admin')->middleware(['web', 'auth', EnsureAdmin::class])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::post('/logout', [LogoutController::class, 'logout'])->name('admin.logout');
    Route::resource('providers', ProviderController::class)->only(['index', 'update']);
    Route::resource('clients', ClientController::class);
    Route::get('/settings/theme', [ThemeController::class, 'show'])->name('admin.settings.theme');
    Route::put('/settings/theme', [ThemeController::class, 'update']);
    Route::get('/settings/general', [GeneralController::class, 'show'])->name('admin.settings.general');
    Route::put('/settings/general', [GeneralController::class, 'update']);
    Route::post('/passkey/register/options', [PasskeyController::class, 'registerOptions']);
    Route::post('/passkey/register', [PasskeyController::class, 'register']);
});
```

- [ ] Update `routes/oauth2.php` to use `App\Http\Controllers\Api\OAuthController`

- [ ] Run full test suite:
```bash
php artisan test
# Expected: all tests pass
```

- [ ] Verify route list:
```bash
php artisan route:list
```

- [ ] Commit:
```bash
git add -A && git commit -m "feat: wire all routes (customer, admin, API)

- Customer device flow routes at /auth/*
- Admin auth routes at /admin/login/*
- Admin protected routes at /admin/* with EnsureAdmin middleware
- Update oauth2.php to use Api\OAuthController

Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

## Task 21: Legacy Cleanup

**Files to remove (exact paths):**

All blade views EXCEPT `resources/views/app.blade.php`:
- `resources/views/auth/code.blade.php`
- `resources/views/auth/return.blade.php`
- `resources/views/clients/_breadcrumbs.blade.php`
- `resources/views/clients/_form.blade.php`
- `resources/views/clients/create.blade.php`
- `resources/views/clients/delete.blade.php`
- `resources/views/clients/edit.blade.php`
- `resources/views/clients/index.blade.php`
- `resources/views/clients/show.blade.php`
- `resources/views/home/index.blade.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/login.blade.php`
- `resources/views/partials/_pagination.blade.php`
- `resources/views/partials/_providersconfig.blade.php`
- `resources/views/partials/_searchselectfield.blade.php`
- `resources/views/partials/_searchtextfield.blade.php`
- `resources/views/partials/_sortheader.blade.php`
- `resources/views/partials/_theme.blade.php`
- `resources/views/users/login.blade.php`

Old controllers:
- `app/Http/Controllers/HomeController.php`
- `app/Http/Controllers/UserController.php`
- `app/Http/Controllers/ClientController.php`
- `app/Http/Controllers/OAuthController.php`
- `app/Http/Controllers/SettingController.php`
- `app/Http/Controllers/SocialProviderController.php`
- `app/Http/Controllers/ThemeController.php`
- `app/Http/Controllers/Api/V1/AuthController.php`

Theme artifacts (if not already removed):
- `app/Models/Theme.php`
- `app/Observers/ThemeObserver.php`

**Steps:**

- [ ] Remove all listed blade files:
```bash
rm -f resources/views/auth/code.blade.php resources/views/auth/return.blade.php
rm -rf resources/views/clients resources/views/home resources/views/layouts resources/views/partials resources/views/users
```

- [ ] Remove all listed old controllers:
```bash
rm -f app/Http/Controllers/HomeController.php
rm -f app/Http/Controllers/UserController.php
rm -f app/Http/Controllers/ClientController.php
rm -f app/Http/Controllers/OAuthController.php
rm -f app/Http/Controllers/SettingController.php
rm -f app/Http/Controllers/SocialProviderController.php
rm -f app/Http/Controllers/ThemeController.php
rm -rf app/Http/Controllers/Api/V1
```

- [ ] Remove theme artifacts (if not already done):
```bash
rm -f app/Models/Theme.php app/Observers/ThemeObserver.php
```

- [ ] Clean `app/Providers/AppServiceProvider.php`:
  - Remove Blade `@setting` directive
  - Remove view composers for themes
  - Remove any Theme model references

- [ ] Remove old example tests:
```bash
rm -f tests/Feature/ExampleTest.php tests/Unit/ExampleTest.php
```

- [ ] Verify vite build:
```bash
npx vite build
# Expected: build completes without errors
```

- [ ] Run full test suite:
```bash
php artisan test
# Expected: all tests pass
```

- [ ] Verify no references to removed files:
```bash
grep -r "HomeController\|UserController\|ThemeController\|SettingController\|SocialProviderController" app/ routes/ --include="*.php"
# Expected: no output (empty)
```

- [ ] Commit:
```bash
git add -A && git commit -m "chore: remove legacy Blade/Bootstrap/Tabler frontend

- Remove all old Blade views (keep only Inertia app.blade.php)
- Remove old controllers (Home, User, Client, OAuth, Setting, SocialProvider, Theme, Api/V1/Auth)
- Remove Theme model and ThemeObserver
- Clean AppServiceProvider (Blade directives, view composers)
- Remove example test stubs

Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

## Task 22: Final Verification

**Steps:**

- [ ] Run full test suite:
```bash
php artisan test
# Expected: all tests pass with no failures
```

- [ ] Run vite build:
```bash
npx vite build
# Expected: build completes cleanly
```

- [ ] Verify no Bootstrap/Tabler references:
```bash
grep -r "bootstrap\|tabler" resources/ --include="*.vue" --include="*.js" --include="*.css"
# Expected: no output (empty)
```

- [ ] Verify no remaining Blade views (except app.blade.php):
```bash
find resources/views -name "*.blade.php" | grep -v app.blade.php
# Expected: no output (empty)
```

- [ ] Check route list:
```bash
php artisan route:list
# Verify all expected routes are registered
```

- [ ] Commit final state:
```bash
git add -A && git commit -m "chore: final verification — all tests pass, build clean

Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

---

## Implementation Notes

### 1. Factories

You'll likely need to create/update factories for User, Client, DeviceCode, and SocialProvider models. Check `database/factories/` first and create any missing ones.

### 2. Setting::fetch()

Check the current Setting model to see if a `fetch()` static method exists. If not, implement it as:

```php
public static function fetch(string $code, $default = null)
{
    return cache()->rememberForever("settings.{$code}", function () use ($code, $default) {
        $setting = static::where('code', $code)->first();
        return $setting ? $setting->value : $default;
    }) ?? $default;
}
```

### 3. Socialite Provider Resolution

The current AppServiceProvider binds Socialite drivers. The DeviceFlowController and LoginController need to use the same mechanism. Read `app/Services/` to understand the provider contract pattern.

### 4. Vue Pages

For every Vue page, reference `docs/mockups/borealis-prototype.html` for exact visual structure. Use the Dispatch CSS tokens consistently. All admin pages use AdminLayout (or SettingsLayout for settings). All customer pages use CustomerLayout.

### 5. No Ziggy

Unlike Aperture, Borealis doesn't use Ziggy for route generation. Use plain href strings in Vue components.
