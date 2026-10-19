<?php

namespace App\Livewire\Attendance;

use App\Jobs\SendAttendanceWhatsAppNotification;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentEnrollment;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app', ['heading' => 'Presensi Harian Siswa'])]
#[Title('Presensi Siswa')]
class StudentAttendanceSheet extends Component
{
    #[Url]
    public ?int $classId = null;

    #[Url]
    public string $date = '';

    public string $searchQuery = '';

    public bool $sendWhatsApp = true;

    /**
     * @var array<int, array{status: string, notes: string}>
     */
    public array $attendanceData = [];

    public function mount(): void
    {
        $this->date = filled($this->date) ? $this->date : now()->format('Y-m-d');

        if (! $this->classId) {
            $activeYear = AcademicYear::active()->first();
            $firstClass = SchoolClass::query()
                ->when($activeYear, fn ($q) => $q->where('academic_year_id', $activeYear->id))
                ->orderBy('name')
                ->first();

            $this->classId = $firstClass?->id;
        }

        $this->loadAttendance();
    }

    public function updatedClassId(): void
    {
        $this->loadAttendance();
    }

    public function updatedDate(): void
    {
        $this->loadAttendance();
    }

    public function loadAttendance(): void
    {
        $this->attendanceData = [];

        if (! $this->classId) {
            return;
        }

        $enrolledStudents = StudentEnrollment::query()
            ->where('class_id', $this->classId)
            ->where('status', 'active')
            ->pluck('student_id');

        $existingAttendances = StudentAttendance::query()
            ->where('class_id', $this->classId)
            ->whereDate('date', $this->date)
            ->get()
            ->keyBy('student_id');

        foreach ($enrolledStudents as $studentId) {
            if ($existing = $existingAttendances->get($studentId)) {
                $this->attendanceData[$studentId] = [
                    'status' => $existing->status,
                    'notes' => (string) ($existing->notes ?? ''),
                ];
            } else {
                // Default: semua siswa otomatis hadir
                $this->attendanceData[$studentId] = [
                    'status' => 'present',
                    'notes' => '',
                ];
            }
        }
    }

    public function setStatus(int $studentId, string $status): void
    {
        if (isset($this->attendanceData[$studentId])) {
            $this->attendanceData[$studentId]['status'] = $status;
        }
    }

    public function setAllPresent(): void
    {
        foreach ($this->attendanceData as $studentId => $data) {
            $this->attendanceData[$studentId]['status'] = 'present';
        }

        $this->dispatch('notify', 'Semua siswa diatur Hadir.');
    }

    public function canManage(): bool
    {
        return Gate::allows('manage-attendance');
    }

    public function save(): void
    {
        if (! $this->canManage()) {
            abort(403, 'Anda tidak memiliki hak akses untuk menginput presensi.');
        }

        $this->validate([
            'classId' => 'required|exists:classes,id',
            'date' => 'required|date|before_or_equal:today',
            'attendanceData.*.status' => 'required|in:present,sick,permit,absent',
            'attendanceData.*.notes' => 'nullable|string|max:255',
        ]);

        $counts = ['present' => 0, 'sick' => 0, 'permit' => 0, 'absent' => 0];

        $previousStatuses = StudentAttendance::query()
            ->whereDate('date', $this->date)
            ->whereIn('student_id', array_keys($this->attendanceData))
            ->pluck('status', 'student_id');

        DB::transaction(function () use (&$counts, $previousStatuses) {
            foreach ($this->attendanceData as $studentId => $data) {
                $status = $data['status'];
                $notes = filled($data['notes']) ? trim($data['notes']) : null;

                if (isset($counts[$status])) {
                    $counts[$status]++;
                }

                StudentAttendance::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'date' => $this->date,
                    ],
                    [
                        'class_id' => $this->classId,
                        'status' => $status,
                        'notes' => $notes,
                        'recorded_by' => auth()->id(),
                    ]
                );

                // Simpan ulang tanpa perubahan status tidak boleh mengirim WA ke wali untuk kedua kalinya.
                $statusChanged = $previousStatuses->get($studentId) !== $status;

                if ($this->sendWhatsApp && $statusChanged && in_array($status, ['sick', 'permit', 'absent'], true)) {
                    SendAttendanceWhatsAppNotification::dispatch(
                        $studentId,
                        $this->classId,
                        $this->date,
                        $status,
                        $notes
                    );
                }
            }
        });

        $summary = "{$counts['present']} Hadir, {$counts['sick']} Sakit, {$counts['permit']} Izin, {$counts['absent']} Alpa";
        $this->dispatch('notify', "Presensi berhasil disimpan: {$summary}");
    }

    public function render(): View
    {
        $classes = SchoolClass::with('academicYear')->orderBy('name')->get();
        $selectedClass = $this->classId ? SchoolClass::find($this->classId) : null;

        $studentsQuery = Student::query()
            ->whereHas('enrollments', function ($q) {
                $q->where('class_id', $this->classId)->where('status', 'active');
            })
            ->when(filled($this->searchQuery), function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', "%{$this->searchQuery}%")
                        ->orWhere('nis', 'like', "%{$this->searchQuery}%");
                });
            })
            ->orderBy('name');

        $students = $this->classId ? $studentsQuery->get() : collect();

        // Hitung rekap instan dari data di layar
        $stats = [
            'total' => count($this->attendanceData),
            'present' => 0,
            'sick' => 0,
            'permit' => 0,
            'absent' => 0,
        ];

        foreach ($this->attendanceData as $item) {
            $st = $item['status'] ?? 'present';
            if (isset($stats[$st])) {
                $stats[$st]++;
            }
        }

        $savedCount = $this->classId
            ? StudentAttendance::query()->where('class_id', $this->classId)->whereDate('date', $this->date)->count()
            : 0;

        return view('livewire.attendance.student-attendance-sheet', [
            'savedCount' => $savedCount,
            'schoolClasses' => $classes,
            'selectedClass' => $selectedClass,
            'students' => $students,
            'stats' => $stats,
        ]);
    }
}
