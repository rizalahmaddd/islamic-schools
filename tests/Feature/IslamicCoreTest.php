<?php

use App\Livewire\Islamic\ConductRecords;
use App\Livewire\Islamic\MutabaahWorship;
use App\Livewire\Islamic\TahfidzSheet;
use App\Models\AcademicYear;
use App\Models\Guardian;
use App\Models\QuranSurah;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentConductRecord;
use App\Models\StudentDailyWorship;
use App\Models\StudentEnrollment;
use App\Models\TahfidzSession;
use App\Models\Teacher;
use App\Models\User;
use App\Services\TahfidzService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\QuranSurahSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(PermissionSeeder::class);
    $this->seed(QuranSurahSeeder::class);
});

test('authorized users can access islamic tahfidz, mutabaah, and conduct modules while unauthorized are forbidden', function () {
    $guru = User::factory()->create();
    $guru->assignRole('guru');

    $staff = User::factory()->create();
    $staff->assignRole('staff');

    // Guru has islamic.tahfidz.view & islamic.tahfidz.manage
    $this->actingAs($guru)->get(route('islamic.tahfidz'))->assertOk();
    $this->actingAs($guru)->get(route('islamic.mutabaah'))->assertOk();

    // Staff does not have islamic permissions
    $this->actingAs($staff)->get(route('islamic.tahfidz'))->assertForbidden();
    $this->actingAs($staff)->get(route('islamic.mutabaah'))->assertForbidden();
    $this->actingAs($staff)->get(route('islamic.conduct'))->assertForbidden();
});

test('tahfidz service calculates 30-juz progress accurately', function () {
    $student = Student::factory()->create(['status' => 'active']);
    $teacher = Teacher::factory()->create(['is_active' => true]);
    $surahNaba = QuranSurah::where('number', 78)->firstOrFail();

    TahfidzSession::create([
        'student_id' => $student->id,
        'teacher_id' => $teacher->id,
        'session_type' => 'ziyadah',
        'surah_id' => $surahNaba->id,
        'verse_start' => 1,
        'verse_end' => 40,
        'juz' => 30,
        'grade' => 'A',
        'tajwid_notes' => 'Mumtaz makhraj dan mad thobii',
        'recorded_at' => now(),
    ]);

    $service = app(TahfidzService::class);
    $progress = $service->calculateStudentProgress($student);

    expect($progress['completed_juz_count'])->toBe(1)
        ->and($progress['juz_progress'][30]['status'])->toBe('completed')
        ->and($progress['juz_progress'][1]['status'])->toBe('empty')
        ->and($progress['total_sessions_count'])->toBe(1);
});

test('tahfidz sheet records session and dispatches whatsapp notification to guardian', function () {
    Http::fake([
        '*fonnte.com*' => Http::response(['status' => true, 'id' => 'wa-msg-789'], 200),
    ]);

    $teacherUser = User::factory()->create();
    $teacherUser->assignRole('guru');
    $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);

    $guardian = Guardian::factory()->create(['phone' => '6281234567890']);
    $student = Student::factory()->create([
        'guardian_id' => $guardian->id,
        'name' => 'Usamah Bin Zaid',
        'status' => 'active',
    ]);

    $academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $class = SchoolClass::factory()->create(['academic_year_id' => $academicYear->id]);

    StudentEnrollment::create([
        'student_id' => $student->id,
        'academic_year_id' => $academicYear->id,
        'class_id' => $class->id,
        'enrolled_at' => now()->toDateString(),
        'status' => 'active',
    ]);

    $surahIkhlas = QuranSurah::where('number', 112)->firstOrFail();

    Livewire::actingAs($teacherUser)
        ->test(TahfidzSheet::class)
        ->set('selectedClassId', $class->id)
        ->set('selectedStudentId', $student->id)
        ->set('session_type', 'ziyadah')
        ->set('surah_id', $surahIkhlas->id)
        ->set('verse_start', 1)
        ->set('verse_end', 4)
        ->set('juz', 30)
        ->set('grade', 'A')
        ->set('tajwid_notes', 'Makhraj huruf qaf dan qalqalah kubro sangat baik')
        ->set('send_wa', true)
        ->call('saveSession')
        ->assertDispatched('notify');

    $session = TahfidzSession::where('student_id', $student->id)->first();
    expect($session)->not->toBeNull()
        ->and($session->surah_id)->toBe($surahIkhlas->id)
        ->and($session->verse_end)->toBe(4)
        ->and($session->grade)->toBe('A');

    Http::assertSent(function ($request) use ($guardian) {
        return str_contains($request->url(), 'api.fonnte.com/send')
            && $request['target'] === $guardian->phone
            && str_contains($request['message'], 'Usamah Bin Zaid')
            && str_contains($request['message'], 'Al-Ikhlas');
    });
});

