<?php

use App\Livewire\Admissions\AdmissionBatches;
use App\Livewire\Admissions\CandidateIndex;
use App\Livewire\Admissions\PublicRegistration;
use App\Models\AcademicYear;
use App\Models\AdmissionBatch;
use App\Models\AdmissionCandidate;
use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Services\AdmissionConversionService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(PermissionSeeder::class);

    Http::fake();
});

test('public registration allows prospective parents to register online without authentication', function () {
    $academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $batch = AdmissionBatch::factory()->create([
        'academic_year_id' => $academicYear->id,
        'name' => 'Gelombang 1 Reguler',
        'is_active' => true,
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
        'registration_fee' => 300000,
    ]);

    Livewire::test(PublicRegistration::class)
        ->assertSee('Pendaftaran Santri Baru (PPDB)')
        ->assertSee('Gelombang 1 Reguler')
        ->set('admission_batch_id', $batch->id)
        ->set('student_name', 'Ahmad Farhan')
        ->set('gender', 'male')
        ->set('nisn', '0098765432')
        ->set('guardian_name', 'Bpk. Hendra Gunawan')
        ->set('guardian_relationship', 'Ayah')
        ->set('guardian_phone', '081299887766')
        ->set('guardian_email', 'hendra@example.com')
        ->set('address', 'Jl. Merdeka No. 45, Bandung')
        ->call('register')
        ->assertSet('isSuccess', true)
        ->assertSee('Alhamdulillah, Data Telah Kami Terima!')
        ->assertSee('Ahmad Farhan');

    $candidate = AdmissionCandidate::where('student_name', 'Ahmad Farhan')->first();
    expect($candidate)->not->toBeNull()
        ->and($candidate->registration_number)->toStartWith('REG-')
        ->and($candidate->status)->toBe('registered')
        ->and($candidate->guardian_phone)->toBe('081299887766');

    // Verifikasi WhatsApp konfirmasi pendaftaran terkirim
    Http::assertSent(fn ($req) => str_contains($req['target'], '081299887766') && str_contains($req['message'], 'Nomor Registrasi'));
});

test('test scores can be updated and average score is calculated accurately', function () {
    $candidate = AdmissionCandidate::factory()->create([
        'status' => 'registered',
        'test_score_academic' => null,
    ]);

    /** @var AdmissionConversionService $service */
    $service = app(AdmissionConversionService::class);

    $service->updateTestScores($candidate, [
        'test_score_academic' => 80.0,
        'test_score_quran' => 90.0,
        'test_score_interview' => 85.0,
        'test_notes' => 'Hafalan Juz 30 lancar dan mutqin',
    ]);

    $candidate->refresh();
    expect($candidate->status)->toBe('tested')
        ->and((float) $candidate->test_score_academic)->toBe(80.0)
        ->and((float) $candidate->test_score_quran)->toBe(90.0)
        ->and((float) $candidate->test_score_interview)->toBe(85.0)
        ->and($candidate->averageScore())->toBe(85.0);
});

test('marking candidate as passed sends congratulations whatsapp notification', function () {
    $candidate = AdmissionCandidate::factory()->create([
        'guardian_phone' => '081255556666',
        'status' => 'tested',
    ]);

    /** @var AdmissionConversionService $service */
    $service = app(AdmissionConversionService::class);
    $service->updateStatus($candidate, 'passed');

    expect($candidate->fresh()->status)->toBe('passed');

    Http::assertSent(fn ($req) => str_contains($req['target'], '081255556666') && str_contains($req['message'], 'LULUS SELEKSI'));
});

