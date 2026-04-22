# Borealis Vue 3 + Inertia Migration — Implementation Spec

- **Document status:** Approved for implementation
- **Document date:** 2026-04-22
- **Requested file path:** `docs/superpowers/specs/2025-07-25-borealis-vue-migration-design.md`
- **Project:** Borealis
- **Scope:** Full frontend migration from Blade + Bootstrap/Tabler to Vue 3 + Inertia.js + Tailwind CSS 4

## 1. Overview

Borealis will be migrated in a single cut from its current Blade + Bootstrap/Tabler frontend to a Vue 3 + Inertia.js + Tailwind CSS 4 application running inside Laravel 12. This is not a progressive enhancement project and not a hybrid Blade/Inertia transition. The target state is a clean replacement: legacy Blade templates, Bootstrap assets, Tabler dependencies, and theme-specific legacy frontend structures are removed and replaced in one coordinated pass.

The migration preserves Borealis' core backend domain model and business logic, including:

- existing models and database schema unless explicitly changed below;
- OAuth device flow behavior and API semantics;
- Socialite provider integration;
- Sanctum, Horizon, and Octane usage where already present.

The UI must visually follow Aperture's Dispatch design system exactly. The canonical visual reference for all page structure, spacing, tone, and component styling is:

- `docs/mockups/borealis-prototype.html`

Where implementation details in this document and the prototype appear to differ, this specification governs behavior and architecture, while the prototype governs visual design.

## 2. Goals and Non-Goals

### 2.1 Goals

1. Replace all user-facing Blade views with Vue 3 + Inertia pages.
2. Replace Bootstrap/Tabler styling and JS behavior with Tailwind CSS 4 and Vue components.
3. Preserve device OAuth flow behavior and backend token issuance logic.
4. Introduce a single shared theming system sourced from the `settings` table.
5. Introduce an admin authentication experience supporting social login, password login, and passkeys.
6. Restructure controllers and routes into clear customer, admin, and API namespaces.
7. Establish an implementation-ready component library aligned with Dispatch.
8. Ensure the resulting application is testable with Laravel feature tests and browser-level flow coverage.

### 2.2 Non-Goals

The following are explicitly out of scope for this migration and must not expand the work unless a later approved spec says otherwise:

- SSR or hydration beyond standard Inertia client-side rendering;
- mobile admin sidebar redesign beyond deferring to a future enhancement;
- backend redesign of device code issuance, polling, or token storage;
- changing client authentication contracts for `/oauth2/device` and `/oauth2/token`;
- introducing a new design system beyond Dispatch;
- passkey profile management UI beyond the endpoints needed now;
- unrelated model, schema, or observer refactors.

## 3. Technology Stack and Package Changes

### 3.1 Target Stack

- **Backend:** Laravel 12, PHP 8.2+
- **Frontend:** Vue 3, Inertia.js 2, Tailwind CSS 4
- **Bundler:** Vite
- **Vite plugins:** `@vitejs/plugin-vue`, `@tailwindcss/vite`
- **Authentication:** Laravel session auth + `laragear/webauthn` for passkeys
- **Retained packages/services:** Laravel Socialite, Sanctum, Horizon, Octane

### 3.2 Packages to Install

#### NPM

- `vue`
- `@inertiajs/vue3`
- `@vitejs/plugin-vue`

#### Composer

- `inertiajs/inertia-laravel`
- `laragear/webauthn`

### 3.3 Packages to Remove

#### NPM

- `bootstrap`
- `@tabler/core`
- `@tabler/icons-webfont`
- `sass-embedded`
- `popperjs/core`

### 3.4 Build Requirements

- Vite must compile the Vue/Inertia entrypoint from `resources/js/app.js`.
- Tailwind must be loaded from `resources/css/app.css` using Tailwind CSS 4 conventions.
- No SSR entrypoint is required. `resources/js/ssr.js` must not be created unless a future spec explicitly adds SSR.

## 4. Canonical References

Implementation must follow these references in order of authority:

