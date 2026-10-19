<?php

use App\Livewire\Cashless\CanteenPos;
use App\Livewire\Cashless\CardManagement;
use App\Livewire\Portal\GuardianDashboard;
use App\Models\CanteenMerchant;
use App\Models\CanteenProduct;
use App\Models\Guardian;
use App\Models\RfidCard;
use App\Models\Student;
use App\Models\User;
use App\Services\StudentWalletService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

test('service can top-up student wallet and update balance', function () {
    $student = Student::factory()->create();
    /** @var StudentWalletService $service */
    $service = app(StudentWalletService::class);
    $wallet = $service->getOrCreateWallet($student);

    expect((float) $wallet->balance)->toBe(0.0);

    $tx = $service->topUp($wallet, 50000, 'Top-up awal');

    $wallet->refresh();
    expect((float) $wallet->balance)->toBe(50000.0)
        ->and($tx->transaction_type)->toBe('topup')
        ->and((float) $tx->amount)->toBe(50000.0)
        ->and((float) $tx->balance_after)->toBe(50000.0);
});

test('rfid card checkout deducts balance and creates itemized canteen order', function () {
    $student = Student::factory()->create();
    /** @var StudentWalletService $service */
    $service = app(StudentWalletService::class);
    $wallet = $service->getOrCreateWallet($student);
    $service->topUp($wallet, 50000);

    $card = RfidCard::factory()->create([
        'student_id' => $student->id,
        'card_uid' => 'CARD-123456',
        'is_active' => true,
    ]);

    $merchant = CanteenMerchant::factory()->create(['name' => 'Kantin Utama']);
    $product1 = CanteenProduct::factory()->create([
        'canteen_merchant_id' => $merchant->id,
        'name' => 'Roti Bakar',
        'price' => 8000,
    ]);
    $product2 = CanteenProduct::factory()->create([
        'canteen_merchant_id' => $merchant->id,
        'name' => 'Susu Kurma',
        'price' => 7000,
    ]);

    $items = [
        [
            'product_id' => $product1->id,
            'name' => $product1->name,
            'quantity' => 1,
            'price' => 8000.0,
            'subtotal' => 8000.0,
        ],
        [
            'product_id' => $product2->id,
            'name' => $product2->name,
            'quantity' => 1,
            'price' => 7000.0,
            'subtotal' => 7000.0,
        ],
    ];

    $tx = $service->processCanteenPurchase('CARD-123456', 15000, $items, $merchant->id);

    $wallet->refresh();
    expect((float) $wallet->balance)->toBe(35000.0)
        ->and($tx->transaction_type)->toBe('purchase')
        ->and((float) $tx->amount)->toBe(15000.0)
        ->and((float) $tx->balance_after)->toBe(35000.0)
        ->and($tx->order)->not->toBeNull()
        ->and($tx->order->items)->toHaveCount(2);
});

test('checkout rejects if wallet balance is insufficient', function () {
    $student = Student::factory()->create();
    /** @var StudentWalletService $service */
    $service = app(StudentWalletService::class);
    $wallet = $service->getOrCreateWallet($student);
    $service->topUp($wallet, 5000);

    RfidCard::factory()->create([
        'student_id' => $student->id,
        'card_uid' => 'CARD-POOR-1',
        'is_active' => true,
    ]);

    $this->expectException(ValidationException::class);
    $service->processCanteenPurchase('CARD-POOR-1', 15000, []);
});

test('checkout rejects if daily limit is exceeded', function () {
    $student = Student::factory()->create();
    /** @var StudentWalletService $service */
    $service = app(StudentWalletService::class);
    $wallet = $service->getOrCreateWallet($student);
    $service->topUp($wallet, 50000);
    $service->setDailyLimit($wallet, 20000);

    RfidCard::factory()->create([
        'student_id' => $student->id,
        'card_uid' => 'CARD-LIMIT-1',
        'is_active' => true,
    ]);

    // Transaksi pertama 15000 (sisa jatah limit 5000)
    $service->processCanteenPurchase('CARD-LIMIT-1', 15000, []);

    // Transaksi kedua 10000 (melebihi limit harian 20000)
    $this->expectException(ValidationException::class);
    $service->processCanteenPurchase('CARD-LIMIT-1', 10000, []);
});

test('checkout rejects if rfid card is blocked', function () {
    $student = Student::factory()->create();
    /** @var StudentWalletService $service */
    $service = app(StudentWalletService::class);
    $wallet = $service->getOrCreateWallet($student);
    $service->topUp($wallet, 50000);

    $card = RfidCard::factory()->create([
        'student_id' => $student->id,
        'card_uid' => 'CARD-BLOCKED-1',
        'is_active' => false,
        'blocked_at' => now(),
        'blocked_reason' => 'Kartu santri hilang',
    ]);

    $this->expectException(ValidationException::class);
    $service->processCanteenPurchase('CARD-BLOCKED-1', 10000, []);
});

