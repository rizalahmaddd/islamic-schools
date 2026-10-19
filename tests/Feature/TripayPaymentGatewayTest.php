<?php

use App\Livewire\Portal\GuardianDashboard;
use App\Models\AcademicYear;
use App\Models\FeeCategory;
use App\Models\Guardian;
use App\Models\OnlinePaymentTransaction;
use App\Models\PaymentTransaction;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentBill;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Services\TripayService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(PermissionSeeder::class);

    Config::set('services.tripay.api_key', 'test-api-key');
    Config::set('services.tripay.private_key', 'test-private-key');
    Config::set('services.tripay.merchant_code', 'T12345');
    Config::set('services.tripay.mode', 'sandbox');
});

test('tripay service provides valid payment channels and calculates fee accurately', function () {
    /** @var TripayService $service */
    $service = app(TripayService::class);

    $channels = $service->getPaymentChannels();
    expect($channels)->not->toBeEmpty()
        ->and(collect($channels)->pluck('code'))->toContain('QRIS', 'BRIVA', 'BSIVA');

    // QRIS: 750 + 0.7% of 1.000.000 = 750 + 7000 = 7750
    $qrisFee = $service->calculateFee('QRIS', 1000000);
    expect($qrisFee)->toBe(7750.0);

    // BRIVA: 4000 flat
    $vaFee = $service->calculateFee('BRIVA', 1000000);
    expect($vaFee)->toBe(4000.0);
});

test('createClosedTransaction generates pending online transaction with unique order id', function () {
    $academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $guardian = Guardian::factory()->create(['phone' => '08123456789']);
    $student = Student::factory()->create(['guardian_id' => $guardian->id]);
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
    ]);

    /** @var TripayService $service */
    $service = app(TripayService::class);

    $tx = $service->createClosedTransaction($bill, 'QRIS');

    expect($tx)->toBeInstanceOf(OnlinePaymentTransaction::class)
        ->and($tx->order_id)->toStartWith('ORD-')
        ->and((float) $tx->amount)->toBe(500000.0)
        ->and($tx->payment_channel)->toBe('QRIS')
        ->and($tx->status)->toBe('pending')
        ->and($tx->qr_string)->not->toBeNull()
        ->and($tx->isPending())->toBeTrue();
});

test('createClosedTransaction throws exception if bill is already paid', function () {
    $academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $student = Student::factory()->create();
    $feeCategory = FeeCategory::factory()->create();

    $bill = StudentBill::create([
        'student_id' => $student->id,
        'fee_category_id' => $feeCategory->id,
        'academic_year_id' => $academicYear->id,
        'month' => 10,
        'year' => 2026,
        'amount_due' => 500000,
        'amount_paid' => 500000,
        'status' => 'paid',
    ]);

    /** @var TripayService $service */
    $service = app(TripayService::class);

    expect(fn () => $service->createClosedTransaction($bill, 'QRIS'))
        ->toThrow(ValidationException::class);
});

test('webhook rejects invalid signature with 403 forbidden', function () {
    $payload = json_encode([
        'merchant_ref' => 'ORD-2026-9999',
        'status' => 'PAID',
    ]);

    $this->postJson(route('api.v1.payment.webhook'), json_decode($payload, true), [
        'X-Callback-Signature' => 'invalid-signature-hash',
    ])->assertForbidden();
});

test('webhook returns 404 if transaction is not found in database', function () {
    $privateKey = 'test-private-key';
    $payload = json_encode([
        'merchant_ref' => 'ORD-2026-9999',
        'status' => 'PAID',
    ]);

    $signature = hash_hmac('sha256', $payload, $privateKey);

    $this->postJson(route('api.v1.payment.webhook'), json_decode($payload, true), [
        'X-Callback-Signature' => $signature,
    ])->assertNotFound();
});

