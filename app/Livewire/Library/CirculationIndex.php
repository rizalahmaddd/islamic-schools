<?php

namespace App\Livewire\Library;

use App\Livewire\Concerns\WithDataTable;
use App\Models\LibraryBook;
use App\Models\LibraryLoan;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\LibraryCirculationService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app', ['heading' => 'Sirkulasi Peminjaman Buku'])]
#[Title('Sirkulasi Perpustakaan')]
class CirculationIndex extends Component
{
    use WithDataTable;

    public string $search = '';

    public string $statusFilter = 'active'; // 'all' | 'active' | 'overdue' | 'returned'

    // Form Peminjaman Baru
    public bool $showLoanModal = false;

    public ?int $selectedBookId = null;

    public string $borrowerType = 'student';

    public ?int $selectedStudentId = null;

    public ?int $selectedTeacherId = null;

    public int $loanDays = 7;

    public ?string $loanNotes = null;

    // Form Pengembalian Buku & Denda
    public bool $showReturnModal = false;

    public ?int $returningLoanId = null;

    public float $calculatedFine = 0;

    public float $customFine = 0;

    public ?string $returnNotes = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function canManage(): bool
    {
        return auth()->user()?->can('manage-library') ?? false;
    }

    public function openLoanModal(): void
    {
        abort_unless($this->canManage(), 403);

        $this->resetValidation();
        $this->selectedBookId = LibraryBook::where('available_copies', '>', 0)->value('id');
        $this->borrowerType = 'student';
        $this->selectedStudentId = null;
        $this->selectedTeacherId = null;
        $this->loanDays = 7;
        $this->loanNotes = null;
        $this->showLoanModal = true;
        $this->dispatch('open-modal', 'loan-modal');
    }

    public function closeLoanModal(): void
    {
        $this->showLoanModal = false;
        $this->resetValidation();
        $this->dispatch('close-modal', 'loan-modal');
    }

    public function saveLoan(LibraryCirculationService $service): void
    {
        abort_unless($this->canManage(), 403);

        $this->validate([
            'selectedBookId' => ['required', 'exists:library_books,id'],
            'borrowerType' => ['required', 'in:student,teacher'],
            'selectedStudentId' => ['required_if:borrowerType,student', 'nullable', 'exists:students,id'],
            'selectedTeacherId' => ['required_if:borrowerType,teacher', 'nullable', 'exists:teachers,id'],
            'loanDays' => ['required', 'integer', 'min:1', 'max:60'],
            'loanNotes' => ['nullable', 'string', 'max:500'],
        ]);

        $book = LibraryBook::findOrFail((int) $this->selectedBookId);
        $borrowerId = $this->borrowerType === 'student' ? (int) $this->selectedStudentId : (int) $this->selectedTeacherId;

        try {
            $service->borrowBook(
                book: $book,
                borrowerType: $this->borrowerType,
                borrowerId: $borrowerId,
                loanDays: $this->loanDays,
                processedById: auth()->id(),
                notes: $this->loanNotes,
            );

            $this->dispatch('notify', message: __('Peminjaman buku berhasil dicatat.'));
            $this->closeLoanModal();
            $this->resetPage();
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->getMessageBag());
        }
    }

    public function openReturnModal(int $loanId, LibraryCirculationService $service): void
    {
        abort_unless($this->canManage(), 403);

        $this->resetValidation();
        $loan = LibraryLoan::with(['book', 'student', 'teacher'])->findOrFail($loanId);
        $this->returningLoanId = $loan->id;

        $fine = $service->calculateFine($loan, Carbon::today());
        $this->calculatedFine = $fine;
        $this->customFine = $fine;
        $this->returnNotes = null;

        $this->showReturnModal = true;
        $this->dispatch('open-modal', 'return-modal');
    }

    public function closeReturnModal(): void
    {
        $this->showReturnModal = false;
        $this->returningLoanId = null;
        $this->resetValidation();
        $this->dispatch('close-modal', 'return-modal');
    }

    public function processReturn(LibraryCirculationService $service): void
    {
        abort_unless($this->canManage(), 403);

        $this->validate([
            'customFine' => ['required', 'numeric', 'min:0'],
            'returnNotes' => ['nullable', 'string', 'max:500'],
        ]);

        if (! $this->returningLoanId) {
            return;
        }

        $loan = LibraryLoan::findOrFail($this->returningLoanId);

        try {
            $service->returnBook(
                loan: $loan,
                customFine: $this->customFine,
                notes: $this->returnNotes,
            );

            $this->dispatch('notify', message: __('Buku berhasil dikembalikan dan stok diperbarui.'));
            $this->closeReturnModal();
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->getMessageBag());
        }
    }

    public function render(): View
    {
        $today = Carbon::today()->toDateString();

        $loans = LibraryLoan::query()
            ->with(['book', 'student', 'teacher', 'processedBy'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('loan_code', 'like', "%{$this->search}%")
                        ->orWhereHas('book', fn ($b) => $b->where('title', 'like', "%{$this->search}%")->orWhere('code', 'like', "%{$this->search}%"))
                        ->orWhereHas('student', fn ($s) => $s->where('name', 'like', "%{$this->search}%")->orWhere('nis', 'like', "%{$this->search}%"))
                        ->orWhereHas('teacher', fn ($t) => $t->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->statusFilter === 'active', function ($query) use ($today) {
                $query->where('status', 'active')->where('due_date', '>=', $today);
            })
            ->when($this->statusFilter === 'overdue', function ($query) use ($today) {
                $query->where(function ($q) use ($today) {
                    $q->where('status', 'overdue')
                        ->orWhere(function ($sub) use ($today) {
                            $sub->where('status', 'active')->where('due_date', '<', $today);
                        });
                });
            })
            ->when($this->statusFilter === 'returned', fn ($q) => $q->where('status', 'returned'))
            ->orderBy($this->sortField === 'id' ? 'created_at' : $this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $availableBooks = LibraryBook::where('available_copies', '>', 0)->orderBy('title')->get(['id', 'title', 'code', 'available_copies']);
        $students = Student::orderBy('name')->get(['id', 'name', 'nis']);
        $teachers = Teacher::orderBy('name')->get(['id', 'name']);

        $returningLoan = $this->returningLoanId ? LibraryLoan::with(['book', 'student', 'teacher'])->find($this->returningLoanId) : null;

        return view('livewire.library.circulation-index', [
            'loans' => $loans,
            'availableBooks' => $availableBooks,
            'students' => $students,
            'teachers' => $teachers,
            'returningLoan' => $returningLoan,
        ]);
    }
}
