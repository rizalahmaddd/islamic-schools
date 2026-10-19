<?php

namespace App\Services;

use App\Models\LibraryBook;
use App\Models\LibraryLoan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LibraryCirculationService
{
    public const DEFAULT_FINE_PER_DAY = 1000.0;

    public function __construct(
        protected DocumentNumberGenerator $docGenerator,
    ) {}

    /**
     * Meminjam buku perpustakaan untuk santri atau guru.
     *
     * @throws ValidationException
     */
    public function borrowBook(
        LibraryBook $book,
        string $borrowerType,
        int $borrowerId,
        int $loanDays = 7,
        ?int $processedById = null,
        ?string $notes = null
    ): LibraryLoan {
        return DB::transaction(function () use ($book, $borrowerType, $borrowerId, $loanDays, $processedById, $notes) {
            // Lock book row to prevent race condition on copy availability
            /** @var LibraryBook $lockedBook */
            $lockedBook = LibraryBook::query()->whereKey($book->id)->lockForUpdate()->firstOrFail();

            if ($lockedBook->available_copies <= 0) {
                throw ValidationException::withMessages([
                    'book_id' => "Stok buku [{$lockedBook->title}] sedang habis / seluruh eksemplar sedang dipinjam.",
                ]);
            }

            $alreadyBorrowed = LibraryLoan::query()
                ->where('library_book_id', $lockedBook->id)
                ->whereIn('status', ['active', 'overdue'])
                ->where($borrowerType === 'student' ? 'student_id' : 'teacher_id', $borrowerId)
                ->exists();

            if ($alreadyBorrowed) {
                throw ValidationException::withMessages([
                    'borrower_id' => "Peminjam ini masih meminjam [{$lockedBook->title}] dan belum mengembalikannya.",
                ]);
            }

            // Validasi batas peminjaman aktif per santri (maks 3 buku)
            if ($borrowerType === 'student') {
                $activeCount = LibraryLoan::query()
                    ->where('student_id', $borrowerId)
                    ->whereIn('status', ['active', 'overdue'])
                    ->count();

                if ($activeCount >= 3) {
                    throw ValidationException::withMessages([
                        'borrower_id' => 'Santri ini telah mencapai batas maksimal 3 peminjaman buku aktif.',
                    ]);
                }
            }

            $loanCode = $this->docGenerator->next(prefix: 'PIN', pad: 4);
            $borrowDate = Carbon::today();
            $dueDate = Carbon::today()->addDays(max(1, $loanDays));

            $loan = LibraryLoan::create([
                'loan_code' => $loanCode,
                'library_book_id' => $lockedBook->id,
                'borrower_type' => $borrowerType,
                'student_id' => $borrowerType === 'student' ? $borrowerId : null,
                'teacher_id' => $borrowerType === 'teacher' ? $borrowerId : null,
                'borrow_date' => $borrowDate,
                'due_date' => $dueDate,
                'return_date' => null,
                'fine_amount' => 0,
                'status' => 'active',
                'notes' => $notes,
                'processed_by' => $processedById,
            ]);

            $lockedBook->decrement('available_copies');

            return $loan;
        });
    }

    /**
     * Pengembalian buku perpustakaan dan kalkulasi denda keterlambatan otomatis.
     *
     * @throws ValidationException
     */
    public function returnBook(
        LibraryLoan $loan,
        ?float $customFine = null,
        ?string $notes = null
    ): LibraryLoan {
        return DB::transaction(function () use ($loan, $customFine, $notes) {
            /** @var LibraryLoan $lockedLoan */
            $lockedLoan = LibraryLoan::query()->whereKey($loan->id)->lockForUpdate()->firstOrFail();

            if ($lockedLoan->status === 'returned') {
                throw ValidationException::withMessages([
                    'loan_id' => 'Peminjaman buku ini sudah tercatat dikembalikan.',
                ]);
            }

            $today = Carbon::today();
            $fine = max(0.0, $customFine ?? $this->calculateFine($lockedLoan, $today));

            $lockedLoan->update([
                'return_date' => $today,
                'fine_amount' => $fine,
                'status' => 'returned',
                'notes' => $notes ?? $lockedLoan->notes,
            ]);

            // Kembalikan stok buku
            $lockedLoan->book()->increment('available_copies');

            return $lockedLoan->fresh(['book', 'student', 'teacher']);
        });
    }

    /**
     * Hitung denda keterlambatan (Rp 1.000 / hari terlambat).
     */
    public function calculateFine(LibraryLoan $loan, ?Carbon $asOf = null): float
    {
        $asOf ??= Carbon::today();
        $due = Carbon::parse($loan->due_date)->startOfDay();
        $current = $asOf->copy()->startOfDay();

        if ($current->lte($due)) {
            return 0.0;
        }

        $overdueDays = $due->diffInDays($current);

        return (float) ($overdueDays * self::DEFAULT_FINE_PER_DAY);
    }
}
