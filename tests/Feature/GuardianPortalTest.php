<?php

use App\Livewire\Dashboard;
use App\Livewire\Portal\GuardianDashboard;
use App\Models\AcademicYear;
use App\Models\FeeCategory;
use App\Models\Guardian;
use App\Models\PaymentTransaction;
use App\Models\QuranSurah;
use App\Models\SchoolClass;
use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentBill;
use App\Models\StudentEnrollment;
use App\Models\TahfidzSession;
use App\Models\Teacher;
use App\Models\User;
use App\Services\WhatsAppOtpService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\QuranSurahSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(PermissionSeeder::class);
    $this->seed(QuranSurahSeeder::class);

    Http::fake([
        'https://api.fonnte.com/*' => Http::response(['status' => true, 'message' => 'success! message in queue'], 200),
    ]);
});

test('guardian user is auto-provisioned when logging in via whatsapp otp with registered guardian phone', function () {
    $guardian = Guardian::factory()->create([
        'name' => 'Bapak Rahmat Hidayat',
        'phone' => '081234567890',
        'user_id' => null,
    ]);

    /** @var WhatsAppOtpService $otpService */
    $otpService = app(WhatsAppOtpService::class);

    $user = $otpService->findUser('081234567890');

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Bapak Rahmat Hidayat')
        ->and($user->phone)->toBe('081234567890')
        ->and($user->hasRole('wali_murid'))->toBeTrue()
        ->and($guardian->fresh()->user_id)->toBe($user->id);

    // Kirim OTP dan Verifikasi
    $result = $otpService->sendOtp('081234567890');
    expect($result['user_id'])->toBe($user->id);

    $verifiedUser = $otpService->verifyOtp($user->id, '123456');
    expect($verifiedUser->id)->toBe($user->id);
});

test('guest cannot access guardian portal and unauthorized user gets forbidden', function () {
    $this->get(route('portal.guardian'))->assertRedirect(route('login'));

    $userWithoutPermission = User::factory()->create();
    $this->actingAs($userWithoutPermission)
        ->get(route('portal.guardian'))
        ->assertForbidden();

    $waliUser = User::factory()->create();
    $waliUser->assignRole('wali_murid');

    $this->actingAs($waliUser)
        ->get(route('portal.guardian'))
        ->assertOk();
});

test('wali_murid is redirected from main dashboard to guardian portal', function () {
    $waliUser = User::factory()->create();
    $waliUser->assignRole('wali_murid');

    Livewire::actingAs($waliUser)
        ->test(Dashboard::class)
        ->assertRedirect(route('portal.guardian'));
});

test('child switcher switches data context between multiple siblings', function () {
    $waliUser = User::factory()->create();
    $waliUser->assignRole('wali_murid');

    $guardian = Guardian::factory()->create([
        'user_id' => $waliUser->id,
        'name' => 'Ibu Fatimah',
    ]);

    $academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $classA = SchoolClass::factory()->create(['academic_year_id' => $academicYear->id, 'name' => 'Kelas 7A']);
    $classB = SchoolClass::factory()->create(['academic_year_id' => $academicYear->id, 'name' => 'Kelas 9B']);

    $student1 = Student::factory()->create([
        'guardian_id' => $guardian->id,
        'name' => 'Ahmad Fathan',
        'status' => 'active',
    ]);
    StudentEnrollment::factory()->create([
        'student_id' => $student1->id,
        'class_id' => $classA->id,
        'academic_year_id' => $academicYear->id,
    ]);

    $student2 = Student::factory()->create([
        'guardian_id' => $guardian->id,
        'name' => 'Aisyah Humaira',
        'status' => 'active',
    ]);
    StudentEnrollment::factory()->create([
        'student_id' => $student2->id,
        'class_id' => $classB->id,
        'academic_year_id' => $academicYear->id,
    ]);

    // Student 1 attendance
    StudentAttendance::create([
        'student_id' => $student1->id,
        'class_id' => $classA->id,
        'academic_year_id' => $academicYear->id,
        'date' => today()->toDateString(),
        'status' => 'present',
        'check_in_time' => '07:05:00',
    ]);

    // Student 2 tahfidz
    $teacher = Teacher::factory()->create();
    $surah78 = QuranSurah::where('number', 78)->first();
    TahfidzSession::create([
        'student_id' => $student2->id,
        'teacher_id' => $teacher->id,
        'session_type' => 'ziyadah',
        'surah_id' => $surah78->id,
        'verse_start' => 1,
        'verse_end' => 20,
        'juz' => 30,
        'grade' => 'Mumtaz',
        'recorded_at' => now(),
    ]);

    // Test Livewire component
    Livewire::actingAs($waliUser)
        ->test(GuardianDashboard::class)
        ->assertSee('Ahmad Fathan')
        ->assertSee('Kelas 7A')
        ->assertSee('Hadir')
        // Switch to Student 2
        ->set('selectedStudentId', $student2->id)
        ->assertSee('Aisyah Humaira')
        ->assertSee('Kelas 9B')
        ->assertSee('An-Naba')
        ->assertSee('Juz 30')
        ->assertSee('Mumtaz');
});

