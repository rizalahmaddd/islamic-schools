<?php

namespace App\Livewire\Islamic;

use App\Livewire\Concerns\WithDataTable;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentConductRecord;
use App\Services\TahfidzService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app', ['heading' => 'Poin Adab & Disiplin'])]
#[Title('Poin Adab & Disiplin')]
class ConductRecords extends Component
{
    use WithDataTable;

    public string $search = '';

    public string $typeFilter = 'all'; // all, violation, merit

    public ?int $classFilter = null;

    // Form fields
    public bool $showModal = false;

    public ?int $editingId = null;

    public ?int $confirmingDeleteId = null;

    public ?int $student_id = null;

    public string $date = '';

    public string $type = 'violation'; // violation, merit

    public int $points = 10;

    public string $category = 'Kedisiplinan & Waktu';

    public string $description = '';

    public string $action_taken = '';

    public bool $send_wa = true;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('view-conduct') || auth()->user()->can('manage-conduct'), 403);

        $this->date = now()->toDateString();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingClassFilter(): void
    {
        $this->resetPage();
    }

    public function canManage(): bool
    {
        return auth()->user()->can('manage-conduct');
    }

    public function openCreateModal(): void
    {
        abort_unless($this->canManage(), 403);

        $this->resetValidation();
        $this->editingId = null;
        $this->student_id = null;
        $this->date = now()->toDateString();
        $this->type = 'violation';
        $this->points = 10;
        $this->category = 'Kedisiplinan & Waktu';
        $this->description = '';
        $this->action_taken = '';
        $this->send_wa = true;

        $this->showModal = true;
        $this->dispatch('open-modal', 'conduct-form');
    }

    public function openEditModal(int $id): void
    {
        abort_unless($this->canManage(), 403);

        $this->resetValidation();
        $record = StudentConductRecord::findOrFail($id);
        $this->editingId = $id;
        $this->student_id = $record->student_id;
        $this->date = $record->date instanceof \DateTimeInterface
            ? $record->date->format('Y-m-d')
            : substr((string) $record->date, 0, 10);
        $this->type = $record->type;
        $this->points = $record->points;
        $this->category = $record->category;
        $this->description = $record->description;
        $this->action_taken = $record->action_taken ?? '';
        $this->send_wa = false;

        $this->showModal = true;
        $this->dispatch('open-modal', 'conduct-form');
    }

    public function save(): void
    {
        abort_unless($this->canManage(), 403);

        $this->validate([
            'student_id' => ['required', 'exists:students,id'],
            'date' => ['required', 'date'],
            'type' => ['required', 'in:violation,merit'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:1000'],
            'action_taken' => ['nullable', 'string', 'max:1000'],
        ]);

        $record = StudentConductRecord::updateOrCreate(
            ['id' => $this->editingId],
            [
                'student_id' => $this->student_id,
                'date' => $this->date,
                'type' => $this->type,
                'points' => $this->points,
                'category' => $this->category,
                'description' => $this->description,
                'action_taken' => $this->action_taken ?: null,
                'recorded_by' => auth()->id(),
            ]
        );

        if ($this->send_wa) {
            app(TahfidzService::class)->sendConductNotification($record);
        }

        $this->showModal = false;
        $this->dispatch('close-modal', 'conduct-form');
        $this->dispatch('notify', message: 'Catatan adab & kedisiplinan berhasil disimpan.', type: 'success');
    }

    public function confirmDelete(int $id): void
    {
        abort_unless($this->canManage(), 403);

        $this->confirmingDeleteId = $id;
        $this->dispatch('open-modal', 'confirm-delete');
    }

    public function delete(): void
    {
        abort_unless($this->canManage(), 403);

        if ($this->confirmingDeleteId) {
            StudentConductRecord::findOrFail($this->confirmingDeleteId)->delete();
            $this->confirmingDeleteId = null;
            $this->dispatch('close-modal', 'confirm-delete');
            $this->dispatch('notify', message: 'Data berhasil dihapus.', type: 'success');
        }
    }

    /**
     * @return Builder<StudentConductRecord>
     */
    protected function buildQuery(): Builder
    {
        /** @var Builder<StudentConductRecord> $query */
        $query = StudentConductRecord::query()
            ->with(['student.currentEnrollment.schoolClass', 'recordedBy'])
            ->when($this->search !== '', function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('description', 'like', $term)
                        ->orWhere('category', 'like', $term)
                        ->orWhereHas('student', fn ($sq) => $sq->where('name', 'like', $term)->orWhere('nis', 'like', $term));
                });
            })
            ->when($this->typeFilter !== 'all', fn ($q) => $q->where('type', $this->typeFilter))
            ->when($this->classFilter, function ($q) {
                $q->whereHas('student.enrollments', fn ($sq) => $sq->where('class_id', $this->classFilter)->where('status', 'active'));
            })
            ->orderByDesc('date')
            ->orderByDesc('id');

        return $query;
    }

    public function render(): View
    {
        $records = $this->buildQuery()->paginate(15);
        $schoolClasses = SchoolClass::query()->orderBy('grade_level')->orderBy('name')->get();
        $allStudents = Student::query()->where('status', 'active')->orderBy('name')->get();

        $totalViolations = StudentConductRecord::query()->where('type', 'violation')->count();
        $totalMerits = StudentConductRecord::query()->where('type', 'merit')->count();

        return view('livewire.islamic.conduct-records', [
            'records' => $records,
            'schoolClasses' => $schoolClasses,
            'allStudents' => $allStudents,
            'totalViolations' => $totalViolations,
            'totalMerits' => $totalMerits,
        ]);
    }
}
