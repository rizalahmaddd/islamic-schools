<?php

namespace App\Livewire\Attendance;

use App\Livewire\Concerns\WithDataTable;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app', ['heading' => 'Presensi Pendidik & Staf'])]
#[Title('Presensi Guru')]
class TeacherAttendanceList extends Component
{
    use WithDataTable;

    #[Url]
    public string $date = '';

    #[Url]
    public string $statusFilter = '';

    public string $search = '';

    // Modal manual entry
    public bool $showModal = false;

    public ?int $editingTeacherId = null;

    public string $formStatus = 'present';

    public string $formCheckIn = '07:15';

    public string $formCheckOut = '';

    public string $formNotes = '';

    public function mount(): void
    {
        $this->date = filled($this->date) ? $this->date : now()->format('Y-m-d');
    }

    public function openManualModal(int $teacherId): void
    {
        $this->editingTeacherId = $teacherId;
        $attendance = TeacherAttendance::where('teacher_id', $teacherId)
            ->where('date', $this->date)
            ->first();

        if ($attendance) {
            $this->formStatus = $attendance->status;
            $this->formCheckIn = $attendance->check_in_time ? substr((string) $attendance->check_in_time, 0, 5) : '';
            $this->formCheckOut = $attendance->check_out_time ? substr((string) $attendance->check_out_time, 0, 5) : '';
            $this->formNotes = (string) ($attendance->notes ?? '');
        } else {
            $this->formStatus = 'present';
            $this->formCheckIn = now()->format('H:i');
            $this->formCheckOut = '';
            $this->formNotes = '';
        }

        $this->showModal = true;
        $this->dispatch('open-modal', 'manual-attendance-modal');
    }

    public function saveManual(): void
    {
        if (! Gate::allows('manage-attendance')) {
            abort(403);
        }

        $this->validate([
            'editingTeacherId' => 'required|exists:teachers,id',
            'formStatus' => 'required|in:present,late,permit,sick,absent',
            'formCheckIn' => 'nullable|string',
            'formCheckOut' => 'nullable|string',
            'formNotes' => 'nullable|string|max:255',
        ]);

        TeacherAttendance::updateOrCreate(
            [
                'teacher_id' => $this->editingTeacherId,
                'date' => $this->date,
            ],
            [
                'status' => $this->formStatus,
                'check_in_time' => filled($this->formCheckIn) ? $this->formCheckIn.':00' : null,
                'check_out_time' => filled($this->formCheckOut) ? $this->formCheckOut.':00' : null,
                'notes' => filled($this->formNotes) ? $this->formNotes : null,
                'method' => 'manual',
                'recorded_by' => Auth::id(),
            ]
        );

        $this->dispatch('close-modal', 'manual-attendance-modal');
        $this->dispatch('notify', 'Data presensi berhasil disimpan.');
    }

    public function export(string $format = 'xlsx'): StreamedResponse
    {
        $headers = [
            'NIP',
            'Nama Pendidik',
            'Bidang Ajar',
            'Tanggal',
            'Jam Masuk',
            'Jam Pulang',
            'Status',
            'Metode',
            'Jarak (m)',
            'Catatan',
        ];

        $attendances = TeacherAttendance::with('teacher')
            ->where('date', $this->date)
            ->get();

        $teachers = Teacher::active()->get();

        $rows = $teachers->map(function (Teacher $t) use ($attendances) {
            $att = $attendances->firstWhere('teacher_id', $t->id);

            return [
                $t->nip ?: '-',
                $t->name,
                $t->specialization ?: '-',
                $this->date,
                $att?->check_in_time ? substr((string) $att->check_in_time, 0, 5) : '-',
                $att?->check_out_time ? substr((string) $att->check_out_time, 0, 5) : '-',
                $att?->statusLabel() ?? 'Belum Hadir',
                $att?->method ?? '-',
                $att?->distance_meters !== null ? $att->distance_meters.' m' : '-',
                $att?->notes ?? '-',
            ];
        })->all();

        $formattedDate = Carbon::parse($this->date)->translatedFormat('d F Y');
        $filename = "presensi-guru-{$this->date}";
        $title = "Rekapitulasi Presensi Pendidik & Staf ({$formattedDate})";

        return $this->exportFormattedResponse($filename, $headers, $rows, $title, 'Laporan Kehadiran Harian Guru', $format);
    }

    public function render(): View
    {
        $attendances = TeacherAttendance::with(['teacher', 'recorder'])
            ->where('date', $this->date)
            ->get()
            ->keyBy('teacher_id');

        $teachers = Teacher::query()
            ->active()
            ->when(filled($this->search), function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', "%{$this->search}%")
                        ->orWhere('nip', 'like', "%{$this->search}%")
                        ->orWhere('specialization', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('name')
            ->get();

        // Filter status jika dipilih
        if (filled($this->statusFilter)) {
            $teachers = $teachers->filter(function ($t) use ($attendances) {
                $att = $attendances->get($t->id);
                if ($this->statusFilter === 'unrecorded') {
                    return $att === null;
                }

                return $att && $att->status === $this->statusFilter;
            });
        }

        $stats = [
            'total' => Teacher::active()->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'permit' => $attendances->whereIn('status', ['permit', 'sick'])->count(),
            'unrecorded' => max(0, Teacher::active()->count() - $attendances->count()),
        ];

        $editingTeacher = $this->editingTeacherId ? Teacher::find($this->editingTeacherId) : null;

        return view('livewire.attendance.teacher-attendance-list', [
            'teachers' => $teachers,
            'attendances' => $attendances,
            'stats' => $stats,
            'editingTeacher' => $editingTeacher,
        ]);
    }
}
