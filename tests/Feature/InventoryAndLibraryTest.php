<?php

use App\Livewire\Inventory\InventoryIndex;
use App\Livewire\Inventory\RoomIndex;
use App\Livewire\Library\BookIndex;
use App\Livewire\Library\CirculationIndex;
use App\Models\InventoryItem;
use App\Models\InventoryRoom;
use App\Models\LibraryBook;
use App\Models\LibraryLoan;
use App\Models\Student;
use App\Models\User;
use App\Services\LibraryCirculationService;
use Carbon\Carbon;
use Database\Seeders\PermissionSeeder;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

test('admin can manage inventory rooms and view item valuations', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $room = InventoryRoom::factory()->create([
        'name' => 'Laboratorium Robotik',
        'code' => 'R-LAB-IPA',
    ]);

    InventoryItem::factory()->create([
        'inventory_room_id' => $room->id,
        'name' => 'Mikroskop Olympus CX23',
        'price' => 5000000,
        'quantity' => 2,
    ]);

    Livewire::actingAs($admin)
        ->test(RoomIndex::class)
        ->assertSee('Laboratorium Robotik')
        ->assertSee('R-LAB-IPA')
        ->set('code', 'R-PERPUS-01')
        ->set('name', 'Perpustakaan Lt. 1')
        ->set('building', 'Gedung Umar')
        ->call('save')
        ->assertHasNoErrors();

    expect(InventoryRoom::where('code', 'R-PERPUS-01')->exists())->toBeTrue();
});

test('inventory item creation generates sequential code and QR label token', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $room = InventoryRoom::factory()->create(['name' => 'Ruang Guru']);

    Livewire::actingAs($admin)
        ->test(InventoryIndex::class)
        ->set('inventory_room_id', $room->id)
        ->set('name', 'Printer Epson L3210')
        ->set('category', 'elektronik')
        ->set('brand', 'Epson')
        ->set('condition', 'baik')
        ->set('price', 2500000)
        ->set('quantity', 1)
        ->set('unit', 'unit')
        ->call('save')
        ->assertHasNoErrors();

    $item = InventoryItem::where('name', 'Printer Epson L3210')->first();
    expect($item)->not->toBeNull()
        ->and($item->code)->toStartWith('AST-')
        ->and($item->qr_code_token)->toStartWith('AST-QR-');

    // Test print label HTTP endpoint
    $response = $this->actingAs($admin)->get(route('inventory.print-item-label', $item->id));
    $response->assertOk()
        ->assertSee($item->code)
        ->assertSee('Printer Epson L3210');
});

test('library book catalog can be managed and tracked by copies', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(BookIndex::class)
        ->set('title', 'Safinatun Najah')
        ->set('author', 'Salim bin Sumair Al-Hadhrami')
        ->set('category', 'kitab_kuning')
        ->set('rack_location', 'Rak Fiqih A-01')
        ->set('total_copies', 5)
        ->call('save')
        ->assertHasNoErrors();

    $book = LibraryBook::where('title', 'Safinatun Najah')->first();
    expect($book)->not->toBeNull()
        ->and($book->code)->toStartWith('BK-')
        ->and($book->available_copies)->toBe(5)
        ->and($book->barcode_token)->toStartWith('LIB-');
});

test('circulation service borrows book and decrements available copies', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $book = LibraryBook::factory()->create([
        'title' => 'Fathul Qarib Al-Mujib',
        'total_copies' => 3,
        'available_copies' => 3,
    ]);

    $student = Student::factory()->create(['name' => 'Ahmad Faiz']);

    /** @var LibraryCirculationService $circulationService */
    $circulationService = app(LibraryCirculationService::class);

    $loan = $circulationService->borrowBook(
        book: $book,
        borrowerType: 'student',
        borrowerId: $student->id,
        loanDays: 7,
        processedById: $admin->id
    );

    expect($loan)->not->toBeNull()
        ->and($loan->loan_code)->toStartWith('PIN-')
        ->and($loan->status)->toBe('active')
        ->and($book->fresh()->available_copies)->toBe(2);

    // Pinjam sampai batas stok habis
    $circulationService->borrowBook($book, 'student', Student::factory()->create()->id, 7);
    $circulationService->borrowBook($book, 'student', Student::factory()->create()->id, 7);
    expect($book->fresh()->available_copies)->toBe(0);

    // Peminjaman saat stok habis harus melempar ValidationException
    expect(fn () => $circulationService->borrowBook($book, 'student', Student::factory()->create()->id, 7))
        ->toThrow(ValidationException::class);
});