1. This specification for architecture, flows, scope, and behavioral requirements.
2. `docs/mockups/borealis-prototype.html` for visual design and page composition.
3. Aperture Dispatch components and tokens as the source for adapted UI primitives.
4. Existing Borealis backend logic where this specification says to preserve behavior.

## 5. Target Directory and File Structure

The target frontend structure is:

```text
resources/
  js/
    app.js
    Layouts/
      CustomerLayout.vue
      AdminLayout.vue
      SettingsLayout.vue
    Pages/
      Customer/
        Code.vue
        Providers.vue
        Success.vue
        Error.vue
      Admin/
        Login.vue
        Dashboard.vue
        Providers/
          Index.vue
        Clients/
          Index.vue
          Show.vue
          Create.vue
        Settings/
          Theme.vue
          General.vue
    Components/
      UI/
        SectionHeader.vue
        StatCard.vue
        DataTable.vue
        StatusPill.vue
        FormField.vue
        MetadataStrip.vue
        EmptyState.vue
        ConfirmModal.vue
      Admin/
        Sidebar.vue
        SettingsNav.vue
      Customer/
        ProviderButton.vue
        CodeInput.vue
    Composables/
      useTheme.js
      useAccentHue.js
  css/
    app.css
    themes/
      dispatch.css
```

### 5.1 Fonts

Fonts must be hosted locally in `public/fonts/` or `resources/fonts/` with a preference for `public/fonts/` to match the source system and simplify runtime delivery. The required families are:

- Bricolage Grotesque
- Hanken Grotesk
- JetBrains Mono

Font files are copied from Aperture's `public/fonts/` and defined via `@font-face` in `dispatch.css`. No Google Fonts or remote font CDN references are permitted in the final application.

## 6. Backend Restructure

### 6.1 Controller Namespaces

Controllers must be reorganized to the following structure:

```text
app/Http/Controllers/
  Customer/
    DeviceFlowController.php
  Admin/
    DashboardController.php
    ProviderController.php
    ClientController.php
    Settings/
      ThemeController.php
      GeneralController.php
    Auth/
      LoginController.php
      PasskeyController.php
      LogoutController.php
  Api/
    OAuthController.php
```

### 6.2 Required Controller Responsibilities

#### Customer\DeviceFlowController

This controller is the customer-facing device verification surface. It must expose the methods required by the route contract below:

- `code()` — render code entry page
- `submitCode()` — validate code entry and transition to provider selection or error
- `providers()` — render provider selection state for the resolved device code
- `providerRedirect()` — initiate Socialite redirect for selected provider
- `providerCallback()` — complete provider authentication and mark device code successful when appropriate
- `success()` — render success page
- `error()` — render error page with differentiated reason via props

Note: the approved design's summary list omitted `submitCode()` and `providerRedirect()`, but the route contract requires them. They are therefore mandatory.

#### Admin\DashboardController

- `index()` — render dashboard metrics and recent authentications

#### Admin\ProviderController

- `index()` — render provider management index
- `update()` — update provider enabled state and configuration payload

#### Admin\ClientController

- `index()` — render clients table
- `show()` — render client detail/edit page
- `create()` — render create page
- `store()` — persist new client
- `update()` — persist client updates
- `destroy()` — delete client

#### Admin\Settings\ThemeController

- `show()` — render theme settings page
- `update()` — persist accent hue, color mode, site title, and custom CSS settings as applicable

#### Admin\Settings\GeneralController

- `show()` — render general settings page
- `update()` — persist non-theme settings such as legal URLs and device code expiry

#### Admin\Auth\LoginController

- `showLogin()` — render login page
- `socialRedirect()` — begin admin social auth flow
- `socialCallback()` — resolve admin social auth flow, enforce admin access, and log in the session
- `passwordLogin()` — authenticate by email/password for eligible admin users

#### Admin\Auth\PasskeyController

