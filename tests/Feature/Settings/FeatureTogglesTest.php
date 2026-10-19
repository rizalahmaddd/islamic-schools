<?php

use App\Livewire\Layout\NotificationBell;
use App\Livewire\Settings\FeatureToggles;
use App\Support\Features;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(PermissionSeeder::class);
});

test('only superadmin can open the feature settings page', function () {
    actingAsAdmin();
    $this->get(route('settings.features'))->assertForbidden();

    actingAsSuperAdmin();
    $this->get(route('settings.features'))->assertOk()->assertSee('Log Aktivitas');
});

test('superadmin turns a feature off and back on', function () {
    actingAsSuperAdmin();

    Livewire::test(FeatureToggles::class)
        ->set('state.reports.features.activity-log', false)
        ->call('save')
        ->assertRedirect(route('settings.features'));

    expect(Features::disabledKeys())->toBe(['reports.activity-log'])
        ->and(Features::enabled('reports.activity-log'))->toBeFalse()
        ->and(Features::enabled('master-data.students'))->toBeTrue();

    Livewire::test(FeatureToggles::class)
        ->assertSet('state.reports.features.activity-log', false)
        ->set('state.reports.features.activity-log', true)
        ->call('save');

    expect(Features::disabledKeys())->toBe([]);
});

test('non superadmin cannot save feature settings', function () {
    actingAsAdmin();

    Livewire::test(FeatureToggles::class)->assertForbidden();
});

test('a disabled feature is blocked even for users who hold its permission', function () {
    actingAsAdmin();
    $this->get(route('master-data.students'))->assertOk();

    Features::setDisabled(['master-data.students']);

    $this->get(route('master-data.students'))->assertForbidden();
    $this->get(route('reports.activity-log'))->assertOk();

    actingAsSuperAdmin();
    $this->get(route('master-data.students'))->assertForbidden();
});

test('disabling a module blocks every feature inside it', function () {
    Features::setDisabled(['reports']);
    actingAsAdmin();

    $this->get(route('reports.activity-log'))->assertForbidden();
    $this->get(route('dashboard'))->assertOk();

    expect(Features::enabled('reports'))->toBeFalse()
        ->and(Features::enabled('reports.activity-log'))->toBeFalse();
});

test('a module with every feature off counts as off', function () {
    $allMasterData = array_map(fn ($f) => "master-data.{$f}", array_keys(Features::MODULES['master-data']['features']));
    Features::setDisabled($allMasterData);

    expect(Features::enabled('master-data'))->toBeFalse()
        ->and(Features::enabled('reports'))->toBeTrue();
});

test('the sidebar hides a disabled feature', function () {
    actingAsAdmin();

    $this->get(route('dashboard'))->assertSee(route('reports.activity-log'));

    Features::setDisabled(['reports.activity-log']);

    $this->get(route('dashboard'))
        ->assertDontSee(route('reports.activity-log'))
        ->assertSee(route('master-data.students'));
});

test('the settings page itself cannot be switched off', function () {
    Features::setDisabled(['settings', 'settings.company-profile', 'unknown.feature']);

    expect(Features::disabledKeys())->toBe(['settings', 'settings.company-profile']);

    actingAsSuperAdmin();
    $this->get(route('settings.features'))->assertOk();
    $this->get(route('settings.company-profile'))->assertForbidden();
});

test('actions on a page that was open before the feature was disabled are rejected', function () {
    $admin = actingAsAdmin();
    $html = $this->get(route('master-data.students'))->getContent();

    preg_match_all('/wire:snapshot="([^"]+)"/', $html, $matches);
    $snapshot = collect($matches[1])
        ->map(fn (string $raw) => html_entity_decode($raw))
        ->first(fn (string $json) => json_decode($json, true)['memo']['name'] === 'master-data.students');

    $update = fn () => $this->actingAs($admin)->postJson('/livewire/update', [
        'components' => [['snapshot' => $snapshot, 'updates' => [], 'calls' => []]],
    ], ['X-Livewire' => 'true']);

    $update()->assertOk();

    Features::setDisabled(['master-data.students']);

    $update()->assertForbidden();
});

test('guests are still sent to login for a disabled feature', function () {
    Features::setDisabled(['master-data.students']);

    $this->get(route('master-data.students'))->assertRedirect('/login');
});

test('a feature link falls back to plain text or disappears when its feature is off', function () {
    actingAsSuperAdmin();
    Features::setDisabled(['master-data.students']);

    $this->blade('<x-feature-link :href="route(\'master-data.students\')" wire:navigate class="text-emerald-400 hover:underline">Daftar Santri</x-feature-link>')
        ->assertSee('Daftar Santri')
        ->assertDontSee('href', false)
        ->assertDontSee('text-emerald-400', false);

    $this->blade('<x-feature-link :href="route(\'master-data.students\')" hide-when-disabled>Buka Santri</x-feature-link>')
        ->assertDontSee('Buka Santri');

    $this->blade('<x-feature-link :href="route(\'reports.activity-log\')">Log</x-feature-link>')
        ->assertSee('href="'.route('reports.activity-log').'"', false);
});

test('notifications pointing at a disabled feature are hidden and cannot be opened', function () {
    $admin = actingAsAdmin();
    $admin->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'test',
        'data' => ['message' => 'Santri baru', 'url' => route('master-data.students', absolute: false)],
    ]);

    Livewire::test(NotificationBell::class)->assertViewHas('unreadCount', 1);

    Features::setDisabled(['master-data.students']);

    Livewire::test(NotificationBell::class)
        ->assertViewHas('unreadCount', 0)
        ->call('open', $admin->notifications()->first()->id)
        ->assertNoRedirect();
});

