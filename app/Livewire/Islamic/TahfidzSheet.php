<?php

namespace App\Livewire\Islamic;

use App\Models\QuranSurah;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\TahfidzSession;
use App\Models\Teacher;
use App\Services\TahfidzService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app', ['heading' => 'Buku Tahfidz Al-Qur\'an'])]
#[Title('Buku Tahfidz Al-Qur\'an')]
class TahfidzSheet extends Component
{
    public ?int $selectedClassId = null;

    public ?int $selectedStudentId = null;

    // Form fields input setoran
    public string $session_type = 'ziyadah'; // ziyadah, murajaah

    public ?int $surah_id = 78; // Default An-Naba' (Juz 30 awal)

    public int $verse_start = 1;

    public int $verse_end = 10;

    public int $juz = 30;

    public string $grade = 'A'; // A, B, C, D

    public string $tajwid_notes = '';

    public bool $send_wa = true;

    public ?int $editingSessionId = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('view-tahfidz'), 403);

        $firstClass = SchoolClass::query()->orderBy('grade_level')->orderBy('name')->first();
        if ($firstClass) {
            $this->selectedClassId = $firstClass->id;
            $firstStudent = Student::query()
                ->whereHas('enrollments', fn ($q) => $q->where('class_id', $firstClass->id)->where('status', 'active'))
                ->where('status', 'active')
                ->orderBy('name')
                ->first();

            if ($firstStudent) {
                $this->selectedStudentId = $firstStudent->id;
            }
        }
    }

    public function updatedSelectedClassId(): void
    {
        $this->selectedStudentId = null;
        if ($this->selectedClassId) {
            $firstStudent = Student::query()
                ->whereHas('enrollments', fn ($q) => $q->where('class_id', $this->selectedClassId)->where('status', 'active'))
                ->where('status', 'active')
                ->orderBy('name')
                ->first();

            if ($firstStudent) {
                $this->selectedStudentId = $firstStudent->id;
            }
        }
    }

    public function updatedSurahId(): void
    {
        $surah = QuranSurah::find($this->surah_id);
        if ($surah) {
            $this->juz = (int) $surah->juz_start;
            $this->verse_start = 1;
            $this->verse_end = min(10, (int) $surah->total_verses);
        }
    }

    public function selectStudent(int $id): void
    {
        $this->selectedStudentId = $id;
        $this->editingSessionId = null;
        $this->tajwid_notes = '';
    }

    public function canManage(): bool
    {
        return auth()->user()->can('manage-tahfidz');
    }

    public function saveSession(): void
    {
        abort_unless($this->canManage(), 403);

        $totalVerses = (int) QuranSurah::query()->whereKey($this->surah_id)->value('total_verses') ?: 286;

        $this->validate([
            'selectedStudentId' => ['required', 'exists:students,id'],
            'session_type' => ['required', 'in:ziyadah,murajaah'],
            'surah_id' => ['required', 'exists:quran_surahs,id'],
            'verse_start' => ['required', 'integer', 'min:1', "max:{$totalVerses}"],
            'verse_end' => ['required', 'integer', 'gte:verse_start', "max:{$totalVerses}"],
            'juz' => ['required', 'integer', 'between:1,30'],
            'grade' => ['required', 'in:A,B,C,D'],
            'tajwid_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $teacher = Teacher::query()->where('user_id', auth()->id())->first();
        $isNewSession = ! $this->editingSessionId;

        $session = TahfidzSession::updateOrCreate(
            ['id' => $this->editingSessionId],
            [
                'student_id' => $this->selectedStudentId,
                'teacher_id' => $teacher?->id,
                'session_type' => $this->session_type,
                'surah_id' => $this->surah_id,
                'verse_start' => $this->verse_start,
                'verse_end' => $this->verse_end,
                'juz' => $this->juz,
                'grade' => $this->grade,
                'tajwid_notes' => $this->tajwid_notes ?: null,
                'recorded_at' => now(),
            ]
        );

        if ($this->send_wa && $isNewSession) {
            app(TahfidzService::class)->sendTahfidzWhatsApp($session);
        }

        $this->editingSessionId = null;
        $this->tajwid_notes = '';
        $this->dispatch('notify', message: 'Setoran hafalan santri berhasil disimpan!', type: 'success');
    }

    public function deleteSession(int $id): void
    {
        abort_unless($this->canManage(), 403);

        TahfidzSession::findOrFail($id)->delete();
        $this->dispatch('notify', message: 'Catatan setoran berhasil dihapus.', type: 'success');
    }

    public function render(): View
    {
        $schoolClasses = SchoolClass::query()->orderBy('grade_level')->orderBy('name')->get();
        $surahs = QuranSurah::query()->orderBy('number')->get();

        $studentsInClass = collect();
        if ($this->selectedClassId) {
            $studentsInClass = Student::query()
                ->whereHas('enrollments', fn ($q) => $q->where('class_id', $this->selectedClassId)->where('status', 'active'))
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        }

        $selectedStudent = null;
        $progressData = null;
        $recentSessions = collect();

        if ($this->selectedStudentId) {
            $selectedStudent = Student::query()
                ->with(['guardian', 'currentEnrollment.schoolClass'])
                ->find($this->selectedStudentId);

            if ($selectedStudent) {
                $progressData = app(TahfidzService::class)->calculateStudentProgress($selectedStudent);
                $recentSessions = TahfidzSession::query()
                    ->with(['surah', 'teacher'])
                    ->where('student_id', $selectedStudent->id)
                    ->orderByDesc('recorded_at')
                    ->limit(10)
                    ->get();
            }
        }

        return view('livewire.islamic.tahfidz-sheet', [
            'schoolClasses' => $schoolClasses,
            'studentsInClass' => $studentsInClass,
            'selectedStudent' => $selectedStudent,
            'surahs' => $surahs,
            'progressData' => $progressData,
            'recentSessions' => $recentSessions,
        ]);
    }
}
