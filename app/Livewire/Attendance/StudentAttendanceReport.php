<?php

namespace App\Livewire\Attendance;

use App\Livewire\Concerns\WithDataTable;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app', ['heading' => 'Rekapitulasi Presensi Santri'])]
#[Title('Rekap Presensi')]
class StudentAttendanceReport extends Component
{
    use WithDataTable;

    #[Url]
    public ?int $classId = null;

    #[Url]
    public int $month = 0;

    #[Url]
    public int $year = 0;

    public string $search = '';

    public function mount(): void
    {
        $this->month = $this->month ?: (int) now()->format('m');
        $this->year = $this->year ?: (int) now()->format('Y');

        if (! $this->classId) {
            $activeYear = AcademicYear::active()->first();
            $firstClass = SchoolClass::query()
                ->when($activeYear, fn ($q) => $q->where('academic_year_id', $activeYear->id))
                ->orderBy('name')
                ->first();

            $this->classId = $firstClass?->id;
        }
    }

    /**
     * @return Collection<int, array{student: Student, present: int, sick: int, permit: int, absent: int, total_recorded: int, rate: float}>
     */
    protected function getRecapData(): Collection
    {
        if (! $this->classId) {
            return collect();
        }

        $startDate = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth()->toDateString();

        $attendances = StudentAttendance::query()
            ->where('class_id', $this->classId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->groupBy('student_id');

        $students = Student::query()
            ->whereHas('enrollments', function ($q) {
                $q->where('class_id', $this->classId)->where('status', 'active');
            })
            ->when(filled($this->search), function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', "%{$this->search}%")
                        ->orWhere('nis', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('name')
            ->get();

        return $students->map(function (Student $student) use ($attendances) {
            $studentRecords = $attendances->get($student->id, collect());
            $present = $studentRecords->where('status', 'present')->count();
            $sick = $studentRecords->where('status', 'sick')->count();
            $permit = $studentRecords->where('status', 'permit')->count();
            $absent = $studentRecords->where('status', 'absent')->count();
            $total = $present + $sick + $permit + $absent;
            $rate = $total > 0 ? round(($present / $total) * 100, 1) : 0.0;

            return [
                'student' => $student,
                'present' => $present,
                'sick' => $sick,
                'permit' => $permit,
                'absent' => $absent,
                'total_recorded' => $total,
                'rate' => $rate,
            ];
        });
    }

    public function export(string $format = 'xlsx'): StreamedResponse
    {
        $selectedClass = SchoolClass::with('academicYear')->find($this->classId);
        $className = $selectedClass?->name ?? 'Semua-Kelas';
        $monthName = Carbon::createFromDate($this->year, $this->month, 1)->translatedFormat('F_Y');
        $filename = "rekap-presensi-{$className}-{$monthName}";

        $headers = [
            'NIS',
            'Nama Santri',
            'L/P',
            'Hadir (H)',
            'Sakit (S)',
            'Izin (I)',
            'Alpa (A)',
            'Total Pertemuan',
            'Persentase Kehadiran (%)',
        ];

        $recap = $this->getRecapData();

        $rows = $recap->map(function ($row) {
            return [
                $row['student']->nis,
                $row['student']->name,
                $row['student']->gender,
                $row['present'],
                $row['sick'],
                $row['permit'],
                $row['absent'],
                $row['total_recorded'],
                $row['rate'].'%',
            ];
        })->all();

        $title = "Rekapitulasi Presensi Kelas {$className}";
        $description = 'Periode: '.Carbon::createFromDate($this->year, $this->month, 1)->translatedFormat('F Y');

        return $this->exportFormattedResponse($filename, $headers, $rows, $title, $description, $format);
    }

    public function render(): View
    {
        $classes = SchoolClass::with('academicYear')->orderBy('name')->get();
        $selectedClass = $this->classId ? SchoolClass::with('homeroomTeacher')->find($this->classId) : null;
        $recap = $this->getRecapData();

        $totalMeetings = 0;
        if ($this->classId) {
            $startDate = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth()->toDateString();
            $endDate = Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth()->toDateString();
            $totalMeetings = StudentAttendance::query()
                ->where('class_id', $this->classId)
                ->whereBetween('date', [$startDate, $endDate])
                ->distinct('date')
                ->count('date');
        }

        $overallRate = $recap->isNotEmpty() && $recap->sum('total_recorded') > 0
            ? round(($recap->sum('present') / $recap->sum('total_recorded')) * 100, 1)
            : 0.0;

        return view('livewire.attendance.student-attendance-report', [
            'schoolClasses' => $classes,
            'selectedClass' => $selectedClass,
            'recap' => $recap,
            'totalMeetings' => $totalMeetings,
            'overallRate' => $overallRate,
        ]);
    }
}