test('circulation service calculates overdue fine accurately on book return', function () {
    $book = LibraryBook::factory()->create([
        'total_copies' => 2,
        'available_copies' => 1,
    ]);

    $student = Student::factory()->create(['name' => 'Salman Al-Farisi']);

    // Pinjaman yang terlambat 4 hari
    $borrowDate = Carbon::today()->subDays(11);
    $dueDate = Carbon::today()->subDays(4);

    $loan = LibraryLoan::create([
        'loan_code' => 'PIN-2026-9999',
        'library_book_id' => $book->id,
        'borrower_type' => 'student',
        'student_id' => $student->id,
        'borrow_date' => $borrowDate,
        'due_date' => $dueDate,
        'status' => 'active',
        'fine_amount' => 0,
    ]);

    /** @var LibraryCirculationService $service */
    $service = app(LibraryCirculationService::class);

    // Hitung denda: 4 hari x Rp 1.000 = Rp 4.000
    $fine = $service->calculateFine($loan, Carbon::today());
    expect($fine)->toBe(4000.0);

    // Proses pengembalian buku
    $returnedLoan = $service->returnBook($loan, notes: 'Kondisi buku baik, denda lunas tunai.');

    expect($returnedLoan->status)->toBe('returned')
        ->and(Carbon::parse($returnedLoan->return_date)->toDateString())->toBe(Carbon::today()->toDateString())
        ->and((float) $returnedLoan->fine_amount)->toBe(4000.0)
        ->and($book->fresh()->available_copies)->toBe(2);
});

test('circulation desk Livewire interface allows recording loans and processing returns', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $book = LibraryBook::factory()->create([
        'title' => 'Nahwu Wadhih Jilid 1',
        'total_copies' => 4,
        'available_copies' => 4,
    ]);

    $student = Student::factory()->create(['name' => 'Usamah bin Zaid']);

    Livewire::actingAs($admin)
        ->test(CirculationIndex::class)
        ->call('openLoanModal')
        ->assertSet('showLoanModal', true)
        ->set('selectedBookId', $book->id)
        ->set('borrowerType', 'student')
        ->set('selectedStudentId', $student->id)
        ->set('loanDays', 7)
        ->call('saveLoan')
        ->assertSet('showLoanModal', false);

    expect($book->fresh()->available_copies)->toBe(3);

    $loan = LibraryLoan::where('student_id', $student->id)->latest()->first();
    expect($loan)->not->toBeNull();

    // Buka modal pengembalian dan terima buku
    Livewire::actingAs($admin)
        ->test(CirculationIndex::class)
        ->call('openReturnModal', $loan->id)
        ->assertSet('showReturnModal', true)
        ->set('customFine', 0)
        ->call('processReturn')
        ->assertSet('showReturnModal', false);

    expect($loan->fresh()->status)->toBe('returned')
        ->and($book->fresh()->available_copies)->toBe(4);
});

test('the same borrower cannot borrow a book twice before returning it', function () {
    $student = Student::factory()->create();
    $book = LibraryBook::factory()->create(['total_copies' => 3, 'available_copies' => 3]);
    $service = app(LibraryCirculationService::class);

    $service->borrowBook($book, 'student', $student->id);

    expect(fn () => $service->borrowBook($book, 'student', $student->id))->toThrow(ValidationException::class, 'masih meminjam');
    expect($book->fresh()->available_copies)->toBe(2);
});
