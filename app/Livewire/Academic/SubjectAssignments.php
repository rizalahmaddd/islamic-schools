<?php

namespace App\Livewire\Academic;

use App\Livewire\Concerns\WithCrudActions;
use App\Livewire\Concerns\WithRealtimeRefresh;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\SubjectAssignment;
use App\Models\Teacher;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app', ['heading' => 'Pembagian Tugas Mengajar'])]
#[Title('Pembagian Tugas Mengajar')]
class SubjectAssignments extends Component
{
    use WithCrudActions, WithRealtimeRefresh;

    #[Url]
    public ?int $academicYearFilter = null;

    #[Url]
    public ?int $classFilter = null;

    #[Url]
    public ?int $teacherFilter = null;

    // Form attributes
    public ?int $academic_year_id = null;

    public ?int $class_id = null;

    public ?int $subject_id = null;

    public ?int $teacher_id = null;

    public float|string $passing_grade = 75.00;

    protected function modelClass(): string
    {
        return SubjectAssignment::class;
    }

    public function mount(): void
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        if ($activeYear) {
            $this->academicYearFilter = $activeYear->id;
            $this->academic_year_id = $activeYear->id;
        }
    }

    public function canManage(): bool
    {
        return auth()->user()?->can('academic.assignments.manage') ?? false;
    }

    protected function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => [
                'required',
                'exists:subjects,id',
                Rule::unique('subject_assignments', 'subject_id')
                    ->where('academic_year_id', $this->academic_year_id)
                    ->where('class_id', $this->class_id)
                    ->ignore($this->editingId),
            ],
            'teacher_id' => ['required', 'exists:teachers,id'],
            'passing_grade' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }

    protected function messages(): array
    {
        return [
            'subject_id.unique' => 'Mata pelajaran ini sudah ditugaskan pada rombel dan tahun ajaran tersebut.',
            'passing_grade.min' => 'KKM minimal bernilai 0.',
            'passing_grade.max' => 'KKM maksimal bernilai 100.',
        ];
    }

    public function save(): void
    {
        $this->authorizeManage();

        $validated = $this->validate();
        $isEditing = (bool) $this->editingId;

        if ($isEditing) {
            SubjectAssignment::findOrFail($this->editingId)->update($validated);
        } else {
            SubjectAssignment::create($validated);
        }

        $this->closeModal();
        $this->resetPage();
        $this->notify($isEditing ? 'Tugas mengajar berhasil diperbarui.' : 'Tugas mengajar baru berhasil ditambahkan.');
    }

    protected function resetForm(): void
    {
        $this->subject_id = null;
        $this->teacher_id = null;
        $this->passing_grade = 75.00;
        if ($this->academicYearFilter) {
            $this->academic_year_id = $this->academicYearFilter;
        }
        if ($this->classFilter) {
            $this->class_id = $this->classFilter;
        }
        $this->resetValidation();
    }

    protected function fillForm($record): void
    {
        $this->academic_year_id = $record->academic_year_id;
        $this->class_id = $record->class_id;
        $this->subject_id = $record->subject_id;
        $this->teacher_id = $record->teacher_id;
        $this->passing_grade = (float) $record->passing_grade;
    }

    public function render(): View
    {
        $query = SubjectAssignment::query()
            ->with(['academicYear', 'schoolClass', 'subject', 'teacher'])
            ->when($this->academicYearFilter, fn (Builder $q) => $q->where('academic_year_id', $this->academicYearFilter))
            ->when($this->classFilter, fn (Builder $q) => $q->where('class_id', $this->classFilter))
            ->when($this->teacherFilter, fn (Builder $q) => $q->where('teacher_id', $this->teacherFilter))
            ->when($this->search, function (Builder $q) {
                $q->where(function ($search) {
                    $search->whereHas('subject', fn (Builder $sq) => $sq->where('name', 'like', "%{$this->search}%")->orWhere('code', 'like', "%{$this->search}%"))
                        ->orWhereHas('teacher', fn (Builder $tq) => $tq->where('name', 'like', "%{$this->search}%"))
                        ->orWhereHas('schoolClass', fn (Builder $cq) => $cq->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->orderBy('class_id')
            ->orderBy('subject_id');

        $assignments = $query->paginate($this->perPage);

        return view('livewire.academic.subject-assignments', [
            'assignments' => $assignments,
            'academicYears' => AcademicYear::orderByDesc('is_active')->orderBy('name')->get(),
            'schoolClasses' => SchoolClass::orderBy('grade_level')->orderBy('name')->get(),
            'subjects' => Subject::active()->orderBy('name')->get(),
            'teachers' => Teacher::active()->orderBy('name')->get(),
        ]);
    }
}