- `registerOptions()` — provide registration options for logged-in admins
- `register()` — store a completed passkey registration
- `assertionOptions()` — provide sign-in assertion options for passkey login
- `verify()` — verify passkey sign-in assertion and authenticate the session

#### Admin\Auth\LogoutController

- `logout()` — invalidate session and redirect to admin login

#### Api\OAuthController

- `device()` — existing device flow issuance behavior, moved into `Api` namespace
- `token()` — existing polling/token issuance behavior, moved into `Api` namespace

Behavior must remain functionally consistent with the current OAuth device flow implementation.

## 7. Middleware

### 7.1 EnsureAdmin

Create `app/Http/Middleware/EnsureAdmin.php` with this responsibility:

- allow request only when `auth()->check()` is true and `auth()->user()->is_admin` is true;
- otherwise return a 403 response or redirect to `/admin/login` based on the request context.

Implementation requirement:

- HTML requests should redirect unauthenticated users to `/admin/login`.
- authenticated but non-admin users must receive a 403 response and must not be silently elevated or redirected as if they were valid admins.

### 7.2 HandleInertiaRequests

Create `app/Http/Middleware/HandleInertiaRequests.php` to share global props with every Inertia response.

This middleware is the single source for shared frontend data including auth, theme, legal links, and flash messages.

## 8. Route Contract

The route structure must be implemented exactly as follows unless a later approved change supersedes it.

```php
Route::prefix('/auth')->group(function () {
    Route::get('/', [DeviceFlowController::class, 'code']);
    Route::post('/', [DeviceFlowController::class, 'submitCode']);
    Route::get('/providers', [DeviceFlowController::class, 'providers']);
    Route::get('/{provider}/redirect', [DeviceFlowController::class, 'providerRedirect']);
    Route::get('/{provider}/callback', [DeviceFlowController::class, 'providerCallback']);
    Route::get('/success', [DeviceFlowController::class, 'success']);
    Route::get('/error', [DeviceFlowController::class, 'error']);
});

Route::prefix('/admin')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [LoginController::class, 'passwordLogin']);
    Route::get('/login/{provider}/redirect', [LoginController::class, 'socialRedirect']);
    Route::get('/login/{provider}/callback', [LoginController::class, 'socialCallback']);
    Route::post('/login/passkey/options', [PasskeyController::class, 'assertionOptions']);
    Route::post('/login/passkey/verify', [PasskeyController::class, 'verify']);
});

Route::prefix('/admin')->middleware(['auth', EnsureAdmin::class])->group(function () {
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

Route::prefix('/oauth2')->middleware(['api', 'auth:client'])->group(function () {
    Route::post('/device', [OAuthController::class, 'device']);
    Route::post('/token', [OAuthController::class, 'token']);
});
```

### 8.1 Route Rules

- `/auth/*` is public and dedicated to customer device verification.
- `/admin/login*` is guest-accessible.
- all other `/admin/*` routes require authenticated admin access.
- `/oauth2/*` remains API-only and preserves current client-authenticated behavior.

## 9. Database Changes

### 9.1 Remove `themes` Table

The `themes` table is removed entirely. Theme persistence moves to the `settings` table.

### 9.2 Modify `users` Table

Apply this schema change:

```php
Schema::table('users', function (Blueprint $table) {
    $table->boolean('is_admin')->default(false)->after('last_login_at');
    $table->string('password')->nullable()->after('is_admin');
});
```

Rules:

- `is_admin` is the sole admin authorization flag for this migration.
- `password` must be nullable so that social-only and passkey-only admins remain valid.
- WebAuthn identity columns are supplied by `laragear/webauthn` package migrations and must not be manually duplicated.

### 9.3 Seed Required Settings

The following settings rows must exist after migration or seed execution:

| code | name | type | value | encrypted | hidden |
|---|---|---|---|---|---|
| accent_hue | Accent Hue | stString | 55 | false | false |
| color_mode | Colour Mode | stString | dark | false | false |
| site_title | Site Title | stString |  | false | false |
| custom_css | Custom CSS | stString |  | false | false |
| terms_url | Terms of Service URL | stString |  | false | false |
| privacy_url | Privacy Policy URL | stString |  | false | false |
| device_code_expiry | Device Code Expiry | stString | 300 | false | false |