test('1-click conversion accurately converts candidate to active student, guardian, enrollment, and portal user', function () {
    $academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $batch = AdmissionBatch::factory()->create(['academic_year_id' => $academicYear->id]);
    $class = SchoolClass::factory()->create([
        'academic_year_id' => $academicYear->id,
        'name' => 'Kelas 7A Tahfidz',
        'grade_level' => 7,
    ]);

    $candidate = AdmissionCandidate::factory()->passed()->create([
        'admission_batch_id' => $batch->id,
        'student_name' => 'Fathimah Azzahra',
        'gender' => 'female',
        'guardian_name' => 'Ustadz Abdullah',
        'guardian_phone' => '081311223344',
        'guardian_email' => 'abdullah@example.com',
    ]);

    /** @var AdmissionConversionService $service */
    $service = app(AdmissionConversionService::class);

    // Eksekusi 1-klik konversi
    $student = $service->convertCandidateToStudent($candidate, $class->id, '202607001', true);

    expect($student)->toBeInstanceOf(Student::class)
        ->and($student->name)->toBe('Fathimah Azzahra')
        ->and($student->gender)->toBe('female')
        ->and($student->nis)->toBe('202607001')
        ->and($student->status)->toBe('active');

    // 1. Verifikasi Guardian dibuat
    $guardian = Guardian::where('phone', '081311223344')->first();
    expect($guardian)->not->toBeNull()
        ->and($guardian->name)->toBe('Ustadz Abdullah')
        ->and($student->guardian_id)->toBe($guardian->id);

    // 2. Verifikasi Akun User Wali Murid dibuat
    expect($guardian->user_id)->not->toBeNull();
    $user = User::find($guardian->user_id);
    expect($user)->not->toBeNull()
        ->and($user->hasRole('wali_murid'))->toBeTrue();

    // 3. Verifikasi Enrollment Rombel dibuat
    $enrollment = StudentEnrollment::where('student_id', $student->id)->first();
    expect($enrollment)->not->toBeNull()
        ->and($enrollment->class_id)->toBe($class->id)
        ->and($enrollment->academic_year_id)->toBe($academicYear->id)
        ->and($enrollment->status)->toBe('active');

    // 4. Verifikasi status candidate menjadi enrolled
    $candidate->refresh();
    expect($candidate->status)->toBe('enrolled')
        ->and($candidate->converted_student_id)->toBe($student->id)
        ->and($candidate->converted_at)->not->toBeNull();

    // 5. Verifikasi WhatsApp ucapan selamat datang terkirim
    Http::assertSent(fn ($req) => str_contains($req['target'], '081311223344') && str_contains($req['message'], 'SANTRI AKTIF'));

    // 6. Upaya konversi ulang harus ditolak
    expect(fn () => $service->convertCandidateToStudent($candidate, $class->id))
        ->toThrow(ValidationException::class);
});

test('admin can use CandidateIndex livewire component to input scores and execute conversion', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $batch = AdmissionBatch::factory()->create(['academic_year_id' => $academicYear->id]);
    $class = SchoolClass::factory()->create(['academic_year_id' => $academicYear->id, 'name' => '7B']);

    $candidate = AdmissionCandidate::factory()->create([
        'admission_batch_id' => $batch->id,
        'student_name' => 'Bilal bin Rabah',
        'status' => 'verified',
    ]);

    Livewire::actingAs($admin)
        ->test(CandidateIndex::class)
        ->assertSee('Penerimaan Santri Baru (PPDB)')
        ->assertSee('Bilal bin Rabah')
        // Buka modal penilaian tes
        ->call('openScoreModal', $candidate->id)
        ->assertSet('showScoreModal', true)
        ->set('test_score_academic', 88)
        ->set('test_score_quran', 92)
        ->set('test_score_interview', 90)
        ->call('saveScores')
        ->assertSet('showScoreModal', false);

    expect($candidate->fresh()->status)->toBe('tested');

    // Luluskan
    Livewire::actingAs($admin)
        ->test(CandidateIndex::class)
        ->call('markAsPassed', $candidate->id);

    expect($candidate->fresh()->status)->toBe('passed');

    // Buka modal 1-klik konversi dan jalankan
    Livewire::actingAs($admin)
        ->test(CandidateIndex::class)
        ->call('openConvertModal', $candidate->id)
        ->assertSet('showConvertModal', true)
        ->set('target_class_id', $class->id)
        ->call('executeConversion')
        ->assertSet('showConvertModal', false);

    $candidate->refresh();
    expect($candidate->status)->toBe('enrolled')
        ->and($candidate->converted_student_id)->not->toBeNull();
});

test('admin can manage admission batches via AdmissionBatches livewire component', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $academicYear = AcademicYear::factory()->create(['is_active' => true]);

    Livewire::actingAs($admin)
        ->test(AdmissionBatches::class)
        ->assertSee('Gelombang PPDB')
        ->call('openCreateModal')
        ->set('academic_year_id', $academicYear->id)
        ->set('name', 'Gelombang 1 Mandiri')
        ->set('wave_number', 1)
        ->set('start_date', now()->toDateString())
        ->set('end_date', now()->addMonth()->toDateString())
        ->set('quota', 75)
        ->set('registration_fee', 200000)
        ->call('save')
        ->assertSee('Gelombang 1 Mandiri');

    $batch = AdmissionBatch::where('name', 'Gelombang 1 Mandiri')->first();
    expect($batch)->not->toBeNull();

    // Toggle active status
    Livewire::actingAs($admin)
        ->test(AdmissionBatches::class)
        ->call('toggleActive', $batch->id);

    expect($batch->fresh()->is_active)->toBeFalse();
});