test('mutabaah worship records and updates daily prayers, sunnah, and tilawah', function () {
    $teacherUser = User::factory()->create();
    $teacherUser->assignRole('guru');

    $academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $class = SchoolClass::factory()->create(['academic_year_id' => $academicYear->id]);
    $student = Student::factory()->create(['status' => 'active']);

    StudentEnrollment::create([
        'student_id' => $student->id,
        'academic_year_id' => $academicYear->id,
        'class_id' => $class->id,
        'enrolled_at' => now()->toDateString(),
        'status' => 'active',
    ]);

    Livewire::actingAs($teacherUser)
        ->test(MutabaahWorship::class)
        ->set('selectedClassId', $class->id)
        ->call('markAllJamaah')
        ->assertDispatched('notify');

    $worship = StudentDailyWorship::where('student_id', $student->id)->where('date', now()->toDateString())->first();
    expect($worship)->not->toBeNull()
        ->and($worship->fajr)->toBe('jamaah')
        ->and($worship->isha)->toBe('jamaah');

    // Update prayer status
    Livewire::actingAs($teacherUser)
        ->test(MutabaahWorship::class)
        ->set('selectedClassId', $class->id)
        ->call('updatePrayerStatus', $student->id, 'fajr', 'munfarid')
        ->call('toggleSunnah', $student->id, 'dhuha')
        ->call('updateTilawah', $student->id, 5);

    $worship->refresh();
    expect($worship->fajr)->toBe('munfarid')
        ->and($worship->dhuha)->toBeTrue()
        ->and($worship->tilawah_pages)->toBe(5);
});

test('conduct records tracks violations and merits, and dispatches notification to guardian', function () {
    Http::fake([
        '*fonnte.com*' => Http::response(['status' => true, 'id' => 'wa-msg-conduct'], 200),
    ]);

    $waliKelas = User::factory()->create();
    $waliKelas->assignRole('wali_kelas');

    $guardian = Guardian::factory()->create(['phone' => '6281987654321']);
    $student = Student::factory()->create([
        'guardian_id' => $guardian->id,
        'name' => 'Ali Imran',
        'status' => 'active',
    ]);

    Livewire::actingAs($waliKelas)
        ->test(ConductRecords::class)
        ->call('openCreateModal')
        ->set('student_id', $student->id)
        ->set('date', now()->toDateString())
        ->set('type', 'violation')
        ->set('points', 25)
        ->set('category', 'Kedisiplinan & Waktu')
        ->set('description', 'Terlambat mengikuti halaqah tahfidz 20 menit')
        ->set('action_taken', 'Nasihat lisan dan menulis istighfar')
        ->set('send_wa', true)
        ->call('save')
        ->assertDispatched('notify');

    $record = StudentConductRecord::where('student_id', $student->id)->first();
    expect($record)->not->toBeNull()
        ->and($record->points)->toBe(25)
        ->and($record->type)->toBe('violation');

    expect($student->totalViolationPoints())->toBe(25);

    Http::assertSent(function ($request) use ($guardian) {
        return str_contains($request->url(), 'api.fonnte.com/send')
            && $request['target'] === $guardian->phone
            && str_contains($request['message'], 'Ali Imran')
            && str_contains($request['message'], 'PERINGATAN AWAL PEMBINAAN');
    });
});

test('tahfidz verse range cannot exceed the number of verses in the surah', function () {
    $this->seed(QuranSurahSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $student = Student::factory()->create(['status' => 'active']);
    $ikhlas = QuranSurah::where('number', 112)->firstOrFail();

    Livewire::actingAs($admin)
        ->test(TahfidzSheet::class)
        ->set('selectedStudentId', $student->id)
        ->set('session_type', 'ziyadah')
        ->set('surah_id', $ikhlas->id)
        ->set('verse_start', 1)
        ->set('verse_end', 99)
        ->set('juz', 30)
        ->set('grade', 'A')
        ->call('saveSession')
        ->assertHasErrors(['verse_end']);

    expect(TahfidzSession::count())->toBe(0);
});