Implementation rules:

- seeding must be idempotent;
- empty string values represent unset optional UI fields;
- `device_code_expiry` continues to be stored as a string to match existing settings conventions unless a separate schema normalization spec says otherwise.

## 10. Authentication Design

### 10.1 Customer Device Flow

The customer device flow keeps its current logical behavior and API contract.

Sequence:

1. client app calls `POST /oauth2/device` with `client_id` and `client_secret`;
2. API returns `device_code`, `user_code`, and `verification_uri`;
3. user visits `/auth`, enters the four-digit `user_code`;
4. user is shown enabled social providers;
5. user selects a provider and is redirected through Socialite;
6. provider callback associates the completed authentication with the device code record;
7. device code status is set to `dcsSuccessful` on success;
8. client app polls `POST /oauth2/token` until access token is issued.

Preservation requirements:

- keep the `DeviceCode` model unchanged unless a migration-specific compatibility change is strictly required;
- keep existing observers and polling semantics unchanged;
- keep error response semantics for API endpoints unchanged;
- move controller location without changing the external API contract.

### 10.2 Admin Login Modes

#### 10.2.1 Social Login

Flow:

1. admin visits `/admin/login`;
2. page renders enabled providers;
3. clicking a provider hits `/admin/login/{provider}/redirect`;
4. callback hits `/admin/login/{provider}/callback`;
5. application resolves or creates a `User` by provider identity;
6. application checks `is_admin`;
7. admins are logged into the Laravel session and redirected to dashboard;
8. non-admin users are rejected with the explicit message: `You don't have admin access`.

Requirements:

- provider enablement must honor the same provider configuration source used elsewhere in the app;
- social login must not bypass `is_admin` enforcement;
- user creation logic must preserve existing provider identity semantics.

#### 10.2.2 Password Login

Flow:

1. admin submits email and password to `POST /admin/login`;
2. system validates only against users with `is_admin = true`;
3. password login is only allowed when the user record has a non-null password;
4. `Auth::attempt()` is used for authentication;
5. on success, session is regenerated and user is redirected to dashboard.

Requirements:

- password auth must not authenticate non-admin users;
- validation and failure messages must be generic enough not to leak whether a user exists;
- only password-capable admin accounts may use this method.

#### 10.2.3 Passkey Login

Flow:

1. login page offers `Sign in with passkey`;
2. frontend requests assertion options;
3. browser performs WebAuthn assertion ceremony;
4. frontend sends assertion payload for verification;
5. verified admin user is logged in to the session.

Requirements:

- use `laragear/webauthn` package flows instead of custom WebAuthn primitives;
- passkey login is only available to users who already registered a passkey;
- registration UI is future scope, but the registration endpoints must exist for logged-in admin setup flows.

## 11. Frontend Page Requirements

### 11.1 Layouts

#### CustomerLayout.vue

Purpose:

- wrap all customer-facing device flow pages;
- present the dark, centered, narrow-shell layout from the prototype;
- inject site title, legal links, and customer-only custom CSS;
- support normal, loading, success, and error states without page chrome drift.

#### AdminLayout.vue

Purpose:

- wrap all standard admin pages;
- render sidebar + top header structure;
- provide consistent content width, spacing rhythm, and flash banner placement.

#### SettingsLayout.vue

Purpose:

- compose `AdminLayout` and add settings sub-navigation;
- be used by theme and general settings pages only.

### 11.2 Customer Pages

#### Code.vue

- renders four-digit code entry UI;
- supports default state and validation/error state;
- uses `CodeInput` component;
- must support keyboard-friendly auto-advance behavior.

#### Providers.vue

- renders available providers for a validated device code;
- supports normal and loading/submitting states;
- uses `ProviderButton` components.

#### Success.vue