test('tab switching works for attendance, tahfidz, and finance', function () {
    $waliUser = User::factory()->create();
    $waliUser->assignRole('wali_murid');

    $guardian = Guardian::factory()->create(['user_id' => $waliUser->id]);
    $student = Student::factory()->create(['guardian_id' => $guardian->id, 'status' => 'active']);

    $academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $feeCategory = FeeCategory::factory()->create(['name' => 'SPP Bulanan']);
    $bill = StudentBill::create([
        'student_id' => $student->id,
        'fee_category_id' => $feeCategory->id,
        'academic_year_id' => $academicYear->id,
        'month' => 10,
        'year' => 2026,
        'amount_due' => 500000,
        'amount_paid' => 0,
        'status' => 'unpaid',
        'due_date' => now()->addDays(10)->toDateString(),
    ]);

    Livewire::actingAs($waliUser)
        ->test(GuardianDashboard::class)
        ->assertSee('Ringkasan Hari Ini')
        // Ganti ke tab Presensi
        ->call('setTab', 'attendance')
        ->assertSet('activeTab', 'attendance')
        ->assertSee('Rekap Kehadiran Santri')
        // Ganti ke tab Tahfidz
        ->call('setTab', 'tahfidz')
        ->assertSet('activeTab', 'tahfidz')
        ->assertSee('Peta Capaian 30 Juz')
        // Ganti ke tab Tagihan SPP
        ->call('setTab', 'finance')
        ->assertSet('activeTab', 'finance')
        ->assertSee('SPP Bulanan')
        ->assertSee('500.000');
});

test('guardian can only print report card for their own child', function () {
    $waliA = User::factory()->create();
    $waliA->assignRole('wali_murid');
    $guardianA = Guardian::factory()->create(['user_id' => $waliA->id]);
    $studentA = Student::factory()->create(['guardian_id' => $guardianA->id]);

    $waliB = User::factory()->create();
    $waliB->assignRole('wali_murid');
    $guardianB = Guardian::factory()->create(['user_id' => $waliB->id]);
    $studentB = Student::factory()->create(['guardian_id' => $guardianB->id]);

    // Wali A can print report card of Student A
    $this->actingAs($waliA)
        ->get(route('portal.report-cards.print', ['student' => $studentA->id]))
        ->assertOk();

    // Wali A cannot print report card of Student B
    $this->actingAs($waliA)
        ->get(route('portal.report-cards.print', ['student' => $studentB->id]))
        ->assertForbidden();
});

