<?php

use App\Livewire\MasterData\SchoolClasses;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Teacher;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(PermissionSeeder::class);
});

test('unauthorized users cannot view school classes', function () {
    actingAsRole('santri');

    $this->get(route('master-data.classes'))->assertForbidden();
});

test('authorized users can view school classes page', function () {
    actingAsAdmin();

    $this->get(route('master-data.classes'))->assertOk();
});

test('admin can create a school class linked to academic year and teacher', function () {
    actingAsAdmin();
    $year = AcademicYear::factory()->create();
    $teacher = Teacher::factory()->create();

    Livewire::test(SchoolClasses::class)
        ->set('academic_year_id', $year->id)
        ->set('name', '7A Unggulan')
        ->set('grade_level', 7)
        ->set('homeroom_teacher_id', $teacher->id)
        ->set('capacity', 32)
        ->call('save')
        ->assertHasNoErrors();

    $class = SchoolClass::where('name', '7A Unggulan')->first();
    expect($class)->not->toBeNull()
        ->and($class->academic_year_id)->toBe($year->id)
        ->and($class->homeroom_teacher_id)->toBe($teacher->id)
        ->and($class->grade_level)->toBe(7);
});

test('admin can filter classes by academic year', function () {
    actingAsAdmin();
    $year1 = AcademicYear::factory()->create();
    $year2 = AcademicYear::factory()->create();

    $class1 = SchoolClass::factory()->create(['academic_year_id' => $year1->id, 'name' => 'Kelas Khusus 1']);
    $class2 = SchoolClass::factory()->create(['academic_year_id' => $year2->id, 'name' => 'Kelas Khusus 2']);

    Livewire::test(SchoolClasses::class)
        ->set('academicYearFilter', (string) $year1->id)
        ->assertSee('Kelas Khusus 1')
        ->assertDontSee('Kelas Khusus 2');
});
