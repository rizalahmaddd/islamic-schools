<?php

namespace App\Livewire\Boarding;

use App\Livewire\Concerns\WithDataTable;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\StudentLeaveRequest;
use App\Services\BoardingLeaveService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app', ['heading' => 'Perizinan Keluar Santri'])]
#[Title('Perizinan Keluar Santri')]
class StudentLeaves extends Component
{
    use WithDataTable;

    public string $search = '';

    public string $statusFilter = '';

    public string $leaveTypeFilter = '';

    // Create Modal state
    public bool $showCreateModal = false;

    public ?int $student_id = null;

    public string $leave_type = 'weekend';

    public string $departure_plan = '';

    public string $return_plan = '';

    public string $reason = '';

    public ?string $pickup_person = null;

    public ?string $notes = null;

    // Reject Modal state
    public bool $showRejectModal = false;

    public ?int $rejectingLeaveId = null;

    public string $rejection_reason = '';

    // QR Ticket Modal state
    public bool $showTicketModal = false;

    public ?StudentLeaveRequest $activeTicketLeave = null;

    public function mount(): void
    {
        $this->departure_plan = now()->addDay()->setTime(8, 0)->format('Y-m-d\TH:i');
        $this->return_plan = now()->addDays(2)->setTime(17, 0)->format('Y-m-d\TH:i');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingLeaveTypeFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->authorizeManage();
        $this->resetValidation();
        $this->student_id = null;
        $this->leave_type = 'weekend';
        $this->reason = '';
        $this->pickup_person = null;
        $this->notes = null;
        $this->departure_plan = now()->addDay()->setTime(8, 0)->format('Y-m-d\TH:i');
        $this->return_plan = now()->addDays(2)->setTime(17, 0)->format('Y-m-d\TH:i');
        $this->showCreateModal = true;
        $this->dispatch('open-modal', 'leave-create-form');
    }

    public function saveLeave(BoardingLeaveService $service): void
    {
        $this->authorizeManage();
        $validated = $this->validate([
            'student_id' => ['required', 'exists:students,id'],
            'leave_type' => ['required', 'in:weekend,sick,family,dispensation'],
            'departure_plan' => ['required', 'date'],
            'return_plan' => ['required', 'date', 'after:departure_plan'],
            'reason' => ['required', 'string', 'max:1000'],
            'pickup_person' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $student = Student::findOrFail($this->student_id);
        $service->createLeaveRequest($student, $validated, auth()->user());

        $this->showCreateModal = false;
        $this->dispatch('close-modal', 'leave-create-form');
        $this->resetPage();
        $this->dispatch('notify', 'Pengajuan izin santri berhasil dibuat dengan nomor tiket gate pass.');
    }

    public function approve(int $leaveId, BoardingLeaveService $service): void
    {
        $this->authorizeManage();
        $leave = StudentLeaveRequest::findOrFail($leaveId);
        $service->approveLeave($leave, auth()->user());

        $this->dispatch('notify', 'Izin santri '.$leave->leave_number.' berhasil disetujui. Notifikasi telah dikirimkan ke wali.');
    }

    public function openRejectModal(int $leaveId): void
    {
        $this->authorizeManage();
        $this->rejectingLeaveId = $leaveId;
        $this->rejection_reason = '';
        $this->showRejectModal = true;
        $this->dispatch('open-modal', 'reject-modal');
    }

    public function confirmReject(BoardingLeaveService $service): void
    {
        $this->authorizeManage();
        $this->validate([
            'rejection_reason' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $leave = StudentLeaveRequest::findOrFail($this->rejectingLeaveId);
        $service->rejectLeave($leave, auth()->user(), $this->rejection_reason);

        $this->showRejectModal = false;
        $this->dispatch('close-modal', 'reject-modal');
        $this->dispatch('notify', 'Pengajuan izin santri telah ditolak.');
    }

    public function viewTicket(int $leaveId): void
    {
        $this->activeTicketLeave = StudentLeaveRequest::with(['student.guardian', 'student.dormPlacements.room.building'])->findOrFail($leaveId);
        $this->showTicketModal = true;
        $this->dispatch('open-modal', 'ticket-modal');
    }

    protected function authorizeManage(): void
    {
        abort_unless(auth()->user()->can('boarding.manage') || auth()->user()->hasRole('superadmin'), 403);
    }

    public function render()
    {
        $activeYear = AcademicYear::where('is_active', true)->first();

        $query = StudentLeaveRequest::query()
            ->with(['student.guardian', 'student.dormPlacements.room.building', 'approver'])
            ->when($this->search, function ($q) {
                $q->where(function ($search) {
                    $search->where('leave_number', 'like', "%{$this->search}%")
                        ->orWhere('reason', 'like', "%{$this->search}%")
                        ->orWhere('pickup_person', 'like', "%{$this->search}%")
                        ->orWhereHas('student', fn ($s) => $s->where('name', 'like', "%{$this->search}%")->orWhere('nis', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->leaveTypeFilter, fn ($q) => $q->where('leave_type', $this->leaveTypeFilter))
            ->latest('id');

        $leaves = $query->paginate($this->perPage);

        $students = Student::where('status', 'active')->orderBy('name')->get(['id', 'name', 'nis']);

        return view('livewire.boarding.student-leaves', [
            'leaves' => $leaves,
            'students' => $students,
            'activeYear' => $activeYear,
        ]);
    }
}
