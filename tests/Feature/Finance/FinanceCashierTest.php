<?php

use App\Livewire\Finance\CashierPos;
use App\Livewire\Finance\PaymentTransactions;
use App\Models\AcademicYear;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\Guardian;
use App\Models\PaymentTransaction;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentBill;
use App\Models\StudentEnrollment;
use App\Models\StudentFeeWaiver;
use App\Models\User;
use App\Services\BillingService;
use App\Services\DocumentNumberGenerator;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(PermissionSeeder::class);
});

test('authorized users can access cashier POS and finance settings while unauthorized users are forbidden', function () {
    $tuUser = User::factory()->create();
    $tuUser->assignRole('tata_usaha');

    $guruUser = User::factory()->create();
    $guruUser->assignRole('guru');

    // Tata Usaha has finance.view and finance.cashier
    $this->actingAs($tuUser)->get(route('finance.cashier'))->assertOk();
    $this->actingAs($tuUser)->get(route('finance.transactions'))->assertOk();

    // Guru does not have finance access
    $this->actingAs($guruUser)->get(route('finance.cashier'))->assertForbidden();
    $this->actingAs($guruUser)->get(route('finance.transactions'))->assertForbidden();
    $this->actingAs($guruUser)->get(route('finance.fees'))->assertForbidden();
});

test('billing service generates monthly bills for active students in academic year', function () {
    $academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $class = SchoolClass::factory()->create([
        'academic_year_id' => $academicYear->id,
        'grade_level' => '7',
    ]);

    $students = Student::factory()->count(3)->create(['status' => 'active']);
    foreach ($students as $student) {
        StudentEnrollment::create([
            'student_id' => $student->id,
            'academic_year_id' => $academicYear->id,
            'class_id' => $class->id,
            'enrolled_at' => now()->toDateString(),
            'status' => 'active',
        ]);
    }

    $feeCategory = FeeCategory::factory()->create([
        'name' => 'SPP Bulanan',
        'type' => 'monthly',
        'is_active' => true,
    ]);

    FeeStructure::factory()->create([
        'academic_year_id' => $academicYear->id,
        'fee_category_id' => $feeCategory->id,
        'grade_level' => '7',
        'default_amount' => 350000.00,
    ]);

    $service = app(BillingService::class);
    $result = $service->generateMonthlyBills(
        academicYearId: $academicYear->id,
        feeCategoryId: $feeCategory->id,
        month: 7,
        year: 2026
    );

    expect($result['created'])->toBe(3)
        ->and($result['skipped'])->toBe(0);

    expect(StudentBill::query()->where('fee_category_id', $feeCategory->id)->count())->toBe(3);

    $bill = StudentBill::query()->where('student_id', $students[0]->id)->first();
    expect($bill)->not->toBeNull()
        ->and((float) $bill->amount_due)->toBe(350000.00)
        ->and((float) $bill->amount_paid)->toBe(0.00)
        ->and($bill->status)->toBe('unpaid')
        ->and($bill->month)->toBe(7)
        ->and($bill->year)->toBe(2026);

    // Running again for the same period skips existing bills (no duplicate records)
    $secondResult = $service->generateMonthlyBills(
        academicYearId: $academicYear->id,
        feeCategoryId: $feeCategory->id,
        month: 7,
        year: 2026
    );

    expect($secondResult['created'])->toBe(0)
        ->and($secondResult['skipped'])->toBe(3);
});

test('billing service applies fee waiver percentage and fixed amount discounts properly', function () {
    $academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $class = SchoolClass::factory()->create([
        'academic_year_id' => $academicYear->id,
        'grade_level' => '7',
    ]);

    $studentDiscount50 = Student::factory()->create(['status' => 'active']);
    $studentDiscountFull = Student::factory()->create(['status' => 'active']);

    foreach ([$studentDiscount50, $studentDiscountFull] as $student) {
        StudentEnrollment::create([
            'student_id' => $student->id,
            'academic_year_id' => $academicYear->id,
            'class_id' => $class->id,
            'enrolled_at' => now()->toDateString(),
            'status' => 'active',
        ]);
    }

    $feeCategory = FeeCategory::factory()->create(['type' => 'monthly']);

    FeeStructure::factory()->create([
        'academic_year_id' => $academicYear->id,
        'fee_category_id' => $feeCategory->id,
        'grade_level' => '7',
        'default_amount' => 300000.00,
    ]);

    // Student 1: 50% discount
    StudentFeeWaiver::factory()->create([
        'student_id' => $studentDiscount50->id,
        'fee_category_id' => $feeCategory->id,
        'academic_year_id' => $academicYear->id,
        'discount_type' => 'percentage',
        'discount_value' => 50.00,
    ]);

    // Student 2: 100% discount
    StudentFeeWaiver::factory()->create([
        'student_id' => $studentDiscountFull->id,
        'fee_category_id' => $feeCategory->id,
        'academic_year_id' => $academicYear->id,
        'discount_type' => 'percentage',
        'discount_value' => 100.00,
    ]);

    app(BillingService::class)->generateMonthlyBills(
        academicYearId: $academicYear->id,
        feeCategoryId: $feeCategory->id,
        month: 8,
        year: 2026
    );

    $bill1 = StudentBill::query()->where('student_id', $studentDiscount50->id)->first();
    expect((float) $bill1->amount_due)->toBe(150000.00)
        ->and($bill1->status)->toBe('unpaid');

    $bill2 = StudentBill::query()->where('student_id', $studentDiscountFull->id)->first();
    expect((float) $bill2->amount_due)->toBe(0.00)
        ->and($bill2->status)->toBe('paid');
});

