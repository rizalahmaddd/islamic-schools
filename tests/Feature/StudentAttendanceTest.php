<?php

use App\Jobs\SendAttendanceWhatsAppNotification;
use App\Livewire\Attendance\StudentAttendanceReport;
use App\Livewire\Attendance\StudentAttendanceSheet;
use App\Models\AcademicYear;
use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Services\FonnteService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed([RoleSeeder::class, PermissionSeeder::class]);
});

function createAttendanceSetup(): array
{
    $academicYear = AcademicYear::factory()->active()->create();
    $schoolClass = SchoolClass::factory()->create(['academic_year_id' => $academicYear->id]);

    $students = [];
    for ($i = 0; $i < 3; $i++) {
        $guardian = Guardian::factory()->create(['phone' => '628123456780'.$i]);
        $student = Student::factory()->create([
            'guardian_id' => $guardian->id,
            'status' => 'active',
        ]);
        StudentEnrollment::factory()->create([
            'student_id' => $student->id,
            'class_id' => $schoolClass->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'active',
        ]);
        $students[] = $student;
    }

    return [$academicYear, $schoolClass, $students];
}

test('attendance sheet page renders successfully for authorized user', function () {
    [$academicYear, $schoolClass, $students] = createAttendanceSetup();
    actingAsAdmin();

    $this->get(route('attendance.students', ['classId' => $schoolClass->id]))
        ->assertOk()
        ->assertSee($schoolClass->name)
        ->assertSee($students[0]->name);
});

test('attendance sheet defaults every enrolled student to present status', function () {
    [$academicYear, $schoolClass, $students] = createAttendanceSetup();
    actingAsAdmin();

    Livewire::test(StudentAttendanceSheet::class, ['classId' => $schoolClass->id])
        ->assertSet('attendanceData.'.$students[0]->id.'.status', 'present')
        ->assertSet('attendanceData.'.$students[1]->id.'.status', 'present')
        ->assertSet('attendanceData.'.$students[2]->id.'.status', 'present');
});

test('teacher can save attendance batch and records are stored in database', function () {
    Queue::fake();
    [$academicYear, $schoolClass, $students] = createAttendanceSetup();

    $teacherUser = User::factory()->create();
    $teacherUser->assignRole('guru');
    $this->actingAs($teacherUser);

    $today = now()->format('Y-m-d');

    Livewire::test(StudentAttendanceSheet::class, ['classId' => $schoolClass->id, 'date' => $today])
        ->set('attendanceData.'.$students[0]->id.'.status', 'present')
        ->set('attendanceData.'.$students[1]->id.'.status', 'sick')
        ->set('attendanceData.'.$students[1]->id.'.notes', 'Demam tinggi')
        ->set('attendanceData.'.$students[2]->id.'.status', 'absent')
        ->call('save')
        ->assertDispatched('notify');

    expect(StudentAttendance::where('class_id', $schoolClass->id)->whereDate('date', $today)->count())->toBe(3);

    $sickRecord = StudentAttendance::where('student_id', $students[1]->id)->whereDate('date', $today)->first();
    expect($sickRecord->status)->toBe('sick')
        ->and($sickRecord->notes)->toBe('Demam tinggi');

    // Cek bahwa job WA diantrekan untuk siswa sakit dan alpa
    Queue::assertPushed(SendAttendanceWhatsAppNotification::class, 2);
});

test('saving attendance updates existing record without duplicate rows', function () {
    Queue::fake();
    [$academicYear, $schoolClass, $students] = createAttendanceSetup();
    actingAsAdmin();

    $today = now()->format('Y-m-d');

    // Presensi awal: Hadir
    StudentAttendance::create([
        'student_id' => $students[0]->id,
        'class_id' => $schoolClass->id,
        'date' => $today,
        'status' => 'present',
        'recorded_by' => auth()->id(),
    ]);

    // Update lewat Livewire jadi Sakit
    Livewire::test(StudentAttendanceSheet::class, ['classId' => $schoolClass->id, 'date' => $today])
        ->set('attendanceData.'.$students[0]->id.'.status', 'sick')
        ->set('attendanceData.'.$students[0]->id.'.notes', 'Keterangan baru')
        ->call('save');

    expect(StudentAttendance::where('student_id', $students[0]->id)->whereDate('date', $today)->count())->toBe(1)
        ->and(StudentAttendance::where('student_id', $students[0]->id)->whereDate('date', $today)->first()->status)->toBe('sick');
});

test('whatsapp notification is skipped when sendWhatsApp toggle is false', function () {
    Queue::fake();
    [$academicYear, $schoolClass, $students] = createAttendanceSetup();
    actingAsAdmin();

    $today = now()->format('Y-m-d');

    Livewire::test(StudentAttendanceSheet::class, ['classId' => $schoolClass->id, 'date' => $today])
        ->set('sendWhatsApp', false)
        ->set('attendanceData.'.$students[0]->id.'.status', 'absent')
        ->call('save');

    Queue::assertNotPushed(SendAttendanceWhatsAppNotification::class);
});

