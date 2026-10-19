<?php

namespace App\Livewire\MasterData;

use App\Livewire\Concerns\WithCrudActions;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Teacher;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app', ['heading' => 'Kelas & Rombel'])]
#[Title('Kelas & Rombel')]
class SchoolClasses extends Component
{
    use WithCrudActions;

    public ?int $academic_year_id = null;

    public string $name = '';

    public int $grade_level = 7;

    public ?int $homeroom_teacher_id = null;

    public int $capacity = 36;

    public string $academicYearFilter = '';

    public string $gradeFilter = '';

    public function mount(): void
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        if ($activeYear) {
            $this->academicYearFilter = (string) $activeYear->id;
            $this->academic_year_id = $activeYear->id;
        }
    }

    public function save(): void
    {
        $this->authorizeManage();

        $validated = $this->validate();
        $isEditing = (bool) $this->editingId;

        if ($isEditing) {
            SchoolClass::findOrFail($this->editingId)->update($validated);
        } else {
            SchoolClass::create($validated);
        }

        $this->closeModal();
        $this->resetPage();
        $this->notify($isEditing ? __('Data kelas diperbarui.') : __('Kelas baru ditambahkan.'));
    }

    public function export(string $format = 'xlsx')
    {
        $query = SchoolClass::query()
            ->with(['academicYear', 'homeroomTeacher'])
            ->withCount('enrollments')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->academicYearFilter, fn ($q) => $q->where('academic_year_id', $this->academicYearFilter))
            ->when($this->gradeFilter, fn ($q) => $q->where('grade_level', $this->gradeFilter));

        $this->applySorting($query, [
            'name' => 'name',
            'grade_level' => 'grade_level',
            'capacity' => 'capacity',
        ], 'grade_level', 'asc');

        $headers = ['Nama Kelas / Rombel', 'Tingkat / Jenjang', 'Tahun Ajaran', 'Wali Kelas', 'Jumlah Siswa', 'Kapasitas'];

        $rows = $query->get()->map(fn (SchoolClass $c) => [
            $c->name,
            'Tingkat '.$c->grade_level,
            $c->academicYear ? "{$c->academicYear->name} ({$c->academicYear->semester})" : '-',
            $c->homeroomTeacher?->name ?? 'Belum ditentukan',
            $c->enrollments_count.' Siswa',
            $c->capacity.' Kursi',
        ]);

        return $this->exportFormattedResponse('kelas-dan-rombel', $headers, $rows, 'Data Rombongan Belajar', 'Daftar kelas dan alokasi wali kelas', $format);
    }

    protected function guardDelete($record): ?string
    {
        if ($record->enrollments()->where('status', 'active')->exists()) {
            return __('Kelas ini masih berisi santri aktif. Pindahkan santrinya terlebih dahulu.');
        }

        if ($record->subjectAssignments()->exists()) {
            return __('Kelas ini masih punya pembagian tugas mengajar. Hapus penugasannya terlebih dahulu.');
        }

        return null;
    }

    public function render()
    {
        $query = SchoolClass::query()
            ->with(['academicYear', 'homeroomTeacher'])
            ->withCount('enrollments')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->academicYearFilter, fn ($q) => $q->where('academic_year_id', $this->academicYearFilter))
            ->when($this->gradeFilter, fn ($q) => $q->where('grade_level', $this->gradeFilter));

        $this->applySorting($query, [
            'name' => 'name',
            'grade_level' => 'grade_level',
            'capacity' => 'capacity',
        ], 'grade_level', 'asc');

        return view('livewire.master-data.school-classes', [
            'schoolClasses' => $query->paginate($this->perPage),
            'academicYears' => AcademicYear::orderByDesc('start_date')->get(),
            'teachers' => Teacher::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    protected function modelClass(): string
    {
        return SchoolClass::class;
    }

    protected function resetForm(): void
    {
        $this->reset(['name']);
        $activeYear = AcademicYear::where('is_active', true)->first();
        $this->academic_year_id = $activeYear?->id;
        $this->grade_level = 7;
        $this->homeroom_teacher_id = null;
        $this->capacity = 36;
    }

    protected function fillForm($record): void
    {
        $this->academic_year_id = $record->academic_year_id;
        $this->name = $record->name;
        $this->grade_level = (int) $record->grade_level;
        $this->homeroom_teacher_id = $record->homeroom_teacher_id;
        $this->capacity = (int) $record->capacity;
    }

    protected function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'name' => ['required', 'string', 'max:50'],
            'grade_level' => ['required', 'integer', 'min:1', 'max:12'],
            'homeroom_teacher_id' => ['nullable', 'exists:teachers,id'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }
}
