<?php

use App\Livewire\Reports\ReportsCenter;
use App\Models\AcademicYear;
use App\Models\AdmissionBatch;
use App\Models\PaymentTransaction;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\TahfidzSession;
use Livewire\Livewire;

test('guests cannot view reports center', function () {
    $this->get(route('reports.center'))->assertRedirect('/login');
});

test('admin can access reports center and see executive summary', function () {
    actingAsAdmin();

    $academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $class = SchoolClass::factory()->create(['academic_year_id' => $academicYear->id]);
    $student = Student::factory()->create(['status' => 'active']);

    PaymentTransaction::factory()->create([
        'student_id' => $student->id,
        'total_amount' => 500000,
        'paid_at' => now(),
    ]);

    StudentAttendance::factory()->create([
        'student_id' => $student->id,
        'class_id' => $class->id,
        'date' => now()->toDateString(),
        'status' => 'present',
    ]);

    Livewire::test(ReportsCenter::class)
        ->assertOk()
        ->assertSee('Pusat Laporan')
        ->assertSee('Ringkasan Eksekutif')
        ->assertSee('500.000');
});

test('switching tabs displays specific domain metrics', function () {
    actingAsAdmin();

    $academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $class = SchoolClass::factory()->create(['academic_year_id' => $academicYear->id]);
    $student = Student::factory()->create(['status' => 'active']);

    TahfidzSession::factory()->create([
        'student_id' => $student->id,
        'recorded_at' => now(),
        'session_type' => 'ziyadah',
    ]);

    AdmissionBatch::factory()->create(['name' => 'Gelombang 1 Reguler']);

    Livewire::test(ReportsCenter::class)
        ->set('activeTab', 'tahfidz')
        ->assertSee('Total Sesi Setoran Quran')
        ->assertSee($student->name)
        ->set('activeTab', 'admissions')
        ->assertSee('Gelombang 1 Reguler')
        ->set('activeTab', 'finance')
        ->assertSee('Total Kas Masuk');
});

test('exportCsv returns downloadable CSV stream', function () {
    actingAsAdmin();

    Livewire::test(ReportsCenter::class)
        ->call('exportCsv')
        ->assertFileDownloaded();
});
