<?php

namespace App\Livewire\Admissions;

use App\Livewire\Concerns\WithCrudActions;
use App\Models\AcademicYear;
use App\Models\AdmissionBatch;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app', ['heading' => 'Gelombang PPDB'])]
#[Title('Gelombang PPDB')]
class AdmissionBatches extends Component
{
    use WithCrudActions;

    public ?int $academic_year_id = null;

    public string $name = '';

    public int $wave_number = 1;

    public string $start_date = '';

    public string $end_date = '';

    public int $quota = 100;

    public float $registration_fee = 250000;

    public ?string $description = null;

    public bool $is_active = true;

    public function mount(): void
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        if ($activeYear) {
            $this->academic_year_id = $activeYear->id;
        }

        $this->start_date = now()->toDateString();
        $this->end_date = now()->addMonths(2)->toDateString();
    }

    protected function modelClass(): string
    {
        return AdmissionBatch::class;
    }

    protected function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'name' => ['required', 'string', 'max:150'],
            'wave_number' => ['required', 'integer', 'min:1', 'max:10'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'quota' => ['required', 'integer', 'min:1', 'max:1000'],
            'registration_fee' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @param  AdmissionBatch  $record
     */
    protected function fillForm($record): void
    {
        $this->academic_year_id = $record->academic_year_id;
        $this->name = $record->name;
        $this->wave_number = $record->wave_number;
        $this->start_date = $record->start_date instanceof \DateTimeInterface ? $record->start_date->format('Y-m-d') : (string) $record->start_date;
        $this->end_date = $record->end_date instanceof \DateTimeInterface ? $record->end_date->format('Y-m-d') : (string) $record->end_date;
        $this->quota = $record->quota;
        $this->registration_fee = (float) $record->registration_fee;
        $this->description = $record->description;
        $this->is_active = (bool) $record->is_active;
    }

    protected function resetForm(): void
    {
        $this->name = '';
        $this->wave_number = 1;
        $this->start_date = now()->toDateString();
        $this->end_date = now()->addMonths(2)->toDateString();
        $this->quota = 100;
        $this->registration_fee = 250000;
        $this->description = null;
        $this->is_active = true;

        if (! $this->academic_year_id) {
            $this->academic_year_id = AcademicYear::where('is_active', true)->first()?->id;
        }
    }

    public function save(): void
    {
        $this->authorizeManage();
        $validated = $this->validate();

        $isEditing = (bool) $this->editingId;
        if ($isEditing) {
            AdmissionBatch::findOrFail($this->editingId)->update($validated);
        } else {
            AdmissionBatch::create($validated);
        }

        $this->closeModal();
        $this->resetPage();
        $this->notify($isEditing ? __('Gelombang PPDB diperbarui.') : __('Gelombang PPDB baru ditambahkan.'));
    }

    public function toggleActive(int $batchId): void
    {
        $this->authorizeManage();
        $batch = AdmissionBatch::findOrFail($batchId);
        $batch->update(['is_active' => ! $batch->is_active]);
        $this->notify(__('Status aktif gelombang berhasil diperbarui.'));
    }

    protected function authorizeManage(): void
    {
        abort_unless(auth()->user()->can('admissions.manage') || auth()->user()->hasRole('superadmin'), 403);
    }

    public function render()
    {
        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        $query = AdmissionBatch::query()
            ->with('academicYear')
            ->withCount(['candidates'])
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderByDesc('start_date');

        $batches = $query->paginate($this->perPage);

        return view('livewire.admissions.admission-batches', [
            'batches' => $batches,
            'academicYears' => $academicYears,
        ]);
    }
}