test('unauthorized user cannot save attendance', function () {
    [$academicYear, $schoolClass, $students] = createAttendanceSetup();
    $santriUser = User::factory()->create();
    $santriUser->assignRole('santri');
    $this->actingAs($santriUser);

    Livewire::test(StudentAttendanceSheet::class, ['classId' => $schoolClass->id])
        ->call('save')
        ->assertForbidden();
});

test('attendance report renders recap with calculated attendance percentage', function () {
    [$academicYear, $schoolClass, $students] = createAttendanceSetup();
    actingAsAdmin();

    $currentMonth = (int) now()->format('m');
    $currentYear = (int) now()->format('Y');

    // Buat data absensi: Student 0 hadir 2x, Student 1 hadir 1x sakit 1x
    StudentAttendance::create([
        'student_id' => $students[0]->id,
        'class_id' => $schoolClass->id,
        'date' => now()->startOfMonth()->toDateString(),
        'status' => 'present',
    ]);
    StudentAttendance::create([
        'student_id' => $students[0]->id,
        'class_id' => $schoolClass->id,
        'date' => now()->startOfMonth()->addDay()->toDateString(),
        'status' => 'present',
    ]);
    StudentAttendance::create([
        'student_id' => $students[1]->id,
        'class_id' => $schoolClass->id,
        'date' => now()->startOfMonth()->toDateString(),
        'status' => 'present',
    ]);
    StudentAttendance::create([
        'student_id' => $students[1]->id,
        'class_id' => $schoolClass->id,
        'date' => now()->startOfMonth()->addDay()->toDateString(),
        'status' => 'sick',
    ]);

    Livewire::test(StudentAttendanceReport::class, ['classId' => $schoolClass->id, 'month' => $currentMonth, 'year' => $currentYear])
        ->assertSee($students[0]->name)
        ->assertSee('100%') // Student 0: 2/2 = 100%
        ->assertSee('50%');  // Student 1: 1/2 = 50%
});

test('attendance report can be exported as xlsx', function () {
    [$academicYear, $schoolClass, $students] = createAttendanceSetup();
    actingAsAdmin();

    Livewire::test(StudentAttendanceReport::class, ['classId' => $schoolClass->id])
        ->call('export', 'xlsx')
        ->assertFileDownloaded();
});

test('attendance report print view renders letterhead and class details', function () {
    [$academicYear, $schoolClass, $students] = createAttendanceSetup();
    actingAsAdmin();

    $this->get(route('attendance.reports.print', ['schoolClass' => $schoolClass->id]))
        ->assertOk()
        ->assertSee('REKAPITULASI PRESENSI SANTRI')
        ->assertSee($schoolClass->name);
});

test('whatsapp notification job sends message via fonnte service', function () {
    [$academicYear, $schoolClass, $students] = createAttendanceSetup();
    $student = $students[0];

    $mockFonnte = Mockery::mock(FonnteService::class);
    $mockFonnte->shouldReceive('send')
        ->once()
        ->withArgs(function ($phone, $message) use ($student) {
            return str_contains($phone, '628') && str_contains($message, $student->name);
        })
        ->andReturn(['status' => true, 'message' => 'Terkirim']);

    $job = new SendAttendanceWhatsAppNotification(
        $student->id,
        $schoolClass->id,
        now()->toDateString(),
        'sick',
        'Demam tinggi'
    );

    $job->handle($mockFonnte);
});

test('saving the same attendance twice only notifies guardians once', function () {
    Queue::fake();
    [$academicYear, $schoolClass, $students] = createAttendanceSetup();
    actingAsAdmin();

    $sheet = Livewire::test(StudentAttendanceSheet::class, ['classId' => $schoolClass->id, 'date' => now()->format('Y-m-d')])
        ->set('attendanceData.'.$students[1]->id.'.status', 'sick')
        ->call('save')
        ->call('save');

    Queue::assertPushed(SendAttendanceWhatsAppNotification::class, 1);

    $sheet->set('attendanceData.'.$students[1]->id.'.status', 'absent')->call('save');

    Queue::assertPushed(SendAttendanceWhatsAppNotification::class, 2);
});

test('attendance cannot be saved for a future date', function () {
    [$academicYear, $schoolClass, $students] = createAttendanceSetup();
    actingAsAdmin();

    Livewire::test(StudentAttendanceSheet::class, ['classId' => $schoolClass->id, 'date' => now()->addDay()->format('Y-m-d')])
        ->call('save')
        ->assertHasErrors(['date']);

    expect(StudentAttendance::count())->toBe(0);
});
