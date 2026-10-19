<?php

use App\Livewire\Academic\TeachingJournals;
use App\Livewire\Attendance\TeacherAttendanceList;
use App\Livewire\Attendance\TeacherCheckIn;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Setting;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\TeachingJournal;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed([RoleSeeder::class, PermissionSeeder::class]);
});

function createTeacherSetup(): array
{
    $teacherUser = User::factory()->create();
    $teacherUser->assignRole('guru');

    $teacher = Teacher::factory()->create([
        'user_id' => $teacherUser->id,
        'email' => $teacherUser->email,
        'name' => 'Ustadz Ahmad Fauzi, S.Pd',
        'is_active' => true,
    ]);

    $academicYear = AcademicYear::factory()->active()->create();
    $schoolClass = SchoolClass::factory()->create(['academic_year_id' => $academicYear->id]);
    $subject = Subject::factory()->create(['code' => 'TFZ', 'name' => "Tahfidz Al-Qur'an"]);

    return [$teacherUser, $teacher, $academicYear, $schoolClass, $subject];
}

test('teacher check-in page renders successfully for teacher', function () {
    [$teacherUser, $teacher] = createTeacherSetup();
    $this->actingAs($teacherUser);

    $this->get(route('attendance.teachers.check-in'))
        ->assertOk()
        ->assertSee($teacher->name);
});

test('teacher can check in with valid GPS coordinates within radius', function () {
    [$teacherUser, $teacher] = createTeacherSetup();
    $this->actingAs($teacherUser);

    Setting::put('school_lat', '-6.200000');
    Setting::put('school_lng', '106.816666');
    Setting::put('school_radius_meters', '150');
    Setting::put('bypass_geofence', '0');

    // Koordinat sangat dekat (< 20 meter)
    $nearbyLat = -6.200010;
    $nearbyLng = 106.816670;

    Livewire::test(TeacherCheckIn::class)
        ->call('processCheckIn', $nearbyLat, $nearbyLng)
        ->assertDispatched('notify');

    $attendance = TeacherAttendance::where('teacher_id', $teacher->id)
        ->where('date', now()->toDateString())
        ->first();

    expect($attendance)->not->toBeNull()
        ->and($attendance->method)->toBe('gps')
        ->and($attendance->distance_meters)->toBeLessThan(150);
});

test('check-in is rejected when outside geofence radius', function () {
    [$teacherUser, $teacher] = createTeacherSetup();
    $this->actingAs($teacherUser);

    Setting::put('school_lat', '-6.200000');
    Setting::put('school_lng', '106.816666');
    Setting::put('school_radius_meters', '50');
    Setting::put('bypass_geofence', '0');

    // Koordinat jauh (> 5000 meter)
    $farLat = -6.250000;
    $farLng = 106.850000;

    Livewire::test(TeacherCheckIn::class)
        ->call('processCheckIn', $farLat, $farLng)
        ->assertSet('errorMessage', fn ($val) => str_contains($val, 'di luar area sekolah'));

    expect(TeacherAttendance::where('teacher_id', $teacher->id)->where('date', now()->toDateString())->exists())->toBeFalse();
});

test('teacher can check out after checking in', function () {
    [$teacherUser, $teacher] = createTeacherSetup();
    $this->actingAs($teacherUser);

    TeacherAttendance::create([
        'teacher_id' => $teacher->id,
        'date' => now()->toDateString(),
        'check_in_time' => '07:05:00',
        'status' => 'present',
        'method' => 'gps',
    ]);

    Livewire::test(TeacherCheckIn::class)
        ->call('processCheckOut', -6.200000, 106.816666)
        ->assertDispatched('notify');

    $attendance = TeacherAttendance::where('teacher_id', $teacher->id)
        ->where('date', now()->toDateString())
        ->first();

    expect($attendance->check_out_time)->not->toBeNull();
});

test('teacher attendance monitoring list renders with stats and teacher rows', function () {
    [$teacherUser, $teacher] = createTeacherSetup();
    actingAsAdmin();

    TeacherAttendance::create([
        'teacher_id' => $teacher->id,
        'date' => now()->toDateString(),
        'check_in_time' => '07:15:00',
        'status' => 'present',
        'distance_meters' => 20,
    ]);

    Livewire::test(TeacherAttendanceList::class)
        ->assertSee($teacher->name)
        ->assertSee('Hadir Tepat Waktu');
});

test('admin can record manual attendance for teacher', function () {
    [$teacherUser, $teacher] = createTeacherSetup();
    actingAsAdmin();

    Livewire::test(TeacherAttendanceList::class)
        ->set('editingTeacherId', $teacher->id)
        ->set('formStatus', 'permit')
        ->set('formNotes', 'Izin menghadiri seminar kedinasan')
        ->call('saveManual')
        ->assertDispatched('notify');

    $attendance = TeacherAttendance::where('teacher_id', $teacher->id)
        ->where('date', now()->toDateString())
        ->first();

    expect($attendance)->not->toBeNull()
        ->and($attendance->status)->toBe('permit')
        ->and($attendance->method)->toBe('manual')
        ->and($attendance->notes)->toBe('Izin menghadiri seminar kedinasan');
});

test('teacher attendance list can be exported as xlsx', function () {
    [$teacherUser, $teacher] = createTeacherSetup();
    actingAsAdmin();

    Livewire::test(TeacherAttendanceList::class)
        ->call('export', 'xlsx')
        ->assertFileDownloaded();
});

test('teaching journals list renders and displays entries', function () {
    [$teacherUser, $teacher, $academicYear, $schoolClass, $subject] = createTeacherSetup();
    actingAsAdmin();

    $journal = TeachingJournal::create([
        'academic_year_id' => $academicYear->id,
        'teacher_id' => $teacher->id,
        'class_id' => $schoolClass->id,
        'subject_id' => $subject->id,
        'date' => now()->toDateString(),
        'period_start' => 1,
        'period_end' => 2,
        'topic_taught' => 'Kaidah Tajwid Hukum Idzhar & Idgham',
    ]);

    Livewire::test(TeachingJournals::class)
        ->assertSee($journal->topic_taught)
        ->assertSee($teacher->name)
        ->assertSee($schoolClass->name);
});

test('teacher can create a new teaching journal', function () {
    [$teacherUser, $teacher, $academicYear, $schoolClass, $subject] = createTeacherSetup();
    $this->actingAs($teacherUser);

    Livewire::test(TeachingJournals::class)
        ->set('date', now()->toDateString())
        ->set('teacher_id', $teacher->id)
        ->set('class_id', $schoolClass->id)
        ->set('subject_id', $subject->id)
        ->set('period_start', 3)
        ->set('period_end', 4)
        ->set('topic_taught', 'Ziyadah Hafalan Surat An-Naba ayat 1-20')
        ->set('learning_activities', 'Talaqqi klasikal dan setor mandiri')
        ->call('save')
        ->assertDispatched('notify');

    expect(TeachingJournal::where('teacher_id', $teacher->id)->where('topic_taught', 'Ziyadah Hafalan Surat An-Naba ayat 1-20')->exists())->toBeTrue();
});

test('unauthorized user cannot save teaching journal', function () {
    [$teacherUser, $teacher, $academicYear, $schoolClass, $subject] = createTeacherSetup();
    $santriUser = User::factory()->create();
    $santriUser->assignRole('santri');
    $this->actingAs($santriUser);

    Livewire::test(TeachingJournals::class)
        ->set('date', now()->toDateString())
        ->set('teacher_id', $teacher->id)
        ->set('class_id', $schoolClass->id)
        ->set('topic_taught', 'Test')
        ->call('save')
        ->assertForbidden();
});
