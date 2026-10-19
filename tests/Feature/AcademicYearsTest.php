<?php

use App\Livewire\MasterData\AcademicYears;
use App\Models\AcademicYear;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(PermissionSeeder::class);
});

test('unauthorized users cannot view academic years', function () {
    actingAsRole('santri');

    $this->get(route('master-data.academic-years'))->assertForbidden();
});

test('authorized users can view academic years page', function () {
    actingAsAdmin();

    $this->get(route('master-data.academic-years'))->assertOk();
});

test('admin can create and activate academic year', function () {
    actingAsAdmin();

    Livewire::test(AcademicYears::class)
        ->set('name', '2027/2028')
        ->set('semester', 'ganjil')
        ->set('start_date', '2027-07-15')
        ->set('end_date', '2027-12-20')
        ->set('is_active', true)
        ->call('save')
        ->assertHasNoErrors();

    $year = AcademicYear::where('name', '2027/2028')->first();
    expect($year)->not->toBeNull()
        ->and($year->is_active)->toBeTrue();
});

test('activating an academic year deactivates others', function () {
    actingAsAdmin();

    $year1 = AcademicYear::factory()->active()->create(['name' => '2025/2026']);
    $year2 = AcademicYear::factory()->create(['name' => '2026/2027', 'is_active' => false]);

    Livewire::test(AcademicYears::class)
        ->call('activateYear', $year2->id);

    expect($year2->fresh()->is_active)->toBeTrue()
        ->and($year1->fresh()->is_active)->toBeFalse();
});

test('admin can export academic years', function () {
    actingAsAdmin();
    AcademicYear::factory()->count(3)->create();

    $response = Livewire::test(AcademicYears::class)
        ->call('export', 'xlsx');

    expect($response)->not->toBeNull();
});

test('the active academic year cannot be deleted but an unused inactive one can', function () {
    actingAsAdmin();
    $active = AcademicYear::factory()->create(['is_active' => true]);
    $unused = AcademicYear::factory()->create(['is_active' => false]);

    Livewire::test(AcademicYears::class)
        ->call('confirmDelete', $active->id)
        ->call('delete');

    expect(AcademicYear::find($active->id))->not->toBeNull();

    Livewire::test(AcademicYears::class)
        ->call('confirmDelete', $unused->id)
        ->call('delete');

    expect(AcademicYear::find($unused->id))->toBeNull();
});
