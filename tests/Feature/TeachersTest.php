<?php

use App\Livewire\MasterData\Teachers;
use App\Models\Teacher;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(PermissionSeeder::class);
});

test('unauthorized users cannot view teachers', function () {
    actingAsRole('santri');

    $this->get(route('master-data.teachers'))->assertForbidden();
});

test('authorized users can view teachers page', function () {
    actingAsAdmin();

    $this->get(route('master-data.teachers'))->assertOk();
});

test('admin can create a new teacher with phone sanitization', function () {
    actingAsAdmin();

    Livewire::test(Teachers::class)
        ->set('name', 'Ustadz Salman Al-Farisi, Lc.')
        ->set('gender', 'L')
        ->set('nip', '199501012020011001')
        ->set('phone', '081234567899')
        ->set('specialization', 'Tahfidz Al-Qur\'an')
        ->call('save')
        ->assertHasNoErrors();

    $teacher = Teacher::where('nip', '199501012020011001')->first();
    expect($teacher)->not->toBeNull()
        ->and($teacher->name)->toBe('Ustadz Salman Al-Farisi, Lc.')
        ->and($teacher->phone)->toBe('6281234567899');
});

test('teacher requires valid phone and name', function () {
    actingAsAdmin();

    Livewire::test(Teachers::class)
        ->set('name', '')
        ->set('phone', '')
        ->call('save')
        ->assertHasErrors(['name', 'phone']);
});

test('admin can edit teacher data', function () {
    actingAsAdmin();
    $teacher = Teacher::factory()->create(['name' => 'Nama Lama']);

    Livewire::test(Teachers::class)
        ->call('openEditModal', $teacher->id)
        ->set('name', 'Nama Baru Ustadz')
        ->call('save')
        ->assertHasNoErrors();

    expect($teacher->fresh()->name)->toBe('Nama Baru Ustadz');
});
