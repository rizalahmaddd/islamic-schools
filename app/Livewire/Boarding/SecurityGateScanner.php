<?php

namespace App\Livewire\Boarding;

use App\Models\StudentLeaveRequest;
use App\Services\BoardingLeaveService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app', ['heading' => 'Scanner Gerbang Satpam'])]
#[Title('Scanner Pos Satpam')]
class SecurityGateScanner extends Component
{
    public string $qr_token_input = '';

    public ?StudentLeaveRequest $scannedLeave = null;

    public ?string $scanFeedback = null;

    public ?string $feedbackType = null; // 'success' | 'warning' | 'error'

    public ?string $notes = null;

    public function mount(): void
    {
        $this->authorizeScanner();
    }

    public function checkToken(string $token): void
    {
        $this->resetFeedback();
        $trimmed = trim($token);

        if (empty($trimmed)) {
            return;
        }

        // Cari berdasarkan qr_pass_token atau leave_number
        $leave = StudentLeaveRequest::with(['student.guardian', 'student.dormPlacements.room.building'])
            ->where('qr_pass_token', $trimmed)
            ->orWhere('leave_number', $trimmed)
            ->first();

        if (! $leave) {
            $this->scannedLeave = null;
            $this->scanFeedback = 'Tiket Gate Pass tidak ditemukan. Pastikan QR code valid atau masukkan nomor izin dengan benar.';
            $this->feedbackType = 'error';

            return;
        }

        $this->scannedLeave = $leave;
        $this->qr_token_input = $leave->qr_pass_token;

        if ($leave->isPending()) {
            $this->scanFeedback = 'PERHATIAN: Izin santri ini masih berstatus MENUNGGU PERSETUJUAN dan belum boleh keluar gerbang.';
            $this->feedbackType = 'warning';
        } elseif ($leave->isRejected()) {
            $this->scanFeedback = 'DITOLAK: Izin santri ini telah DITOLAK oleh pembina: '.$leave->rejection_reason;
            $this->feedbackType = 'error';
        } elseif ($leave->isReturned()) {
            $this->scanFeedback = 'INFORMASI: Santri ini sudah tercatat kembali ke pondok pada '.$leave->actual_returned_at?->format('d/m/Y H:i').'.';
            $this->feedbackType = 'warning';
        }
    }

    public function confirmExit(BoardingLeaveService $service): void
    {
        $this->authorizeScanner();

        if (! $this->scannedLeave) {
            return;
        }

        try {
            $result = $service->scanSecurityExit($this->scannedLeave->qr_pass_token, auth()->user());
            $this->scannedLeave->refresh();
            $this->scanFeedback = $result['message'];
            $this->feedbackType = 'success';
            $this->dispatch('play-sound', 'success');
        } catch (ValidationException $e) {
            $this->scanFeedback = collect($e->errors())->flatten()->first();
            $this->feedbackType = 'error';
        }
    }

    public function confirmReturn(BoardingLeaveService $service): void
    {
        $this->authorizeScanner();

        if (! $this->scannedLeave) {
            return;
        }

        try {
            $result = $service->scanSecurityReturn($this->scannedLeave->qr_pass_token, auth()->user(), $this->notes);
            $this->scannedLeave->refresh();
            $this->scanFeedback = $result['message'];
            $this->feedbackType = $result['is_late'] ? 'warning' : 'success';
            $this->notes = null;
            $this->dispatch('play-sound', $result['is_late'] ? 'warning' : 'success');
        } catch (ValidationException $e) {
            $this->scanFeedback = collect($e->errors())->flatten()->first();
            $this->feedbackType = 'error';
        }
    }

    public function resetScanner(): void
    {
        $this->qr_token_input = '';
        $this->scannedLeave = null;
        $this->notes = null;
        $this->resetFeedback();
    }

    protected function resetFeedback(): void
    {
        $this->scanFeedback = null;
        $this->feedbackType = null;
    }

    protected function authorizeScanner(): void
    {
        abort_unless(
            auth()->user()->can('boarding.gate-pass.scan')
            || auth()->user()->hasRole(['satpam', 'musyrif', 'admin', 'superadmin']),
            403
        );
    }

    public function render()
    {
        // Riwayat scan gerbang hari ini
        $todayGateActivity = StudentLeaveRequest::query()
            ->with(['student.guardian', 'student.dormPlacements.room.building'])
            ->where(function ($q) {
                $q->whereDate('actual_departure_at', now()->toDateString())
                    ->orWhereDate('actual_returned_at', now()->toDateString());
            })
            ->latest('updated_at')
            ->limit(20)
            ->get();

        return view('livewire.boarding.security-gate-scanner', [
            'todayGateActivity' => $todayGateActivity,
        ]);
    }
}