test('canteen pos livewire component can manage cart and checkout via rfid scan', function () {
    $cashier = User::factory()->create();
    $cashier->assignRole('admin');

    $student = Student::factory()->create();
    /** @var StudentWalletService $service */
    $service = app(StudentWalletService::class);
    $wallet = $service->getOrCreateWallet($student);
    $service->topUp($wallet, 40000);

    RfidCard::factory()->create([
        'student_id' => $student->id,
        'card_uid' => 'CARD-POS-TEST',
        'is_active' => true,
    ]);

    $merchant = CanteenMerchant::factory()->create();
    $product = CanteenProduct::factory()->create([
        'canteen_merchant_id' => $merchant->id,
        'name' => 'Nasi Kuning Barakah',
        'price' => 12000,
        'stock' => 10,
    ]);

    Livewire::actingAs($cashier)
        ->test(CanteenPos::class)
        ->assertSee('Nasi Kuning Barakah')
        ->call('addToCart', $product->id)
        ->assertSet('cart.'.$product->id.'.quantity', 1)
        ->set('cardUid', 'CARD-POS-TEST')
        ->call('processPayment')
        ->assertHasNoErrors()
        ->assertSet('cart', [])
        ->assertSee('Nasi Kuning Barakah');

    $wallet->refresh();
    expect((float) $wallet->balance)->toBe(28000.0);
});

test('card management livewire component can top-up wallet and set daily limits', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $student = Student::factory()->create(['name' => 'Ahmad Santri']);
    $card = RfidCard::factory()->create([
        'student_id' => $student->id,
        'card_uid' => 'RFID-MGT-001',
        'is_active' => true,
    ]);

    /** @var StudentWalletService $service */
    $service = app(StudentWalletService::class);
    $wallet = $service->getOrCreateWallet($student);

    Livewire::actingAs($admin)
        ->test(CardManagement::class)
        ->assertSee('Ahmad Santri')
        ->assertSee('RFID-MGT-001')
        ->call('openTopupModal', $student->id)
        ->assertSet('topupStudentId', $student->id)
        ->set('topupAmount', 25000)
        ->set('topupNotes', 'Setoran saku tunai wali')
        ->call('saveTopup')
        ->assertHasNoErrors();

    $wallet->refresh();
    expect((float) $wallet->balance)->toBe(25000.0);

    // Ubah limit belanja harian
    Livewire::actingAs($admin)
        ->test(CardManagement::class)
        ->call('openLimitModal', $student->id)
        ->set('newDailyLimit', 15000)
        ->call('saveLimit')
        ->assertHasNoErrors();

    $wallet->refresh();
    expect((float) $wallet->daily_limit)->toBe(15000.0);

    // Blokir kartu
    Livewire::actingAs($admin)
        ->test(CardManagement::class)
        ->call('toggleBlockCard', $card->id)
        ->assertHasNoErrors();

    $card->refresh();
    expect($card->is_active)->toBeFalse();
});

test('guardian portal displays student wallet balance and itemized canteen purchases', function () {
    $guardianUser = User::factory()->create();
    $guardianUser->assignRole('wali_murid');

    $guardian = Guardian::factory()->create([
        'user_id' => $guardianUser->id,
    ]);

    $student = Student::factory()->create([
        'guardian_id' => $guardian->id,
        'status' => 'active',
        'name' => 'Santri Tercinta',
    ]);

    /** @var StudentWalletService $service */
    $service = app(StudentWalletService::class);
    $wallet = $service->getOrCreateWallet($student);
    $service->topUp($wallet, 50000);
    $service->setDailyLimit($wallet, 25000);

    $card = RfidCard::factory()->create([
        'student_id' => $student->id,
        'card_uid' => 'CARD-GUARDIAN-TEST',
        'is_active' => true,
    ]);

    $merchant = CanteenMerchant::factory()->create(['name' => 'Kantin Pondok Sehat']);
    $items = [
        [
            'name' => 'Susu UHT Cokelat',
            'quantity' => 2,
            'price' => 5000.0,
            'subtotal' => 10000.0,
        ],
    ];

    $service->processCanteenPurchase('CARD-GUARDIAN-TEST', 10000, $items, $merchant->id);

    Livewire::actingAs($guardianUser)
        ->test(GuardianDashboard::class)
        ->call('setTab', 'cashless')
        ->assertSee('Saldo Dompet Santri')
        ->assertSee('40.000') // Saldo sisa Rp 40.000
        ->assertSee('25.000') // Daily limit Rp 25.000
        ->assertSee('Kantin Pondok Sehat')
        ->assertSee('Susu UHT Cokelat');
});

test('canteen POS charges the database price even if the cart in the browser was tampered with', function () {
    $this->seed([RoleSeeder::class, PermissionSeeder::class]);
    $cashier = User::factory()->create();
    $cashier->assignRole('admin');
    $student = Student::factory()->create();
    $service = app(StudentWalletService::class);
    $wallet = $service->getOrCreateWallet($student);
    $service->topUp($wallet, 50000);
    RfidCard::factory()->create(['student_id' => $student->id, 'card_uid' => 'CARD-TAMPER', 'is_active' => true]);
    $product = CanteenProduct::factory()->create(['price' => 12000, 'is_available' => true]);

    Livewire::actingAs($cashier)
        ->test(CanteenPos::class)
        ->call('addToCart', $product->id)
        ->set('cart.'.$product->id.'.price', 1)
        ->set('cart.'.$product->id.'.subtotal', 1)
        ->set('cardUid', 'CARD-TAMPER')
        ->call('processPayment');

    expect((float) $wallet->fresh()->balance)->toBe(38000.0);
});

test('unavailable canteen products cannot be added to the cart', function () {
    $this->seed([RoleSeeder::class, PermissionSeeder::class]);
    $cashier = User::factory()->create();
    $cashier->assignRole('admin');
    $product = CanteenProduct::factory()->create(['is_available' => false]);

    Livewire::actingAs($cashier)
        ->test(CanteenPos::class)
        ->call('addToCart', $product->id)
        ->assertSet('cart', []);
});
