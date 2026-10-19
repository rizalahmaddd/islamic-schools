<?php

namespace App\Livewire\MasterData;

use App\Models\Student;
use App\Support\Audit\AuditTrail;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

class StudentShow extends Component
{
    public Student $student;

    public function mount(Student $student): void
    {
        $this->student = $student->load([
            'guardian',
            'enrollments.schoolClass.homeroomTeacher',
            'enrollments.academicYear',
            'dormPlacements.room.building',
            'wallet',
            'rfidCards',
            'conductRecords' => fn ($q) => $q->latest()->limit(5),
            'tahfidzSessions' => fn ($q) => $q->with('surah')->latest('recorded_at')->limit(5),
            'bills' => fn ($q) => $q->with('feeCategory')->unpaid()->orderBy('due_date'),
        ]);
    }

    /**
     * @return Collection<int, Activity>
     */
    #[Computed]
    public function history(): Collection
    {
        return Activity::query()
            ->with('causer')
            ->where('log_name', AuditTrail::LOG_NAME)
            ->whereMorphedTo('subject', $this->student)
            ->latest('id')
            ->limit(10)
            ->get();
    }

    public function canViewActivityLog(): bool
    {
        return auth()->user()->can('viewAny', Activity::class);
    }

    public function render()
    {
        return view('livewire.master-data.student-show')
            ->layout('layouts.app', ['heading' => $this->student->name])
            ->title($this->student->name.' — Detail Siswa');
    }
}