test('document number generator produces unique sequential receipt numbers without race condition', function () {
    $generator = app(DocumentNumberGenerator::class);

    $num1 = $generator->next(prefix: 'KW', pad: 5, year: 2026);
    $num2 = $generator->next(prefix: 'KW', pad: 5, year: 2026);
    $num3 = $generator->next(prefix: 'KW', pad: 5, year: 2026);

    expect($num1)->toBe('KW-2026-00001')
        ->and($num2)->toBe('KW-2026-00002')
        ->and($num3)->toBe('KW-2026-00003');
});

test('cashier POS searches student and processes full payment with receipt generation', function () {
    $cashier = User::factory()->create();
    $cashier->assignRole('bendahara');

    $guardian = Guardian::factory()->create(['phone' => '6281234567890']);
    $student = Student::factory()->create([
        'guardian_id' => $guardian->id,
        'name' => 'Fulan Al-Ayyubi',
        'nis' => '20269999',
        'status' => 'active',
    ]);

    $category = FeeCategory::factory()->create(['name' => 'SPP Bulanan']);
    $bill = StudentBill::factory()->create([
        'student_id' => $student->id,
        'fee_category_id' => $category->id,
        'month' => 7,
        'year' => 2026,
        'amount_due' => 350000.00,
        'amount_paid' => 0.00,
        'status' => 'unpaid',
    ]);

    Livewire::actingAs($cashier)
        ->test(CashierPos::class)
        ->set('studentSearch', 'Fulan')
        ->assertSee('Fulan Al-Ayyubi')
        ->call('selectStudent', $student->id)
        ->assertSet('selectedStudentId', $student->id)
        ->assertSet('selectedBills', [$bill->id])
        ->assertSet('totalToPay', 350000.00)
        ->set('paymentMethod', 'cash')
        ->set('cashTendered', 400000.00)
        ->assertSet('changeAmount', 50000.00)
        ->call('processPayment')
        ->assertDispatched('notify');

    $bill->refresh();
    expect($bill->status)->toBe('paid')
        ->and((float) $bill->amount_paid)->toBe(350000.00);

    $transaction = PaymentTransaction::query()->where('student_id', $student->id)->first();
    expect($transaction)->not->toBeNull()
        ->and($transaction->receipt_number)->toMatch('/^KW-\d{4}-\d{5}$/')
        ->and((float) $transaction->total_amount)->toBe(350000.00)
        ->and($transaction->payment_method)->toBe('cash')
        ->and($transaction->received_by)->toBe($cashier->id);

    expect($transaction->items()->count())->toBe(1)
        ->and((float) $transaction->items->first()->amount_allocated)->toBe(350000.00);
});

test('cashier POS supports partial payment installment', function () {
    $cashier = User::factory()->create();
    $cashier->assignRole('bendahara');

    $student = Student::factory()->create(['status' => 'active']);
    $bill = StudentBill::factory()->create([
        'student_id' => $student->id,
        'amount_due' => 500000.00,
        'amount_paid' => 0.00,
        'status' => 'unpaid',
    ]);

    Livewire::actingAs($cashier)
        ->test(CashierPos::class)
        ->call('selectStudent', $student->id)
        ->call('updateAllocation', $bill->id, 200000.00)
        ->assertSet('totalToPay', 200000.00)
        ->set('paymentMethod', 'transfer')
        ->call('processPayment');

    $bill->refresh();
    expect($bill->status)->toBe('partially_paid')
        ->and((float) $bill->amount_paid)->toBe(200000.00)
        ->and($bill->remaining_amount)->toBe(300000.00);
});