test('public print registration slip renders candidate details and registration number', function () {
    $candidate = AdmissionCandidate::factory()->create([
        'registration_number' => 'REG-2026-9999',
        'student_name' => 'Muhammad Al-Fatih',
    ]);

    $this->get(route('ppdb.print', 'REG-2026-9999'))->assertForbidden();

    $this->get(URL::signedRoute('ppdb.print', 'REG-2026-9999'))
        ->assertOk()
        ->assertSee('BUKTI PENDAFTARAN SANTRI BARU')
        ->assertSee('REG-2026-9999')
        ->assertSee('Muhammad Al-Fatih');
});

test('public registration supports multi-step wizard navigation and step validation', function () {
    $academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $batch = AdmissionBatch::factory()->create([
        'academic_year_id' => $academicYear->id,
        'name' => 'Gelombang 1 Reguler',
        'is_active' => true,
    ]);

    Livewire::test(PublicRegistration::class)
        ->assertSet('currentStep', 1)
        ->set('admission_batch_id', $batch->id)
        ->call('nextStep')
        ->assertSet('currentStep', 2)
        // Step 2 without student name should fail validation
        ->set('student_name', '')
        ->call('nextStep')
        ->assertHasErrors(['student_name'])
        ->assertSet('currentStep', 2)
        // Provide student data and move to step 3
        ->set('student_name', 'Fauzan Ahmad')
        ->set('gender', 'male')
        ->call('nextStep')
        ->assertSet('currentStep', 3)
        // Step 3 without guardian name should fail validation
        ->set('guardian_name', '')
        ->call('nextStep')
        ->assertHasErrors(['guardian_name'])
        ->assertSet('currentStep', 3)
        // Provide guardian data and move to step 4
        ->set('guardian_name', 'Bpk. Ahmad')
        ->set('guardian_phone', '081234567890')
        ->call('nextStep')
        ->assertSet('currentStep', 4)
        // Can go back to step 1
        ->call('goToStep', 1)
        ->assertSet('currentStep', 1);
});

test('public registration supports tracking application status via lookup modal', function () {
    $candidate = AdmissionCandidate::factory()->create([
        'registration_number' => 'REG-2026-7788',
        'student_name' => 'Salman Al Farisi',
        'guardian_phone' => '6281234567890',
        'status' => 'verified',
    ]);

    Livewire::test(PublicRegistration::class)
        ->assertSet('showLookupModal', false)
        ->call('openLookupModal')
        ->assertSet('showLookupModal', true)
        ->set('lookup_registration_number', 'REG-2026-7788')
        ->set('lookup_phone_last4', '0000')
        ->call('checkStatus')
        ->assertSet('lookupNotFound', true)
        ->assertDontSee('Salman Al Farisi')
        ->set('lookup_phone_last4', '7890')
        ->call('checkStatus')
        ->assertSet('lookupNotFound', false)
        ->assertSee('Salman Al Farisi')
        ->assertSee('Terverifikasi')
        // Test not found
        ->set('lookup_registration_number', 'REG-UNKNOWN')
        ->call('checkStatus')
        ->assertSet('lookupNotFound', true)
        ->assertSee('Nomor Registrasi Tidak Ditemukan')
        ->call('closeLookupModal')
        ->assertSet('showLookupModal', false);
});

test('public registration only offers batches that are currently open with quota left', function () {
    $open = AdmissionBatch::factory()->create(['name' => 'Gelombang Terbuka']);
    AdmissionBatch::factory()->create(['name' => 'Gelombang Kedaluwarsa', 'start_date' => now()->subMonths(3), 'end_date' => now()->subMonth()]);
    AdmissionBatch::factory()->create(['name' => 'Gelombang Belum Mulai', 'start_date' => now()->addWeek(), 'end_date' => now()->addMonth()]);

    Livewire::test(PublicRegistration::class)
        ->assertSet('admission_batch_id', $open->id)
        ->assertSee('Gelombang Terbuka')
        ->assertDontSee('Gelombang Kedaluwarsa')
        ->assertDontSee('Gelombang Belum Mulai');
});

test('registering to a closed batch or twice for the same child is rejected', function () {
    $service = app(AdmissionConversionService::class);
    $expired = AdmissionBatch::factory()->create(['start_date' => now()->subMonths(3), 'end_date' => now()->subMonth()]);
    $open = AdmissionBatch::factory()->create();
    $payload = [
        'student_name' => 'Zaid Bin Haritsah',
        'gender' => 'male',
        'guardian_name' => 'Haritsah',
        'guardian_phone' => '6281234567811',
    ];

    expect(fn () => $service->registerCandidate($payload + ['admission_batch_id' => $expired->id]))
        ->toThrow(ValidationException::class);

    $service->registerCandidate($payload + ['admission_batch_id' => $open->id]);

    expect(fn () => $service->registerCandidate(array_merge($payload, ['student_name' => 'zaid bin haritsah', 'admission_batch_id' => $open->id])))
        ->toThrow(ValidationException::class);
    expect(AdmissionCandidate::count())->toBe(1);
});
