<?php

use App\Livewire\Academic\GradeSheet;
use App\Livewire\Academic\ReportCardIndex;
use App\Livewire\Academic\SubjectAssignments;
use App\Models\AcademicYear;
use App\Models\QuranSurah;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentConductRecord;
use App\Models\StudentEnrollment;
use App\Models\StudentGrade;
use App\Models\Subject;
use App\Models\SubjectAssignment;
use App\Models\TahfidzSession;
use App\Models\Teacher;
use App\Models\User;
use App\Services\AcademicGradingService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\QuranSurahSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(PermissionSeeder::class);
    $this->seed(QuranSurahSeeder::class);
});

test('authorized users can view academic pages while unauthorized are forbidden', function () {
    $guru = User::factory()->create();
    $guru->assignRole('guru');

    $santri = User::factory()->create();
    $santri->assignRole('santri');

    // Guru can view academic pages
    $this->actingAs($guru)->get(route('academic.teaching-journals'))->assertOk();
    $this->actingAs($guru)->get(route('academic.subject-assignments'))->assertOk();
    $this->actingAs($guru)->get(route('academic.grade-sheet'))->assertOk();
    $this->actingAs($guru)->get(route('academic.report-cards'))->assertOk();

    // Santri cannot view teacher/academic management pages
    $this->actingAs($santri)->get(route('academic.teaching-journals'))->assertForbidden();
    $this->actingAs($santri)->get(route('academic.subject-assignments'))->assertForbidden();
    $this->actingAs($santri)->get(route('academic.grade-sheet'))->assertForbidden();
    $this->actingAs($santri)->get(route('academic.report-cards'))->assertForbidden();
});

test('academic grading service calculates final score, predicates, and descriptions correctly', function () {
    /** @var AcademicGradingService $service */
    $service = app(AcademicGradingService::class);

    // Final score formula: 50% formative + 25% mid + 25% final
    $finalScore = $service->calculateFinalScore(90.0, 80.0, 80.0);
    expect($finalScore)->toBe(85.00);

    // Predicate checks with KKM = 75
    expect($service->determinePredicate(95.0, 75.0))->toBe('A');
    expect($service->determinePredicate(85.0, 75.0))->toBe('B');
    expect($service->determinePredicate(75.0, 75.0))->toBe('C');
    expect($service->determinePredicate(68.0, 75.0))->toBe('D');

    // Competence descriptions
    $descA = $service->generateCompetenceDescriptions('Fiqih', 'A');
    expect($descA['achievement'])->toContain('Fiqih');
    expect($descA['achievement'])->toContain('sangat istimewa');

    $descD = $service->generateCompetenceDescriptions('Bahasa Arab', 'D');
    expect($descD['achievement'])->toContain('membutuhkan pendampingan');
});

test('can manage subject assignments via Livewire', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $year = AcademicYear::factory()->create(['is_active' => true]);
    $class = SchoolClass::factory()->create(['academic_year_id' => $year->id]);
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create(['is_active' => true]);

    Livewire::actingAs($admin)
        ->test(SubjectAssignments::class)
        ->call('openCreateModal')
        ->set('academic_year_id', $year->id)
        ->set('class_id', $class->id)
        ->set('subject_id', $subject->id)
        ->set('teacher_id', $teacher->id)
        ->set('passing_grade', 80.00)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('subject_assignments', [
        'academic_year_id' => $year->id,
        'class_id' => $class->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'passing_grade' => 80.00,
    ]);
});

test('teachers can input student grades and auto generate descriptions in grade sheet', function () {
    $guru = User::factory()->create();
    $guru->assignRole('guru');

    $teacher = Teacher::factory()->create(['user_id' => $guru->id, 'is_active' => true]);
    $year = AcademicYear::factory()->create(['is_active' => true]);
    $class = SchoolClass::factory()->create(['academic_year_id' => $year->id]);
    $subject = Subject::factory()->create(['name' => 'Aqidah Akhlak']);

    $student = Student::factory()->create(['status' => 'active']);
    StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'class_id' => $class->id,
        'academic_year_id' => $year->id,
        'status' => 'active',
    ]);

    $assignment = SubjectAssignment::create([
        'academic_year_id' => $year->id,
        'class_id' => $class->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'passing_grade' => 75.00,
    ]);

    Livewire::actingAs($guru)
        ->test(GradeSheet::class, ['assignment_id' => $assignment->id])
        ->set("rows.{$student->id}.formative_average", '90')
        ->set("rows.{$student->id}.mid_term_score", '90')
        ->set("rows.{$student->id}.final_term_score", '90')
        ->call('recalculateRow', $student->id)
        ->call('generateAllDescriptions')
        ->call('saveAll');

    $this->assertDatabaseHas('student_grades', [
        'subject_assignment_id' => $assignment->id,
        'student_id' => $student->id,
        'final_score' => 90.00,
        'predicate' => 'A',
    ]);
});