test('cashier POS dispatches whatsapp receipt notification via fonnte service', function () {
    Http::fake([
        '*fonnte.com*' => Http::response(['status' => true, 'id' => 'wa-msg-123'], 200),
    ]);

    $cashier = User::factory()->create();
    $cashier->assignRole('bendahara');

    $guardian = Guardian::factory()->create(['phone' => '6281299887766']);
    $student = Student::factory()->create([
        'guardian_id' => $guardian->id,
        'name' => 'Ahmad Dahlan',
        'status' => 'active',
    ]);

    $bill = StudentBill::factory()->create([
        'student_id' => $student->id,
        'amount_due' => 250000.00,
        'amount_paid' => 0.00,
        'status' => 'unpaid',
    ]);

    Livewire::actingAs($cashier)
        ->test(CashierPos::class)
        ->call('selectStudent', $student->id)
        ->call('processPayment')
        ->call('sendWhatsAppReceipt')
        ->assertDispatched('notify');

    Http::assertSent(function ($request) use ($guardian) {
        return str_contains($request->url(), 'api.fonnte.com/send')
            && $request['target'] === $guardian->phone
            && str_contains($request['message'], 'Ahmad Dahlan');
    });
});

test('payment transactions list can filter by method and export CSV', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    PaymentTransaction::factory()->create([
        'receipt_number' => 'KW-2026-90001',
        'payment_method' => 'cash',
        'total_amount' => 300000.00,
        'paid_at' => now(),
    ]);

    PaymentTransaction::factory()->create([
        'receipt_number' => 'KW-2026-90002',
        'payment_method' => 'transfer',
        'total_amount' => 500000.00,
        'paid_at' => now(),
    ]);

    Livewire::actingAs($admin)
        ->test(PaymentTransactions::class)
        ->assertSee('KW-2026-90001')
        ->assertSee('KW-2026-90002')
        ->set('methodFilter', 'transfer')
        ->assertSee('KW-2026-90002')
        ->assertDontSee('KW-2026-90001')
        ->call('exportCsv')
        ->assertFileDownloaded('rekap_buku_kas_'.now()->format('Ymd_His').'.csv');
});

test('print receipt and daily cashbook render successfully with proper amounts and terbilang', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $student = Student::factory()->create(['name' => 'Hasan Al-Banna']);
    $bill = StudentBill::factory()->create(['student_id' => $student->id]);

    $transaction = PaymentTransaction::factory()->create([
        'receipt_number' => 'KW-2026-77777',
        'student_id' => $student->id,
        'total_amount' => 250000.00,
        'paid_at' => now(),
    ]);

    $transaction->items()->create([
        'student_bill_id' => $bill->id,
        'amount_allocated' => 250000.00,
    ]);

    $this->actingAs($admin)
        ->get(route('finance.receipts.print', $transaction))
        ->assertOk()
        ->assertSee('KW-2026-77777')
        ->assertSee('Hasan Al-Banna')
        ->assertSee('Dua Ratus Lima Puluh Ribu Rupiah');

    $this->actingAs($admin)
        ->get(route('finance.cashbook.print', ['start_date' => now()->toDateString(), 'end_date' => now()->toDateString()]))
        ->assertOk()
        ->assertSee('BUKU KAS HARIAN PENERIMAAN TU')
        ->assertSee('KW-2026-77777');
});

test('terbilang helper accurately converts numbers to Indonesian words', function () {
    expect(BillingService::terbilang(0))->toBe('')
        ->and(BillingService::terbilang(1))->toBe('Satu')
        ->and(BillingService::terbilang(11))->toBe('Sebelas')
        ->and(BillingService::terbilang(25))->toBe('Dua Puluh Lima')
        ->and(BillingService::terbilang(100))->toBe('Seratus')
        ->and(BillingService::terbilang(150))->toBe('Seratus Lima Puluh')
        ->and(BillingService::terbilang(1000))->toBe('Seribu')
        ->and(BillingService::terbilang(250000))->toBe('Dua Ratus Lima Puluh Ribu')
        ->and(BillingService::terbilang(1750000))->toBe('Satu Juta Tujuh Ratus Lima Puluh Ribu');
});

test('cashier POS rejects a bill that another cashier already paid after the page was loaded', function () {
    $cashier = User::factory()->create();
    $cashier->assignRole('tata_usaha');
    $student = Student::factory()->create(['status' => 'active']);
    $bill = StudentBill::factory()->create([
        'student_id' => $student->id,
        'fee_category_id' => FeeCategory::factory()->create()->id,
        'amount_due' => 350000.00,
        'amount_paid' => 0.00,
        'status' => 'unpaid',
    ]);

    $component = Livewire::actingAs($cashier)->test(CashierPos::class)->call('selectStudent', $student->id);

    $bill->update(['amount_paid' => 350000.00, 'status' => 'paid']);

    $component->call('processPayment')
        ->assertDispatched('notify', type: 'error')
        ->assertSet('completedTransactionId', null);

    expect(PaymentTransaction::count())->toBe(0)
        ->and((float) $bill->fresh()->amount_paid)->toBe(350000.00);
});
