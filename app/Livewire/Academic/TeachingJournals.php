<?php

namespace App\Livewire\Academic;

use App\Livewire\Concerns\WithCrudActions;
use App\Livewire\Concerns\WithRealtimeRefresh;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app', ['heading' => 'Jurnal Mengajar Harian'])]
#[Title('Jurnal Mengajar')]
class TeachingJournals extends Component
{
    use WithCrudActions, WithFileUploads, WithRealtimeRefresh;

    #[Url]
    public ?int $classFilter = null;

    #[Url]
    public ?int $subjectFilter = null;

    #[Url]
    public ?string $dateFilter = null;

    // Form inputs
    public string $date = '';

    public ?int $teacher_id = null;

    public ?int $class_id = null;

    public ?int $subject_id = null;

    public int $period_start = 1;

    public int $period_end = 2;

    public string $topic_taught = '';

    public string $learning_activities = '';

    public string $notes = '';

    public $photo = null;

    public ?string $existingPhotoPath = null;

    protected function modelClass(): string
    {
        return TeachingJournal::class;
    }

    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
        $this->setDefaultTeacher();
    }

    protected function setDefaultTeacher(): void
    {
        $user = Auth::user();
        $matchedTeacher = Teacher::where('user_id', $user?->id)->first()
            ?? Teacher::where('email', $user?->email)->first();

        if ($matchedTeacher) {
            $this->teacher_id = $matchedTeacher->id;
        }
    }

    protected function resetForm(): void
    {
        $this->date = now()->format('Y-m-d');
        $this->class_id = null;
        $this->subject_id = null;
        $this->period_start = 1;
        $this->period_end = 2;
        $this->topic_taught = '';
        $this->learning_activities = '';
        $this->notes = '';
        $this->photo = null;
        $this->existingPhotoPath = null;

        $this->setDefaultTeacher();
    }

    protected function fillForm($record): void
    {
        /** @var TeachingJournal $record */
        $this->date = $record->date ? Carbon::parse($record->date)->format('Y-m-d') : now()->format('Y-m-d');
        $this->teacher_id = $record->teacher_id;
        $this->class_id = $record->class_id;
        $this->subject_id = $record->subject_id;
        $this->period_start = $record->period_start;
        $this->period_end = $record->period_end;
        $this->topic_taught = $record->topic_taught;
        $this->learning_activities = (string) $record->learning_activities;
        $this->notes = (string) $record->notes;
        $this->photo = null;
        $this->existingPhotoPath = $record->evidence_photo_path;
    }

    public function canManage(): bool
    {
        return Gate::allows('manage-teaching-journal') || Gate::allows('manage-attendance');
    }

    protected function authorizeManage(): void
    {
        if (! $this->canManage()) {
            abort(403, 'Anda tidak memiliki hak akses mengelola jurnal mengajar.');
        }
    }

    public function save(): void
    {
        $this->authorizeManage();

        $this->validate([
            'date' => 'required|date',
            'teacher_id' => 'required|exists:teachers,id',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'period_start' => 'required|integer|min:1|max:15',
            'period_end' => 'required|integer|min:1|max:15|gte:period_start',
            'topic_taught' => 'required|string|max:255',
            'learning_activities' => 'nullable|string',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|max:3072',
        ]);

        $activeYear = AcademicYear::active()->first();
        if (! $activeYear) {
            $activeYear = AcademicYear::first();
        }

        $photoPath = $this->existingPhotoPath;
        if ($this->photo) {
            $photoPath = $this->photo->store('teaching-journals', 'public');
        }

        $data = [
            'academic_year_id' => $activeYear?->id ?? 1,
            'teacher_id' => $this->teacher_id,
            'class_id' => $this->class_id,
            'subject_id' => $this->subject_id,
            'date' => $this->date,
            'period_start' => $this->period_start,
            'period_end' => $this->period_end,
            'topic_taught' => $this->topic_taught,
            'learning_activities' => filled($this->learning_activities) ? $this->learning_activities : null,
            'notes' => filled($this->notes) ? $this->notes : null,
            'evidence_photo_path' => $photoPath,
        ];

        if ($this->editingId) {
            TeachingJournal::findOrFail($this->editingId)->update($data);
            $message = 'Jurnal mengajar berhasil diperbarui.';
        } else {
            TeachingJournal::create($data);
            $message = 'Jurnal mengajar berhasil dicatat.';
        }

        $this->closeModal();
        $this->dispatch('notify', $message);
    }

    public function export(string $format = 'xlsx')
    {
        $headers = [
            'Tanggal',
            'Guru Pengampu',
            'Kelas / Rombel',
            'Mata Pelajaran',
            'Jam Ke',
            'Materi / Topik',
            'Aktivitas Pembelajaran',
            'Catatan',
        ];

        $journals = TeachingJournal::with(['teacher', 'schoolClass', 'subject'])
            ->orderBy('date', 'desc')
            ->get();

        $rows = $journals->map(function (TeachingJournal $j) {
            return [
                $j->date ? Carbon::parse($j->date)->translatedFormat('d F Y') : '-',
                $j->teacher?->name ?? '-',
                $j->schoolClass?->name ?? '-',
                $j->subject?->name ?? 'Mata Pelajaran Umum',
                "Jam ke {$j->period_start} - {$j->period_end}",
                $j->topic_taught,
                $j->learning_activities ?? '-',
                $j->notes ?? '-',
            ];
        })->all();

        return $this->exportFormattedResponse('jurnal-mengajar', $headers, $rows, 'Jurnal Mengajar Harian', 'Rekap aktivitas ajar guru di kelas', $format);
    }

    public function render(): View
    {
        $schoolClasses = SchoolClass::orderBy('name')->get();
        $subjects = Subject::active()->orderBy('name')->get();
        $teachers = Teacher::active()->orderBy('name')->get();

        $query = TeachingJournal::with(['teacher', 'schoolClass', 'subject'])
            ->when($this->classFilter, fn ($q) => $q->where('class_id', $this->classFilter))
            ->when($this->subjectFilter, fn ($q) => $q->where('subject_id', $this->subjectFilter))
            ->when($this->dateFilter, fn ($q) => $q->where('date', $this->dateFilter))
            ->when(filled($this->search), function (Builder $q) {
                $q->where(function ($sub) {
                    $sub->where('topic_taught', 'like', "%{$this->search}%")
                        ->orWhere('learning_activities', 'like', "%{$this->search}%")
                        ->orWhereHas('teacher', fn ($t) => $t->where('name', 'like', "%{$this->search}%"))
                        ->orWhereHas('schoolClass', fn ($c) => $c->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->orderBy('date', 'desc')
            ->orderBy('period_start', 'asc');

        $journals = $query->paginate($this->perPage);

        return view('livewire.academic.teaching-journals', [
            'journals' => $journals,
            'schoolClasses' => $schoolClasses,
            'subjects' => $subjects,
            'teachers' => $teachers,
        ]);
    }
}
