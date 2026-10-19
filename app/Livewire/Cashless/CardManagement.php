<?php

namespace App\Livewire\Cashless;

use App\Livewire\Concerns\WithDataTable;
use App\Models\RfidCard;
use App\Models\Student;
use App\Services\StudentWalletService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app', ['heading' => 'Kartu RFID & Dompet Santri'])]
#[Title('Kartu RFID & Saldo Santri')]
class CardManagement extends Component
{
    use WithDataTable;

    public string $search = '';

    public string $statusFilter = ''; // '' | 'has_card' | 'no_card' | 'blocked'

    // Modal Registrasi Kartu Baru
    public bool $showCardModal = false;

    public ?int $selectedStudentId = null;

    public string $cardUid = '';

    public string $cardLabel = 'Kartu Utama';

    // Modal Top-up Saldo
    public bool $showTopupModal = false;

    public ?int $topupStudentId = null;

    public float $topupAmount = 50000;

    public ?string $topupNotes = null;

    // Modal Atur Limit Harian
    public bool $showLimitModal = false;

    public ?int $limitStudentId = null;

    public float $newDailyLimit = 50000;

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
        return auth()->user()?->can('manage-cashless') || auth()->user()?->hasRole(['admin', 'superadmin', 'tata_usaha']);
    }

    // --- Modal Registrasi Kartu ---
    public function openCardModal(?int $studentId = null): void
    {
        abort_unless($this->canManage(), 403);

        $this->resetValidation();
        $this->selectedStudentId = $studentId;
        $this->cardUid = '';
        $this->cardLabel = 'Kartu Utama';
        $this->showCardModal = true;
        $this->dispatch('open-modal', 'card-modal');
    }

    public function closeCardModal(): void
    {
        $this->showCardModal = false;
        $this->resetValidation();
        $this->dispatch('close-modal', 'card-modal');
    }

    public function saveCard(StudentWalletService $service): void
    {
        abort_unless($this->canManage(), 403);

        $this->validate([
            'selectedStudentId' => ['required', 'exists:students,id'],
            'cardUid' => ['required', 'string', 'max:50', Rule::unique('rfid_cards', 'card_uid')],
            'cardLabel' => ['required', 'string', 'max:50'],
        ]);

        $student = Student::findOrFail($this->selectedStudentId);
        $service->registerCard($student, $this->cardUid, $this->cardLabel);

        $this->dispatch('notify', message: __('Kartu Smart Card RFID berhasil didaftarkan untuk santri.'));
        $this->closeCardModal();
    }

    // --- Modal Top-Up Saldo ---
    public function openTopupModal(int $studentId): void
    {
        abort_unless($this->canManage(), 403);

        $this->resetValidation();
        $this->topupStudentId = $studentId;
        $this->topupAmount = 50000;
        $this->topupNotes = 'Isi ulang saldo tunai POS';
        $this->showTopupModal = true;
        $this->dispatch('open-modal', 'topup-modal');
    }

    public function closeTopupModal(): void
    {
        $this->showTopupModal = false;
        $this->topupStudentId = null;
        $this->resetValidation();
        $this->dispatch('close-modal', 'topup-modal');
    }

    public function saveTopup(StudentWalletService $service): void
    {
        abort_unless($this->canManage(), 403);

        $this->validate([
            'topupStudentId' => ['required', 'exists:students,id'],
            'topupAmount' => ['required', 'numeric', 'min:1000', 'max:10000000'],
            'topupNotes' => ['nullable', 'string', 'max:255'],
        ]);

        $student = Student::findOrFail($this->topupStudentId);
        $wallet = $service->getOrCreateWallet($student);

        $service->topUp(
            wallet: $wallet,
            amount: $this->topupAmount,
            notes: $this->topupNotes,
            cashierUserId: auth()->id()
        );

        $this->dispatch('notify', message: __('Pengisian saldo dompet santri berhasil dicatat.'));
        $this->closeTopupModal();
    }

    // --- Modal Atur Limit Harian ---
    public function openLimitModal(int $studentId): void
    {
        abort_unless($this->canManage(), 403);

        $this->resetValidation();
        $this->limitStudentId = $studentId;
        $student = Student::findOrFail($studentId);
        $wallet = app(StudentWalletService::class)->getOrCreateWallet($student);
        $this->newDailyLimit = (float) $wallet->daily_limit;

        $this->showLimitModal = true;
        $this->dispatch('open-modal', 'limit-modal');
    }

    public function closeLimitModal(): void
    {
        $this->showLimitModal = false;
        $this->limitStudentId = null;
        $this->resetValidation();
        $this->dispatch('close-modal', 'limit-modal');
    }

    public function saveLimit(StudentWalletService $service): void
    {
        abort_unless($this->canManage(), 403);

        $this->validate([
            'limitStudentId' => ['required', 'exists:students,id'],
            'newDailyLimit' => ['required', 'numeric', 'min:0', 'max:10000000'],
        ]);

        $student = Student::findOrFail($this->limitStudentId);
        $wallet = $service->getOrCreateWallet($student);

        $service->setDailyLimit($wallet, $this->newDailyLimit);

        $this->dispatch('notify', message: __('Batas limit jajan harian berhasil diperbarui.'));
        $this->closeLimitModal();
    }

    public function toggleBlockCard(int $cardId, StudentWalletService $service): void
    {
        abort_unless($this->canManage(), 403);

        $card = RfidCard::findOrFail($cardId);

        if ($card->is_active) {
            $service->blockCard($card, 'Diblokir oleh administrator / kartu hilang');
            $this->dispatch('notify', message: __('Kartu RFID telah dinonaktifkan / diblokir.'), type: 'warning');
        } else {
            $service->activateCard($card);
            $this->dispatch('notify', message: __('Kartu RFID telah diaktifkan kembali.'));
        }
    }

    public function render(): View
    {
        $students = Student::query()
            ->with(['rfidCards', 'wallet'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('nis', 'like', "%{$this->search}%")
                        ->orWhereHas('rfidCards', fn ($c) => $c->where('card_uid', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->statusFilter === 'has_card', fn ($q) => $q->has('rfidCards'))
            ->when($this->statusFilter === 'no_card', fn ($q) => $q->doesntHave('rfidCards'))
            ->when($this->statusFilter === 'blocked', fn ($q) => $q->whereHas('rfidCards', fn ($c) => $c->where('is_active', false)))
            ->orderBy('name')
            ->paginate($this->perPage);

        $allStudents = Student::orderBy('name')->get(['id', 'name', 'nis']);

        return view('livewire.cashless.card-management', [
            'students' => $students,
            'allStudents' => $allStudents,
        ]);
    }
}
