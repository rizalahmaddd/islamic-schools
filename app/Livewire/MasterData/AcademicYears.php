<?php

namespace App\Livewire\MasterData;

use App\Livewire\Concerns\WithCrudActions;
use App\Models\AcademicYear;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app', ['heading' => 'Tahun Ajaran'])]
#[Title('Tahun Ajaran')]
class AcademicYears extends Component
{
    use WithCrudActions;

    public string $name = '';

    public string $semester = 'ganjil';

    public string $start_date = '';

    public string $end_date = '';

    public bool $is_active = false;

    public function save(): void
    {
        $this->authorizeManage();

        $validated = $this->validate();
        $isEditing = (bool) $this->editingId;

        if ($isEditing) {
            $record = AcademicYear::findOrFail($this->editingId);
            $record->update($validated);
            if ($this->is_active) {
                $record->activate();
            }
        } else {
            $record = AcademicYear::create($validated);
            if ($this->is_active) {
                $record->activate();
            }
        }

        $this->closeModal();
        $this->resetPage();
        $this->notify($isEditing ? __('Tahun ajaran diperbarui.') : __('Tahun ajaran ditambahkan.'));
    }

    public function activateYear(int $id): void
    {
        $this->authorizeManage();

        $record = AcademicYear::findOrFail($id);
        $record->activate();

        $this->notify(__("Tahun ajaran {$record->name} ({$record->semester}) berhasil diaktifkan."));
    }

    public function export(string $format = 'xlsx')
    {
        $query = AcademicYear::query()
            ->when($this->search, function ($query) {
                $query->where(function ($search) {
                    $search->where('name', 'like', "%{$this->search}%")
                        ->orWhere('semester', 'like', "%{$this->search}%");
                });
            });

        $this->applySorting($query, [
            'name' => 'name',
            'semester' => 'semester',
            'start_date' => 'start_date',
            'end_date' => 'end_date',
            'is_active' => 'is_active',
        ], 'start_date', 'desc');

        $headers = ['Tahun Ajaran', 'Semester', 'Tanggal Mulai', 'Tanggal Selesai', 'Status'];

        $rows = $query->get()->map(fn (AcademicYear $item) => [
            $item->name,
            ucfirst((string) $item->semester),
            $item->start_date?->format('d/m/Y') ?? '-',
            $item->end_date?->format('d/m/Y') ?? '-',
            $item->is_active ? 'Aktif' : 'Nonaktif',
        ]);

        return $this->exportFormattedResponse('tahun-ajaran', $headers, $rows, 'Data Tahun Ajaran', 'Daftar tahun ajaran dan semester sekolah', $format);
    }

    protected function guardDelete($record): ?string
    {
        if ($record->is_active) {
            return __('Tahun ajaran aktif tidak bisa dihapus. Aktifkan tahun ajaran lain terlebih dahulu.');
        }

        if ($record->classes()->exists() || $record->studentBills()->exists()) {
            return __('Tahun ajaran ini sudah punya kelas atau tagihan, jadi tidak bisa dihapus.');
        }

        return null;
    }

    public function render()
    {
        $query = AcademicYear::query()
            ->when($this->search, function ($query) {
                $query->where(function ($search) {
                    $search->where('name', 'like', "%{$this->search}%")
                        ->orWhere('semester', 'like', "%{$this->search}%");
                });
            });

        $this->applySorting($query, [
            'name' => 'name',
            'semester' => 'semester',
            'start_date' => 'start_date',
            'end_date' => 'end_date',
            'is_active' => 'is_active',
        ], 'start_date', 'desc');

        return view('livewire.master-data.academic-years', [
            'academicYears' => $query->paginate($this->perPage),
        ]);
    }

    protected function modelClass(): string
    {
        return AcademicYear::class;
    }

    protected function resetForm(): void
    {
        $this->reset(['name', 'start_date', 'end_date']);
        $this->semester = 'ganjil';
        $this->is_active = false;
    }

    protected function fillForm($record): void
    {
        $this->name = $record->name;
        $this->semester = (string) $record->semester;
        $this->start_date = $record->start_date?->format('Y-m-d') ?? '';
        $this->end_date = $record->end_date?->format('Y-m-d') ?? '';
        $this->is_active = (bool) $record->is_active;
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'semester' => ['required', 'in:ganjil,genap'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_active' => ['boolean'],
        ];
    }
}