- renders successful authentication completion message;
- includes any post-success explanatory copy required by prototype.

#### Error.vue

- renders friendly customer-safe error messaging;
- differentiates between failed auth and expired/invalid code by props;
- must not expose technical exception details.

### 11.3 Admin Pages

#### Login.vue

- renders social login options, email/password form, and passkey action;
- must clearly separate the three login methods;
- displays flash or inline auth errors consistently.

#### Dashboard.vue

- renders summary stats and recent authentications table;
- uses `StatCard`, `SectionHeader`, `DataTable`, and `StatusPill` where applicable.

#### Providers/Index.vue

- renders provider list or grid with enable toggles and configuration surface.

#### Clients/Index.vue

- renders clients data table;
- supports empty state and clickable rows.

#### Clients/Show.vue

- renders metadata, credentials, editable form, and danger zone;
- may include the edit experience inline instead of using a separate Blade-style edit page.

#### Clients/Create.vue

- renders create form using shared field components.

#### Settings/Theme.vue

- renders accent preset swatches, hue slider, color mode selector, site title field, and custom CSS editor.

#### Settings/General.vue

- renders general operational settings: device code expiry, terms URL, and privacy URL. Site title remains owned by `Settings/Theme.vue` to keep the settings split unambiguous.

## 12. Theme System

### 12.1 Source of Truth

Theme state is stored in the `settings` table using these keys:

- `accent_hue`
- `color_mode`
- `site_title`
- `custom_css`
- `terms_url`
- `privacy_url`

### 12.2 Shared Data

`HandleInertiaRequests` must expose theme and legal settings to every page.

Required prop contract:

```php
'auth' => [
    'user' => $user ? [
        'id' => $user->id,
        'nickname' => $user->nickname,
        'email' => $user->email,
        'is_admin' => $user->is_admin,
        'avatar_url' => $user->avatar_url,
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
    'success' => session('success'),
    'error' => session('error'),
],
```

### 12.3 `useTheme.js`

Responsibilities:

- read shared Inertia theme props;
- apply CSS custom properties to `:root`;
- handle light, dark, and system mode behavior;
- react when page props change after navigation.

### 12.4 Accent Token Rules

Primary accent token:

- `--color-primary: oklch(76% 0.16 {hue})`

Derived tokens must include at least:

- `--color-primary-hover`
- `--color-accent-dim`
- `--color-accent-text`
- `--color-glow`

Implementation must use the same hue conversion logic as Aperture's `useAccentHue.js`, adapted as needed for Borealis.

### 12.5 Accent Presets

| Name | Hue |
|---|---:|
| Pink | 350 |
| Coral | 20 |
| Tangerine | 55 |
| Lime | 135 |
| Teal | 185 |
| Sky | 230 |
| Violet | 295 |
| Magenta | 325 |

### 12.6 Custom CSS Rules

- customer pages receive custom CSS injection via a `<style>` tag when `custom_css` is non-empty;
- admin pages do not apply custom CSS from settings;
- Dispatch neutral/system tokens remain the base layer for both surfaces.

## 13. Component Library

### 13.1 Shared UI Components to Copy and Adapt

These components are copied from Aperture and adapted to Borealis:

1. `SectionHeader` — section heading with optional actions slot
2. `StatCard` — label, value, optional subtitle, optional dot status colour
3. `DataTable` — header/body slots, row click support, sort affordances
4. `StatusPill` — semantic status pill with icon + label
5. `FormField` — label wrapper with hint, error, and required/optional indicators
6. `MetadataStrip` — horizontal metadata rows/items with separators
7. `EmptyState` — icon, heading, description, optional action slot
8. `ConfirmModal` — confirm/cancel modal supporting danger treatment

Adaptation rules:

- remove Aperture-specific business logic;
- preserve component APIs where practical to keep the port predictable;
- align class usage to Borealis `dispatch.css` tokens and Tailwind utilities;
- keep components presentation-focused and reusable.

### 13.2 Borealis-Specific Components

#### Sidebar