test('an unregistered feature key fails loudly instead of passing silently', function () {
    Features::enabled('master-data.nonexistent');
})->throws(InvalidArgumentException::class);

test('every page still renders when every other feature is switched off', function () {
    actingAsSuperAdmin();

    $allFeatures = collect(Features::MODULES)
        ->flatMap(fn (array $module, string $key) => collect(array_keys($module['features']))->map(fn (string $feature) => "{$key}.{$feature}"));

    $pages = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($route) => in_array('GET', $route->methods(), true) && $route->parameterNames() === [] && $route->getName())
        ->filter(fn ($route) => Features::featuresForRoute($route->getName()) !== []);

    foreach ($pages as $route) {
        $keep = Features::featuresForRoute($route->getName());
        Features::setDisabled($allFeatures->diff($keep)->values()->all());

        $this->get(route($route->getName()))->assertOk();
    }

    Features::setDisabled($allFeatures->all());

    $this->get(route('dashboard'))->assertOk();
    $this->get(route('settings.features'))->assertOk();
});

test('every detail page still renders with seeded data when every other feature is switched off', function () {
    $this->seed(DatabaseSeeder::class);
    actingAsSuperAdmin();

    $allFeatures = collect(Features::MODULES)
        ->flatMap(fn (array $module, string $key) => collect(array_keys($module['features']))->map(fn (string $feature) => "{$key}.{$feature}"));

    $pages = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($route) => in_array('GET', $route->methods(), true) && count($route->parameterNames()) === 1 && $route->getName())
        ->filter(fn ($route) => Features::featuresForRoute($route->getName()) !== []);

    $rendered = 0;

    foreach ($pages as $route) {
        $model = 'App\\Models\\'.Str::studly($route->parameterNames()[0]);
        $record = class_exists($model) ? $model::query()->first() : null;

        if ($record === null) {
            continue;
        }

        foreach ([[], $allFeatures->diff(Features::featuresForRoute($route->getName()))->values()->all()] as $disabled) {
            Features::setDisabled($disabled);
            $response = $this->get(route($route->getName(), $record));

            expect($response->status())->toBe(200, "{$route->getName()} with ".count($disabled).' features off');
        }

        $rendered++;
    }

    expect($rendered)->toBeGreaterThan(0);
});

test('feature toggles search filters modules and features correctly', function () {
    actingAsSuperAdmin();

    Livewire::test(FeatureToggles::class)
        ->assertSee('Siswa & Santri')
        ->assertSee('Log Aktivitas')
        ->set('search', 'Aktivitas')
        ->assertSee('Log Aktivitas')
        ->assertDontSee('Profil Lembaga')
        ->set('search', 'non-existent-feature-xyz')
        ->assertSee('Tidak Ada Fitur yang Ditemukan')
        ->call('resetFilters')
        ->assertSee('Profil Lembaga');
});

test('feature toggles status and category filters work properly', function () {
    actingAsSuperAdmin();

    Livewire::test(FeatureToggles::class)
        ->set('categoryFilter', 'data')
        ->assertSee('Siswa & Santri')
        ->assertDontSee('Log Aktivitas')
        ->set('categoryFilter', 'system')
        ->assertSee('Log Aktivitas')
        ->assertDontSee('Daftar santri/siswa, data wali murid')
        ->set('categoryFilter', 'all')
        ->set('state.reports.features.activity-log', false)
        ->set('statusFilter', 'inactive')
        ->assertSee('Log Aktivitas')
        ->assertDontSee('Profil Lembaga');
});

test('feature toggles bulk actions and reset changes operate properly', function () {
    actingAsSuperAdmin();

    $component = Livewire::test(FeatureToggles::class)
        ->call('disableModule', 'master-data')
        ->assertSet('state.master-data.on', false)
        ->call('enableModule', 'master-data')
        ->assertSet('state.master-data.on', true)
        ->call('toggleAllInModule', 'settings', false)
        ->assertSet('state.settings.features.company-profile', false)
        ->assertSet('state.settings.features.roles-and-permissions', false);

    expect($component->get('changesCount'))->toBeGreaterThan(0);

    $component->call('resetChanges');

    expect($component->get('changesCount'))->toBe(0)
        ->and($component->get('state.settings.features.company-profile'))->toBeTrue();
});

test('every app route belongs to a feature unless it is deliberately always open', function () {
    $alwaysOpen = [
        'home', 'dashboard', 'profile', 'settings.features', 'settings.backups*', 'branding.logo', 'pwa.*',
        'login', 'password.*', 'verification.*',
        '*livewire.*', 'boost.*', 'storage.*',
        // API routes name their feature through the `feature:` middleware instead (tests/Feature/Api/ApiDocumentationTest.php).
        'api.*', 'sanctum.*', 'scalar*',
    ];

    $unregistered = collect(Route::getRoutes()->getRoutes())
        ->map(fn ($route) => $route->getName())
        ->filter()
        ->reject(fn (string $name) => Str::is($alwaysOpen, $name) || Features::featuresForRoute($name) !== [])
        ->values()
        ->all();

    expect($unregistered)->toBe([], 'Daftarkan route ini di App\Support\Features::MODULES: '.implode(', ', $unregistered));
});
