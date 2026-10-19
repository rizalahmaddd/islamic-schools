<?php

use App\Models\Student;
use App\Models\User;
use App\Support\Features;
use Livewire\Volt\Volt;

/**
 * @return list<array{label: string, sub: ?string, url: string, flags: list<array{label: string, color: string}>}>
 */
function searchGroup(array $results, string $group): array
{
    return collect($results)->firstWhere('group', $group)['items'] ?? [];
}

test('matching part of a result label is bolded and the rest stays escaped', function () {
    actingAsAdmin();
    Student::factory()->create(['name' => 'Ahmad Sinar <Bintang>']);

    Volt::test('layout.global-search')
        ->call('openSearch')
        ->set('query', 'sina')
        ->assertSeeHtml('Ahmad <mark class="bg-transparent font-semibold text-slate-50">Sina</mark>r &lt;Bintang&gt;');
});

test('a student is found by name or nis and opens its detail page', function () {
    actingAsRole('admin');
    $student = Student::factory()->create(['name' => 'Fadhil Rahman', 'nis' => '99990001', 'status' => 'inactive']);

    $byName = searchGroup(Volt::test('layout.global-search')->set('query', 'fadhil')->get('results'), 'Siswa / Santri');
    $byNis = searchGroup(Volt::test('layout.global-search')->set('query', '99990001')->get('results'), 'Siswa / Santri');

    expect($byName[0]['url'])->toBe(route('master-data.students.show', $student))
        ->and($byName[0]['flags'])->toBe([['label' => 'Nonaktif', 'color' => 'slate']])
        ->and($byNis[0]['label'])->toBe('Fadhil Rahman');
});

test('students are hidden from the search when their feature is off', function () {
    actingAsAdmin();
    Student::factory()->create(['name' => 'Santri Tersembunyi']);
    Features::setDisabled(['master-data.students']);

    expect(searchGroup(Volt::test('layout.global-search')->set('query', 'tersembunyi')->get('results'), 'Siswa / Santri'))->toBe([]);
});

test('users are only searchable by roles that manage user roles', function () {
    User::factory()->create(['name' => 'Rahmat Hidayat', 'username' => 'rahmat']);

    actingAsAdmin();
    expect(searchGroup(Volt::test('layout.global-search')->set('query', 'rahmat')->get('results'), 'Pengguna'))->toHaveCount(1);

    actingAsRole('staff');
    expect(searchGroup(Volt::test('layout.global-search')->set('query', 'rahmat')->get('results'), 'Pengguna'))->toBe([]);
});

test('menus are searchable by partial words across group and label', function () {
    actingAsAdmin();

    $results = Volt::test('layout.global-search')->set('query', 'lap aktiv')->get('results');

    expect(collect(searchGroup($results, 'Menu'))->pluck('url')->all())->toBe([route('reports.activity-log')]);
});

test('menus the user cannot access are left out of the search', function () {
    actingAsRole('staff');

    $results = Volt::test('layout.global-search')->set('query', 'pengaturan')->get('results');

    expect(searchGroup($results, 'Menu'))->toBe([]);
});
