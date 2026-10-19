<?php

namespace App\Livewire\Academic;

use App\Models\AcademicYear;
use App\Models\StudentEnrollment;
use App\Models\StudentGrade;
use App\Models\SubjectAssignment;
use App\Models\Teacher;
use App\Services\AcademicGradingService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app', ['heading' => 'Buku Nilai Mata Pelajaran'])]
#[Title('Buku Nilai')]
class GradeSheet extends Component
{
    #[Url(as: 'assignment_id')]
    public ?int $selectedAssignmentId = null;

    /**
     * @var array<int, array{
     *     student_id: int,
     *     name: string,
     *     nis: string,
     *     formative_average: float|string,
     *     mid_term_score: float|string,
     *     final_term_score: float|string,
     *     final_score: float|string,
     *     predicate: string,
     *     achievement_desc: string,
     *     improvement_desc: string
     * }>
     */
    public array $rows = [];

    public function mount(AcademicGradingService $gradingService): void
    {
        Gate::authorize('academic.grading.view');

        if (! $this->selectedAssignmentId) {
            $defaultAssignment = $this->getDefaultAssignment();
            if ($defaultAssignment) {
                $this->selectedAssignmentId = $defaultAssignment->id;
            }
        }

        if ($this->selectedAssignmentId) {
            $this->loadGrades($gradingService);
        }
    }