test('homeroom teacher can update report card notes and print official report card with tahfidz annex', function () {
    $waliKelasUser = User::factory()->create();
    $waliKelasUser->assignRole('wali_kelas');

    $teacher = Teacher::factory()->create(['user_id' => $waliKelasUser->id]);
    $year = AcademicYear::factory()->create([
        'is_active' => true,
        'name' => '2026/2027',
        'semester' => 'ganjil',
        'start_date' => now()->startOfYear()->toDateString(),
        'end_date' => now()->endOfYear()->toDateString(),
    ]);
    $class = SchoolClass::factory()->create(['academic_year_id' => $year->id, 'homeroom_teacher_id' => $teacher->id, 'name' => '7A Tahfidz']);
    $student = Student::factory()->create(['status' => 'active', 'name' => 'Fulan bin Fulan', 'nis' => '99887766']);

    StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'class_id' => $class->id,
        'academic_year_id' => $year->id,
        'status' => 'active',
    ]);

    // Presensi
    StudentAttendance::create([
        'student_id' => $student->id,
        'class_id' => $class->id,
        'date' => now()->toDateString(),
        'status' => 'sick',
        'recorded_by' => $waliKelasUser->id,
    ]);

    // Tahfidz setoran
    $surah = QuranSurah::first();
    TahfidzSession::create([
        'student_id' => $student->id,
        'teacher_id' => $teacher->id,
        'session_type' => 'ziyadah',
        'surah_id' => $surah->id,
        'verse_start' => 1,
        'verse_end' => 7,
        'juz' => 1,
        'grade' => 'A',
        'recorded_at' => now(),
    ]);

    // Poin adab
    StudentConductRecord::create([
        'student_id' => $student->id,
        'date' => now()->toDateString(),
        'type' => 'merit',
        'points' => 10,
        'category' => 'Akhlak Terpuji',
        'description' => 'Membantu menata masjid',
        'recorded_by' => $waliKelasUser->id,
    ]);

    // Subject assignment & grade
    $subject = Subject::factory()->create(['name' => 'Tahfidz Al-Quran']);
    $assignment = SubjectAssignment::create([
        'academic_year_id' => $year->id,
        'class_id' => $class->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'passing_grade' => 75.00,
    ]);

    StudentGrade::create([
        'subject_assignment_id' => $assignment->id,
        'student_id' => $student->id,
        'formative_average' => 95.00,
        'mid_term_score' => 92.00,
        'final_term_score' => 94.00,
        'final_score' => 94.00,
        'predicate' => 'A',
        'achievement_desc' => 'Sangat menguasai tajwid dan hafalan.',
    ]);

    // Test ReportCardIndex Livewire
    Livewire::actingAs($waliKelasUser)
        ->test(ReportCardIndex::class)
        ->set('classFilter', $class->id)
        ->set('academicYearFilter', $year->id)
        ->call('editNotes', $student->id)
        ->set('extracurricular_notes', 'Pramuka dan Muhadhoroh')
        ->set('homeroom_notes', 'Pertahankan prestasi belajar.')
        ->set('promotion_status', 'promoted')
        ->call('saveNotes');

    $this->assertDatabaseHas('report_card_summaries', [
        'student_id' => $student->id,
        'class_id' => $class->id,
        'academic_year_id' => $year->id,
        'sick_count' => 1,
        'promotion_status' => 'promoted',
    ]);

    // Test Print E-Rapor route
    $response = $this->actingAs($waliKelasUser)->get(route('academic.report-cards.print', [
        'student' => $student->id,
        'academic_year_id' => $year->id,
    ]));

    $response->assertOk();
    $response->assertSee('LAPORAN HASIL BELAJAR (E-RAPOR)');
    $response->assertSee('Fulan bin Fulan');
    $response->assertSee('99887766');
    $response->assertSee('Tahfidz Al-Quran');
    $response->assertSee('Sangat menguasai tajwid');
    $response->assertSee('Capaian Kepesantrenan');
    $response->assertSee('Verifikasi Dokumen Digital');
});

test('grade sheet refuses scores outside 0 to 100', function () {
    $guru = User::factory()->create();
    $guru->assignRole('guru');
    $teacher = Teacher::factory()->create(['user_id' => $guru->id, 'is_active' => true]);
    $year = AcademicYear::factory()->create(['is_active' => true]);
    $class = SchoolClass::factory()->create(['academic_year_id' => $year->id]);
    $student = Student::factory()->create(['status' => 'active']);
    StudentEnrollment::factory()->create(['student_id' => $student->id, 'class_id' => $class->id, 'academic_year_id' => $year->id, 'status' => 'active']);
    $assignment = SubjectAssignment::create([
        'academic_year_id' => $year->id,
        'class_id' => $class->id,
        'subject_id' => Subject::factory()->create()->id,
        'teacher_id' => $teacher->id,
        'passing_grade' => 75.00,
    ]);

    Livewire::actingAs($guru)
        ->test(GradeSheet::class, ['assignment_id' => $assignment->id])
        ->set("rows.{$student->id}.mid_term_score", '150')
        ->set("rows.{$student->id}.final_term_score", '-5')
        ->call('saveAll')
        ->assertHasErrors(["rows.{$student->id}.mid_term_score", "rows.{$student->id}.final_term_score"]);

    expect(StudentGrade::count())->toBe(0);
});