test('webhook processes paid event, settles transaction, updates bill, and creates receipt', function () {
    $academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $guardian = Guardian::factory()->create(['phone' => '081298765432']);
    $student = Student::factory()->create(['guardian_id' => $guardian->id]);
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
    ]);

    /** @var TripayService $service */
    $service = app(TripayService::class);
    $onlineTx = $service->createClosedTransaction($bill, 'QRIS');

    $privateKey = 'test-private-key';
    $payload = [
        'merchant_ref' => $onlineTx->order_id,
        'reference' => $onlineTx->reference,
        'payment_method' => 'QRIS',
        'total_amount' => 500000,
        'status' => 'PAID',
        'paid_at' => now()->timestamp,
    ];
    $json = json_encode($payload);
    $signature = hash_hmac('sha256', $json, $privateKey);

    $response = $this->postJson(route('api.v1.payment.webhook'), $payload, [
        'X-Callback-Signature' => $signature,
    ]);

    $response->assertOk()
        ->assertJson(['success' => true]);

    // Verifikasi Database
    $onlineTx->refresh();
    expect($onlineTx->status)->toBe('settlement')
        ->and($onlineTx->paid_at)->not->toBeNull()
        ->and($onlineTx->payment_transaction_id)->not->toBeNull();

    $bill->refresh();
    expect((float) $bill->amount_paid)->toBe(500000.0)
        ->and($bill->status)->toBe('paid');

    // Verifikasi PaymentTransaction (Kuitansi Resmi)
    $receipt = PaymentTransaction::find($onlineTx->payment_transaction_id);
    expect($receipt)->not->toBeNull()
        ->and($receipt->receipt_number)->toStartWith('KW-')
        ->and((float) $receipt->total_amount)->toBe(500000.0)
        ->and($receipt->items)->toHaveCount(1)
        ->and((float) $receipt->items->first()->amount_allocated)->toBe(500000.0);
});

test('webhook is idempotent and protects against double-crediting when paid callback is received multiple times', function () {
    $academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $guardian = Guardian::factory()->create(['phone' => '081298765432']);
    $student = Student::factory()->create(['guardian_id' => $guardian->id]);
    $feeCategory = FeeCategory::factory()->create(['name' => 'Uang Gedung']);

    $bill = StudentBill::create([
        'student_id' => $student->id,
        'fee_category_id' => $feeCategory->id,
        'academic_year_id' => $academicYear->id,
        'month' => 10,
        'year' => 2026,
        'amount_due' => 1000000,
        'amount_paid' => 0,
        'status' => 'unpaid',
    ]);

    /** @var TripayService $service */
    $service = app(TripayService::class);
    $onlineTx = $service->createClosedTransaction($bill, 'BRIVA');

    $privateKey = 'test-private-key';
    $payload = [
        'merchant_ref' => $onlineTx->order_id,
        'reference' => $onlineTx->reference,
        'payment_method' => 'BRIVA',
        'total_amount' => 1000000,
        'status' => 'PAID',
    ];
    $json = json_encode($payload);
    $signature = hash_hmac('sha256', $json, $privateKey);

    // Call 1: Pertama kali sukses
    $this->postJson(route('api.v1.payment.webhook'), $payload, [
        'X-Callback-Signature' => $signature,
    ])->assertOk();

    expect((float) $bill->fresh()->amount_paid)->toBe(1000000.0)
        ->and(PaymentTransaction::count())->toBe(1);

    // Call 2: Callback ulang (duplicate webhook delivery)
    $this->postJson(route('api.v1.payment.webhook'), $payload, [
        'X-Callback-Signature' => $signature,
    ])->assertOk();

    // Nominal tidak boleh berlipat ganda
    expect((float) $bill->fresh()->amount_paid)->toBe(1000000.0)
        ->and(PaymentTransaction::count())->toBe(1);
});

test('guardian can open payment modal and create online payment transaction via livewire', function () {
    $waliUser = User::factory()->create();
    $waliUser->assignRole('wali_murid');

    $guardian = Guardian::factory()->create([
        'user_id' => $waliUser->id,
        'name' => 'Bapak Sulaiman',
    ]);

    $academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $class = SchoolClass::factory()->create(['academic_year_id' => $academicYear->id]);
    $student = Student::factory()->create([
        'guardian_id' => $guardian->id,
        'name' => 'Fathurrahman',
        'status' => 'active',
    ]);
    StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'class_id' => $class->id,
        'academic_year_id' => $academicYear->id,
    ]);

    $feeCategory = FeeCategory::factory()->create(['name' => 'SPP Bulanan']);
    $bill = StudentBill::create([
        'student_id' => $student->id,
        'fee_category_id' => $feeCategory->id,
        'academic_year_id' => $academicYear->id,
        'month' => 10,
        'year' => 2026,
        'amount_due' => 350000,
        'amount_paid' => 0,
        'status' => 'unpaid',
    ]);

    Livewire::actingAs($waliUser)
        ->test(GuardianDashboard::class)
        ->call('setTab', 'finance')
        ->assertSee('Bayar Online')
        // Buka modal bayar
        ->call('openPaymentModal', $bill->id)
        ->assertSet('showPaymentModal', true)
        ->assertSet('payingBillId', $bill->id)
        ->assertSee('Pembayaran Tagihan Online')
        // Buat transaksi pembayaran QRIS
        ->set('selectedPaymentChannel', 'QRIS')
        ->call('createOnlinePayment')
        ->assertSee('Instruksi Pembayaran')
        ->assertSee('QRIS Standar Pembayaran Nasional')
        // Tutup modal
        ->call('closePaymentModal')
        ->assertSet('showPaymentModal', false);
});
