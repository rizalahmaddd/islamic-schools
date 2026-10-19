<?php

namespace App\Livewire\Islamic;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentDailyWorship;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app', ['heading' => 'Mutaba\'ah Ibadah Santri'])]
#[Title('Mutaba\'ah Ibadah Santri')]
class MutabaahWorship extends Component
{
    public string $date = '';

    public ?int $selectedClassId = null;

    /** @var array<int, array{fajr: string, dhuhr: string, asr: string, maghrib: string, isha: string, dhuha: bool, tahajjud: bool, tilawah_pages: int, fasting_type: string}> */
    public array $worshipState = [];

    public function mount(): void
    {
        abort_unless(auth()->user()->can('view-tahfidz') || auth()->user()->can('manage-tahfidz'), 403);

        $this->date = now()->toDateString();
        $firstClass = SchoolClass::query()->orderBy('grade_level')->orderBy('name')->first();
        if ($firstClass) {
            $this->selectedClassId = $firstClass->id;
        }

        $this->loadWorshipState();
    }

    public function updatedDate(): void
    {
        $this->loadWorshipState();
    }

    public function updatedSelectedClassId(): void
    {
        $this->loadWorshipState();
    }

    public function canManage(): bool
    {
        return auth()->user()->can('manage-tahfidz');
    }

    public function loadWorshipState(): void
    {
        $this->worshipState = [];

        if (! $this->selectedClassId) {
            return;
        }

        $students = Student::query()
            ->whereHas('enrollments', fn ($q) => $q->where('class_id', $this->selectedClassId)->where('status', 'active'))
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $existing = StudentDailyWorship::query()
            ->whereIn('student_id', $students->pluck('id'))
            ->where('date', $this->date)
            ->get()
            ->keyBy('student_id');

        foreach ($students as $student) {
            $record = $existing->get($student->id);

            $this->worshipState[$student->id] = [
                'fajr' => $record ? $record->fajr : 'jamaah',
                'dhuhr' => $record ? $record->dhuhr : 'jamaah',
                'asr' => $record ? $record->asr : 'jamaah',
                'maghrib' => $record ? $record->maghrib : 'jamaah',
                'isha' => $record ? $record->isha : 'jamaah',
                'dhuha' => $record ? (bool) $record->dhuha : false,
                'tahajjud' => $record ? (bool) $record->tahajjud : false,
                'tilawah_pages' => $record ? (int) $record->tilawah_pages : 0,
                'fasting_type' => $record ? $record->fasting_type : 'none',
            ];
        }
    }

    public function updatePrayerStatus(int $studentId, string $prayer, string $status): void
    {
        abort_unless($this->canManage(), 403);

        if (isset($this->worshipState[$studentId])) {
            $this->worshipState[$studentId][$prayer] = $status;
            $this->persistStudentWorship($studentId);
        }
    }

    public function toggleSunnah(int $studentId, string $prayer): void
    {
        abort_unless($this->canManage(), 403);

        if (isset($this->worshipState[$studentId])) {
            $this->worshipState[$studentId][$prayer] = ! $this->worshipState[$studentId][$prayer];
            $this->persistStudentWorship($studentId);
        }
    }

    public function updateTilawah(int $studentId, mixed $pages): void
    {
        abort_unless($this->canManage(), 403);

        if (isset($this->worshipState[$studentId])) {
            $this->worshipState[$studentId]['tilawah_pages'] = max(0, (int) $pages);
            $this->persistStudentWorship($studentId);
        }
    }

    public function markAllJamaah(): void
    {
        abort_unless($this->canManage(), 403);

        foreach (array_keys($this->worshipState) as $studentId) {
            $this->worshipState[$studentId]['fajr'] = 'jamaah';
            $this->worshipState[$studentId]['dhuhr'] = 'jamaah';
            $this->worshipState[$studentId]['asr'] = 'jamaah';
            $this->worshipState[$studentId]['maghrib'] = 'jamaah';
            $this->worshipState[$studentId]['isha'] = 'jamaah';

            $this->persistStudentWorship($studentId);
        }

        $this->dispatch('notify', message: 'Semua shalat fardhu ditandai Berjamaah!', type: 'success');
    }

    protected function persistStudentWorship(int $studentId): void
    {
        $state = $this->worshipState[$studentId] ?? null;
        if (! $state) {
            return;
        }

        StudentDailyWorship::updateOrCreate(
            [
                'student_id' => $studentId,
                'date' => $this->date,
            ],
            [
                'fajr' => $state['fajr'],
                'dhuhr' => $state['dhuhr'],
                'asr' => $state['asr'],
                'maghrib' => $state['maghrib'],
                'isha' => $state['isha'],
                'dhuha' => $state['dhuha'],
                'tahajjud' => $state['tahajjud'],
                'tilawah_pages' => $state['tilawah_pages'],
                'fasting_type' => $state['fasting_type'],
                'recorded_by' => auth()->id(),
            ]
        );
    }

    public function render(): View
    {
        $schoolClasses = SchoolClass::query()->orderBy('grade_level')->orderBy('name')->get();

        $students = collect();
        if ($this->selectedClassId) {
            $students = Student::query()
                ->whereHas('enrollments', fn ($q) => $q->where('class_id', $this->selectedClassId)->where('status', 'active'))
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        }

        return view('livewire.islamic.mutabaah-worship', [
            'schoolClasses' => $schoolClasses,
            'students' => $students,
        ]);
    }
}