- fixed 220px-wide admin sidebar;
- grouped navigation;
- active route styling;
- logo/brand region;
- logout action.

#### SettingsNav

- sub-navigation for settings pages;
- must expose Theme and General sections only.

#### ProviderButton

- brand-coloured social provider action button;
- icon + label;
- loading and disabled states;
- full-width on customer/mobile layouts.

#### CodeInput

- four input positions or a functionally equivalent grouped entry control;
- auto-focus and auto-advance;
- backspace support for reverse navigation;
- paste handling for valid four-digit entry;
- visible error styling.

## 14. Inertia Data and Rendering Rules

- controllers must return Inertia responses for all customer and admin pages;
- shared props must be the authoritative source for auth/theme/legal/flash state;
- page-specific props must contain only the data necessary for that page;
- forms must use standard Inertia form submission patterns;
- success and error flash messaging on admin pages must round-trip through the session and shared `flash` prop.

## 15. Files and Assets to Remove

### 15.1 Blade Views to Remove

Remove all legacy Blade templates under `resources/views/` except the Inertia root template.

Files explicitly removed:

- `resources/views/home/index.blade.php`
- `resources/views/users/login.blade.php`
- `resources/views/layouts/login.blade.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/auth/return.blade.php`
- `resources/views/auth/code.blade.php`
- `resources/views/partials/_searchtextfield.blade.php`
- `resources/views/partials/_theme.blade.php`
- `resources/views/partials/_sortheader.blade.php`
- `resources/views/partials/_providersconfig.blade.php`
- `resources/views/partials/_searchselectfield.blade.php`
- `resources/views/partials/_pagination.blade.php`
- `resources/views/clients/show.blade.php`
- `resources/views/clients/_form.blade.php`
- `resources/views/clients/edit.blade.php`
- `resources/views/clients/create.blade.php`
- `resources/views/clients/index.blade.php`
- `resources/views/clients/delete.blade.php`
- `resources/views/clients/_breadcrumbs.blade.php`

Retain:

- `resources/views/app.blade.php` as the Inertia root template.

### 15.2 Controllers to Remove or Replace

- `app/Http/Controllers/HomeController.php`
- `app/Http/Controllers/UserController.php`
- `app/Http/Controllers/ClientController.php`
- `app/Http/Controllers/OAuthController.php`
- `app/Http/Controllers/SettingController.php`
- `app/Http/Controllers/SocialProviderController.php`
- `app/Http/Controllers/ThemeController.php`
- `app/Http/Controllers/Api/V1/AuthController.php`

### 15.3 Frontend Assets to Remove or Replace

- `resources/sass/` entire directory
- `resources/js/bootstrap.js`
- old `resources/js/app.js`
- old `resources/css/app.css`

### 15.4 Models and Observers to Remove

- `app/Models/Theme.php`
- `app/Observers/ThemeObserver.php`

## 16. Error Handling Rules

### 16.1 Customer Surface

- all customer-facing errors must be friendly and non-technical;
- invalid or expired device codes must route users back into the customer error experience, not raw exceptions;
- invalid device-code access should resolve to a 404-aware flow that ultimately redirects users to code entry or dedicated error rendering as designed.

### 16.2 Admin Surface

- form submission results are communicated via flash success/error messaging;
- unauthorized non-admin access returns 403;
- login failures remain on login surface with clear but non-enumerating messages.

### 16.3 API Surface

- `/oauth2/device` and `/oauth2/token` continue to return JSON responses;
- existing HTTP status semantics remain unchanged;
- migration must not convert API failures into HTML responses.

## 17. Responsive Design Requirements

### 17.1 Customer Pages

- mobile-first;
- optimized for 375px viewport width;
- centered shell with max width of 420px;
- provider buttons and form actions full width on mobile.

### 17.2 Admin Pages

- desktop-first with full sidebar layout at approximately 1024px and above;
- mobile sidebar toggle is future enhancement and is not required in this implementation;
- initial implementation may keep admin mobile behavior minimal so long as it does not break navigation or form usage.

