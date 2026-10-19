<?php

namespace App\Livewire\Academic;

use App\Models\AcademicYear;
use App\Models\ReportCardSummary;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Services\AcademicGradingService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app', ['heading' => 'Pengolahan & Cetak E-Rapor'])]
#[Title('E-Rapor Santri')]
class ReportCardIndex extends Component
{
    #[Url]
    public ?int $academicYearFilter = null;

    #[Url]
    public ?int $classFilter = null;

    // Modal state for homeroom notes & promotion
    public bool $showNotesModal = false;

    public ?int $selectedStudentId = null;

    public string $selectedStudentName = '';

    public int $sick_count = 0;

    public int $permit_count = 0;

    public int $absent_count = 0;

    public string $extracurricular_notes = '';

    public string $homeroom_notes = '';

    public ?string $promotion_status = 'promoted';

    public bool $is_locked = false;

    public function mount(): void
    {
        Gate::authorize('academic.grading.view');

        $activeYear = AcademicYear::where('is_active', true)->first();
        if ($activeYear) {
            $this->academicYearFilter = $activeYear->id;
        }

        $firstClass = SchoolClass::orderBy('grade_level')->orderBy('name')->first();
        if ($firstClass) {
            $this->classFilter = $firstClass->id;
        }
    }

    public function editNotes(int $studentId): void
    {
        Gate::authorize('academic.grading.manage');

        /** @var AcademicGradingService $gradingService */
        $gradingService = app(AcademicGradingService::class);

        $student = Student::findOrFail($studentId);
        $this->selectedStudentId = $student->id;
        $this->selectedStudentName = $student->name;

        $class = SchoolClass::find($this->classFilter);
        $year = AcademicYear::find($this->academicYearFilter);

        if ($class && $year) {
            $summary = ReportCardSummary::where('student_id', $student->id)
                ->where('class_id', $class->id)
                ->where('academic_year_id', $year->id)
                ->first();

            if (! $summary) {
                $summary = $gradingService->syncReportCardAttendance($student, $class, $year);
            }

            $this->sick_count = $summary->sick_count;
            $this->permit_count = $summary->permit_count;
            $this->absent_count = $summary->absent_count;
            $this->extracurricular_notes = (string) $summary->extracurricular_notes;
            $this->homeroom_notes = (string) $summary->homeroom_notes;
            $this->promotion_status = $summary->promotion_status ?: 'promoted';
            $this->is_locked = (bool) $summary->is_locked;
        }

        $this->showNotesModal = true;
        $this->dispatch('open-modal', 'notes-modal');
    }

    public function closeNotesModal(): void
    {
        $this->showNotesModal = false;
        $this->selectedStudentId = null;
        $this->dispatch('close-modal', 'notes-modal');
        $this->resetErrorBag();
    }

    public function saveNotes(): void
    {
        Gate::authorize('academic.grading.manage');

        if (! $this->selectedStudentId || ! $this->classFilter || ! $this->academicYearFilter) {
            return;
        }

        ReportCardSummary::updateOrCreate(
            [
                'student_id' => $this->selectedStudentId,
                'class_id' => $this->classFilter,
                'academic_year_id' => $this->academicYearFilter,
            ],
            [
                'sick_count' => $this->sick_count,
                'permit_count' => $this->permit_count,
                'absent_count' => $this->absent_count,
                'extracurricular_notes' => $this->extracurricular_notes,
                'homeroom_notes' => $this->homeroom_notes,
                'promotion_status' => $this->promotion_status,
                'is_locked' => $this->is_locked,
            ]
        );

        $this->closeNotesModal();
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Catatan wali kelas dan status kenaikan berhasil disimpan.',
        ]);
    }

    public function toggleLock(int $studentId): void
    {
        Gate::authorize('academic.grading.manage');

        $summary = ReportCardSummary::where('student_id', $studentId)
            ->where('class_id', $this->classFilter)
            ->where('academic_year_id', $this->academicYearFilter)
            ->first();

        if ($summary) {
            $summary->update(['is_locked' => ! $summary->is_locked]);
            $this->dispatch('notify', [
                'type' => 'info',
                'message' => $summary->is_locked ? 'Rapor santri dikunci.' : 'Kunci rapor dibuka.',
            ]);
        }
    }

    public function syncAllAttendance(): void
    {
        if (! $this->classFilter || ! $this->academicYearFilter) {
            return;
        }

        $class = SchoolClass::find($this->classFilter);
        $year = AcademicYear::find($this->academicYearFilter);

        if (! $class || ! $year) {
            return;
        }

        /** @var AcademicGradingService $gradingService */
        $gradingService = app(AcademicGradingService::class);

        $enrollments = StudentEnrollment::where('class_id', $class->id)->where('status', 'active')->with('student')->get();

        foreach ($enrollments as $enrollment) {
            $gradingService->syncReportCardAttendance($enrollment->student, $class, $year);
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Data presensi seluruh siswa di rombel ini berhasil disinkronkan.',
        ]);
    }

    public function render(): View
    {
        /** @var AcademicGradingService $gradingService */
        $gradingService = app(AcademicGradingService::class);
        $studentsData = collect();

        if ($this->classFilter && $this->academicYearFilter) {
            $enrollments = StudentEnrollment::query()
                ->with(['student.guardian'])
                ->where('class_id', $this->classFilter)
                ->where('status', 'active')
                ->get()
                ->sortBy(fn (StudentEnrollment $e) => $e->student->name);

            $studentsData = $enrollments->map(function (StudentEnrollment $e) use ($gradingService) {
                return $gradingService->getStudentReportCardData($e->student, $this->academicYearFilter);
            });
        }

        return view('livewire.academic.report-card-index', [
            'academicYears' => AcademicYear::orderByDesc('is_active')->orderBy('name')->get(),
            'schoolClasses' => SchoolClass::orderBy('grade_level')->orderBy('name')->get(),
            'studentsData' => $studentsData,
        ]);
    }
}