    protected function getDefaultAssignment(): ?SubjectAssignment
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user?->id)->first();

        $activeYear = AcademicYear::where('is_active', true)->first();

        $query = SubjectAssignment::query();

        if ($activeYear) {
            $query->where('academic_year_id', $activeYear->id);
        }

        if ($teacher && ! $user?->can('academic.assignments.manage')) {
            $query->where('teacher_id', $teacher->id);
        }

        return $query->first();
    }

    public function updatedSelectedAssignmentId(): void
    {
        $this->loadGrades(app(AcademicGradingService::class));
    }

    public function loadGrades(AcademicGradingService $gradingService): void
    {
        if (! $this->selectedAssignmentId) {
            $this->rows = [];

            return;
        }

        $assignment = SubjectAssignment::with(['schoolClass', 'subject'])->find($this->selectedAssignmentId);
        if (! $assignment) {
            $this->rows = [];

            return;
        }

        $enrollments = StudentEnrollment::query()
            ->with('student')
            ->where('class_id', $assignment->class_id)
            ->where('status', 'active')
            ->get()
            ->sortBy(fn (StudentEnrollment $e) => $e->student->name);

        $existingGrades = StudentGrade::query()
            ->where('subject_assignment_id', $assignment->id)
            ->get()
            ->keyBy('student_id');

        $this->rows = [];

        foreach ($enrollments as $enrollment) {
            $student = $enrollment->student;
            $grade = $existingGrades->get($student->id);

            $formative = $grade ? (float) $grade->formative_average : 0.00;
            $mid = $grade ? (float) $grade->mid_term_score : 0.00;
            $final = $grade ? (float) $grade->final_term_score : 0.00;

            $finalScore = $grade ? (float) $grade->final_score : $gradingService->calculateFinalScore($formative, $mid, $final);
            $predicate = $grade ? $grade->predicate : $gradingService->determinePredicate($finalScore, (float) $assignment->passing_grade);

            $defaultDesc = $gradingService->generateCompetenceDescriptions($assignment->subject->name, $predicate);
            $achievement = $grade?->achievement_desc ?: $defaultDesc['achievement'];
            $improvement = $grade?->improvement_desc ?: $defaultDesc['improvement'];

            $this->rows[$student->id] = [
                'student_id' => $student->id,
                'name' => $student->name,
                'nis' => $student->nis,
                'formative_average' => $formative > 0 ? (string) $formative : '',
                'mid_term_score' => $mid > 0 ? (string) $mid : '',
                'final_term_score' => $final > 0 ? (string) $final : '',
                'final_score' => (string) $finalScore,
                'predicate' => $predicate,
                'achievement_desc' => $achievement,
                'improvement_desc' => $improvement,
            ];
        }
    }

    public function updatedRows(mixed $value, string $name): void
    {
        // name format: rows.STUDENT_ID.FIELD
        $parts = explode('.', $name);
        if (count($parts) >= 2) {
            $studentId = (int) $parts[0];
            $this->recalculateRow($studentId);
        }
    }

    public function recalculateRow(int $studentId): void
    {
        if (! isset($this->rows[$studentId]) || ! $this->selectedAssignmentId) {
            return;
        }

        $assignment = SubjectAssignment::find($this->selectedAssignmentId);
        if (! $assignment) {
            return;
        }

        /** @var AcademicGradingService $gradingService */
        $gradingService = app(AcademicGradingService::class);

        $formative = (float) ($this->rows[$studentId]['formative_average'] ?: 0);
        $mid = (float) ($this->rows[$studentId]['mid_term_score'] ?: 0);
        $final = (float) ($this->rows[$studentId]['final_term_score'] ?: 0);

        $finalScore = $gradingService->calculateFinalScore($formative, $mid, $final);
        $predicate = $gradingService->determinePredicate($finalScore, (float) $assignment->passing_grade);

        $this->rows[$studentId]['final_score'] = (string) $finalScore;
        $this->rows[$studentId]['predicate'] = $predicate;
    }

    public function generateAllDescriptions(AcademicGradingService $gradingService): void
    {
        if (! $this->selectedAssignmentId) {
            return;
        }

        $assignment = SubjectAssignment::with('subject')->find($this->selectedAssignmentId);
        if (! $assignment) {
            return;
        }

        foreach ($this->rows as $studentId => $row) {
            $desc = $gradingService->generateCompetenceDescriptions($assignment->subject->name, $row['predicate']);
            $this->rows[$studentId]['achievement_desc'] = $desc['achievement'];
            $this->rows[$studentId]['improvement_desc'] = $desc['improvement'];
        }

        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Deskripsi capaian kompetensi berhasil disesuaikan dengan nilai.',
        ]);
    }

    public function saveAll(AcademicGradingService $gradingService): void
    {
        Gate::authorize('academic.grading.manage');

        if (! $this->selectedAssignmentId) {
            return;
        }

        $assignment = SubjectAssignment::with(['subject', 'schoolClass.academicYear'])->findOrFail($this->selectedAssignmentId);

        $this->validate([
            'rows.*.formative_average' => ['nullable', 'numeric', 'between:0,100'],
            'rows.*.mid_term_score' => ['nullable', 'numeric', 'between:0,100'],
            'rows.*.final_term_score' => ['nullable', 'numeric', 'between:0,100'],
            'rows.*.achievement_desc' => ['nullable', 'string', 'max:1000'],
            'rows.*.improvement_desc' => ['nullable', 'string', 'max:1000'],
        ], [
            'rows.*.formative_average.between' => 'Nilai harian harus antara 0 sampai 100.',
            'rows.*.mid_term_score.between' => 'Nilai UTS harus antara 0 sampai 100.',
            'rows.*.final_term_score.between' => 'Nilai UAS harus antara 0 sampai 100.',
            'rows.*.*.numeric' => 'Nilai harus berupa angka.',
        ]);

        DB::transaction(function () use ($assignment, $gradingService) {
            foreach ($this->rows as $studentId => $data) {
                $formative = (float) ($data['formative_average'] ?: 0);
                $mid = (float) ($data['mid_term_score'] ?: 0);
                $final = (float) ($data['final_term_score'] ?: 0);
                $finalScore = $gradingService->calculateFinalScore($formative, $mid, $final);
                $predicate = $gradingService->determinePredicate($finalScore, (float) $assignment->passing_grade);

                StudentGrade::updateOrCreate(
                    [
                        'subject_assignment_id' => $assignment->id,
                        'student_id' => $studentId,
                    ],
                    [
                        'formative_average' => $formative,
                        'mid_term_score' => $mid,
                        'final_term_score' => $final,
                        'final_score' => $finalScore,
                        'predicate' => $predicate,
                        'achievement_desc' => $data['achievement_desc'],
                        'improvement_desc' => $data['improvement_desc'],
                    ]
                );
            }
        });

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Nilai seluruh siswa berhasil disimpan ke basis data.',
        ]);
    }

    public function render(): View
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user?->id)->first();

        $assignmentsQuery = SubjectAssignment::query()
            ->with(['schoolClass', 'subject', 'teacher', 'academicYear'])
            ->orderBy('class_id')
            ->orderBy('subject_id');

        if ($teacher && ! $user?->can('academic.assignments.manage')) {
            $assignmentsQuery->where('teacher_id', $teacher->id);
        }

        $assignments = $assignmentsQuery->get();
        $currentAssignment = $this->selectedAssignmentId ? SubjectAssignment::with(['schoolClass', 'subject', 'teacher'])->find($this->selectedAssignmentId) : null;

        return view('livewire.academic.grade-sheet', [
            'assignments' => $assignments,
            'currentAssignment' => $currentAssignment,
        ]);
    }
}