## 18. Testing Strategy

Testing is required. This migration is not complete without coverage for the new page/controller structure.

### 18.1 Test Types

- **Feature tests** for controller endpoints and Inertia responses
- **Unit tests** for middleware, theming composables where practical, and any pure helper logic
- **Browser tests** using Dusk or Playwright for critical end-to-end flows

### 18.2 Required Feature Test Coverage

1. customer code entry page render and validation
2. provider selection render for valid code
3. provider redirect initiation
4. provider callback success path
5. customer success and error page rendering
6. admin social login success and admin rejection path
7. admin password login success/failure paths
8. admin passkey assertion endpoints and successful sign-in flow scaffolding
9. admin dashboard access control
10. client CRUD endpoints
11. provider enable/disable update behavior
12. theme settings update behavior
13. general settings update behavior
14. `/oauth2/device` and `/oauth2/token` behavior preservation

### 18.3 Inertia Test Requirements

Use `inertiajs/inertia-laravel` testing helpers, including `assertInertia` and `assertComponent`, for page assertions.

### 18.4 Browser Flow Requirements

At minimum, automate these critical flows:

- device code entry through provider redirect initiation;
- admin login with passkey;
- one primary admin CRUD flow.

## 19. Implementation Sequence

The work should be executed in this order to reduce rework and integration risk:

1. install/remove packages and update Vite/Tailwind configuration;
2. create Inertia root template and new JS/CSS entrypoints;
3. add middleware and shared props contract;
4. apply schema and settings seed changes;
5. move/rebuild controller structure and routes while preserving backend behavior;
6. port Dispatch tokens, fonts, and shared UI primitives;
7. implement layouts;
8. implement customer pages and flow wiring;
9. implement admin auth pages and endpoints;
10. implement admin dashboard, providers, clients, and settings pages;
11. remove legacy Blade/assets/controllers/models;
12. complete feature, unit, and browser tests;
13. verify no Bootstrap/Tabler dependencies or Blade views remain in active use.

## 20. Acceptance Criteria

The migration is complete only when all of the following are true:

1. Borealis runs on Vue 3 + Inertia + Tailwind 4 with Vite.
2. No active user-facing Blade pages remain except the Inertia root template.
3. Bootstrap and Tabler packages are removed from runtime use.
4. Customer device flow behaves the same as before from the client API perspective.
5. Admin users can authenticate via supported methods subject to `is_admin` enforcement.
6. Theming values come exclusively from `settings`, not the removed `themes` table.
7. Dispatch-based components and fonts are in place and visually aligned with the prototype.
8. Shared Inertia props are available on every page as specified.
9. Legacy controllers, assets, and theme model artifacts listed above are removed.
10. Required tests pass for customer flow, admin auth, CRUD, settings, and API continuity.

## 21. Risks and Guardrails

### 21.1 Primary Risks

- accidental behavior drift in OAuth device endpoints during controller relocation;
- incomplete removal of Blade-era partials or Bootstrap/Tabler assets;
- theme token mismatch between Aperture source and Borealis adaptation;
- passkey integration mismatches if package conventions are bypassed.

### 21.2 Guardrails

- preserve API request/response contracts for existing OAuth endpoints;
- prefer direct adaptation from Aperture component/token sources rather than visual reinterpretation;
- centralize theme state in `HandleInertiaRequests` + composables;
- validate route protection with explicit admin middleware tests.

## 22. Out-of-Scope Follow-Up Opportunities

These may be implemented later, but are not required for this migration:

- responsive admin hamburger/sidebar drawer;
- richer passkey registration/profile UX;
- SSR or progressive enhancement beyond Inertia defaults;
- additional admin analytics or dashboard redesign beyond prototype parity.

## 23. Definition of Done

This spec is satisfied when the repository contains the new Inertia/Vue architecture, the legacy frontend has been removed, the backend behavior remains compatible where required, and automated tests verify the migrated customer and admin flows.