test('guardian can only print receipt for their own child', function () {
    $waliA = User::factory()->create();
    $waliA->assignRole('wali_murid');
    $guardianA = Guardian::factory()->create(['user_id' => $waliA->id]);
    $studentA = Student::factory()->create(['guardian_id' => $guardianA->id]);

    $waliB = User::factory()->create();
    $waliB->assignRole('wali_murid');
    $guardianB = Guardian::factory()->create(['user_id' => $waliB->id]);
    $studentB = Student::factory()->create(['guardian_id' => $guardianB->id]);

    $academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $feeCategory = FeeCategory::factory()->create();
    $billA = StudentBill::create([
        'student_id' => $studentA->id,
        'fee_category_id' => $feeCategory->id,
        'academic_year_id' => $academicYear->id,
        'month' => 10,
        'year' => 2026,
        'amount_due' => 500000,
        'amount_paid' => 500000,
        'status' => 'paid',
    ]);
    $transactionA = PaymentTransaction::create([
        'student_id' => $studentA->id,
        'receipt_number' => 'KWT-001',
        'payment_method' => 'cash',
        'total_amount' => 500000,
        'paid_at' => now(),
        'received_by_id' => $waliA->id,
    ]);

    $billB = StudentBill::create([
        'student_id' => $studentB->id,
        'fee_category_id' => $feeCategory->id,
        'academic_year_id' => $academicYear->id,
        'month' => 10,
        'year' => 2026,
        'amount_due' => 500000,
        'amount_paid' => 500000,
        'status' => 'paid',
    ]);
    $transactionB = PaymentTransaction::create([
        'student_id' => $studentB->id,
        'receipt_number' => 'KWT-002',
        'payment_method' => 'cash',
        'total_amount' => 500000,
        'paid_at' => now(),
        'received_by_id' => $waliB->id,
    ]);

    // Wali A can print receipt of Student A
    $this->actingAs($waliA)
        ->get(route('portal.receipts.print', ['paymentTransaction' => $transactionA->id]))
        ->assertOk();

    // Wali A cannot print receipt of Student B
    $this->actingAs($waliA)
        ->get(route('portal.receipts.print', ['paymentTransaction' => $transactionB->id]))
        ->assertForbidden();
});

test('pwa webmanifest and service worker files are accessible and valid', function () {
    $response = $this->get('/manifest.webmanifest');
    $response->assertOk();
    $manifest = json_decode($response->getContent(), true);

    expect($manifest)->toBeArray()
        ->and($manifest['display'])->toBe('standalone')
        ->and($manifest['start_url'])->toBe('/dashboard')
        ->and($manifest['icons'])->not->toBeEmpty();

    $swResponse = $this->get('/sw.js');
    $swResponse->assertOk()
        ->assertSee('islamic-school-pwa-v2');
});

test('pwa manifest follows the app branding and its default icons exist', function () {
    Setting::put('app_name', 'Pesantren Nurul Huda');

    $manifest = $this->get('/manifest.webmanifest')->assertOk()->json();

    expect($manifest['name'])->toBe('Pesantren Nurul Huda')
        ->and($manifest['short_name'])->toBe('Pesantren Nurul')
        ->and(collect($manifest['icons'])->pluck('sizes')->all())->toBe(['192x192', '512x512'])
        ->and(public_path('icons/icon-192.png'))->toBeFile()
        ->and(public_path('icons/icon-512.png'))->toBeFile()
        ->and(public_path('offline.html'))->toBeFile();
});

test('a guardian cannot switch the portal to a student who is not their child', function () {
    $parent = User::factory()->create();
    $parent->assignRole('wali_murid');
    $guardian = Guardian::factory()->create(['user_id' => $parent->id]);
    $ownChild = Student::factory()->create(['guardian_id' => $guardian->id, 'status' => 'active']);
    $stranger = Student::factory()->create(['name' => 'Anak Orang Lain', 'status' => 'active']);

    Livewire::actingAs($parent)
        ->test(GuardianDashboard::class)
        ->set('selectedStudentId', $stranger->id)
        ->assertSet('selectedStudentId', $ownChild->id)
        ->assertDontSee('Anak Orang Lain');
});

test('a guardian cannot open the payment modal for a bill of another family', function () {
    $parent = User::factory()->create();
    $parent->assignRole('wali_murid');
    Guardian::factory()->create(['user_id' => $parent->id]);
    $strangerBill = StudentBill::factory()->create(['status' => 'unpaid']);

    Livewire::actingAs($parent)
        ->test(GuardianDashboard::class)
        ->call('openPaymentModal', $strangerBill->id)
        ->assertForbidden();
});
