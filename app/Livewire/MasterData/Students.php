<?php

namespace App\Livewire\MasterData;

use App\Livewire\Concerns\WithCrudActions;
use App\Models\AcademicYear;
use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Services\StudentExcelService;
use App\Support\Phone;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app', ['heading' => 'Data Siswa'])]
#[Title('Data Siswa')]
class Students extends Component
{
    use WithCrudActions, WithFileUploads;

    // Form fields Siswa
    public string $nis = '';

    public string $nisn = '';

    public string $nik = '';

    public string $name = '';

    public string $nickname = '';

    public string $gender = 'L';

    public string $pob = '';

    public string $dob = '';

    public string $phone = '';

    public string $address = '';

    public string $status = 'active';

    public ?int $target_class_id = null;

    // Form fields Wali
    public string $guardian_name = '';

    public string $guardian_phone = '';

    public string $guardian_relationship = 'Ayah';

    public string $guardian_occupation = '';

    // Filter properties
    public string $classFilter = '';

    public string $genderFilter = '';

    public string $statusFilter = 'active';

    // Import Excel properties
    public $importFile = null;

    public bool $showImportModal = false;

    public ?array $importSummary = null;

    public function save(): void
    {
        $this->authorizeManage();

        $validated = $this->validate();
        $isEditing = (bool) $this->editingId;

        $guardianPhone = Phone::international($this->guardian_phone);

        // 1. Simpan atau cari Wali Murid
        $guardian = Guardian::firstOrCreate(
            ['phone' => $guardianPhone],
            [
                'name' => $this->guardian_name,
                'relationship' => $this->guardian_relationship,
                'occupation' => $this->guardian_occupation,
                'address' => $this->address,
            ]
        );

        if ($isEditing) {
            $student = Student::findOrFail($this->editingId);
            $student->update([
                'guardian_id' => $guardian->id,
                'nis' => $this->nis,
                'nisn' => $this->nisn ?: null,
                'nik' => $this->nik ?: null,
                'name' => $this->name,
                'nickname' => $this->nickname ?: null,
                'gender' => $this->gender,
                'pob' => $this->pob ?: null,
                'dob' => $this->dob ?: null,
                'phone' => $this->phone ? Phone::international($this->phone) : null,
                'address' => $this->address ?: null,
                'status' => $this->status,
            ]);
        } else {
            $student = Student::create([
                'guardian_id' => $guardian->id,
                'nis' => $this->nis,
                'nisn' => $this->nisn ?: null,
                'nik' => $this->nik ?: null,
                'name' => $this->name,
                'nickname' => $this->nickname ?: null,
                'gender' => $this->gender,
                'pob' => $this->pob ?: null,
                'dob' => $this->dob ?: null,
                'phone' => $this->phone ? Phone::international($this->phone) : null,
                'address' => $this->address ?: null,
                'status' => $this->status,
            ]);
        }

        // 2. Hubungkan ke rombel kelas jika ada
        if ($this->target_class_id) {
            $targetClass = SchoolClass::find($this->target_class_id);
            if ($targetClass) {
                StudentEnrollment::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'academic_year_id' => $targetClass->academic_year_id,
                    ],
                    [
                        'class_id' => $targetClass->id,
                        'enrolled_at' => now(),
                        'status' => 'active',
                    ]
                );
            }
        }

        $this->closeModal();
        $this->resetPage();
        $this->notify($isEditing ? __('Data santri diperbarui.') : __('Santri baru berhasil didaftarkan.'));
    }

    public function downloadTemplate()
    {
        return app(StudentExcelService::class)->downloadTemplate();
    }

    public function openImportModal(): void
    {
        $this->authorizeManage();
        $this->importFile = null;
        $this->importSummary = null;
        $this->showImportModal = true;
        $this->dispatch('open-modal', 'import-excel');
    }

    public function importExcel(): void
    {
        $this->authorizeManage();

        $this->validate([
            'importFile' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ]);

        $result = app(StudentExcelService::class)->import($this->importFile);
        $this->importSummary = $result;
        $this->importFile = null;
        $this->resetPage();

        if ($result['imported'] > 0) {
            $this->notify(__("Berhasil mengimpor {$result['imported']} siswa!"));
        }
    }

    public function export(string $format = 'xlsx')
    {
        $query = Student::query()
            ->with(['guardian', 'currentEnrollment.schoolClass'])
            ->when($this->search, function ($q) {
                $q->where(function ($search) {
                    $search->where('name', 'like', "%{$this->search}%")
                        ->orWhere('nis', 'like', "%{$this->search}%")
                        ->orWhere('nisn', 'like', "%{$this->search}%")
                        ->orWhereHas('guardian', fn ($g) => $g->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->classFilter, function ($q) {
                $q->whereHas('currentEnrollment', fn ($e) => $e->where('class_id', $this->classFilter));
            })
            ->when($this->genderFilter, fn ($q) => $q->where('gender', $this->genderFilter))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter));

        $this->applySorting($query, [
            'nis' => 'nis',
            'name' => 'name',
            'gender' => 'gender',
            'status' => 'status',
        ], 'name', 'asc');

        $headers = ['NIS', 'NISN', 'Nama Lengkap', 'L/P', 'Kelas Aktif', 'Nama Wali', 'No. WA Wali', 'Status'];

        $rows = $query->get()->map(fn (Student $s) => [
            $s->nis,
            $s->nisn ?: '-',
            $s->name,
            $s->gender,
            $s->currentClass()?->name ?? 'Belum ada kelas',
            $s->guardian?->name ?? '-',
            $s->guardian?->phone ?? '-',
            $s->statusLabel(),
        ]);

        return $this->exportFormattedResponse('data-siswa', $headers, $rows, 'Data Induk Siswa & Santri', 'Seluruh data santri dan penempatan rombel', $format);
    }

    protected function guardDelete($record): ?string
    {
        if ($record->bills()->whereIn('status', ['unpaid', 'partially_paid'])->exists()) {
            return __('Santri ini masih punya tagihan yang belum lunas. Selesaikan tagihannya atau ubah status menjadi Pindah/Keluar.');
        }

        if ((float) $record->wallet?->balance > 0) {
            return __('Dompet kantin santri ini masih ada saldo. Selesaikan saldonya terlebih dahulu.');
        }

        return null;
    }

    public function render()
    {
        $activeYear = AcademicYear::where('is_active', true)->first();

        $query = Student::query()
            ->with(['guardian', 'currentEnrollment.schoolClass'])
            ->when($this->search, function ($q) {
                $q->where(function ($search) {
                    $search->where('name', 'like', "%{$this->search}%")
                        ->orWhere('nis', 'like', "%{$this->search}%")
                        ->orWhere('nisn', 'like', "%{$this->search}%")
                        ->orWhereHas('guardian', fn ($g) => $g->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->classFilter, function ($q) {
                $q->whereHas('currentEnrollment', fn ($e) => $e->where('class_id', $this->classFilter));
            })
            ->when($this->genderFilter, fn ($q) => $q->where('gender', $this->genderFilter))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter));

        $this->applySorting($query, [
            'nis' => 'nis',
            'name' => 'name',
            'gender' => 'gender',
            'status' => 'status',
        ], 'name', 'asc');

        return view('livewire.master-data.students', [
            'students' => $query->paginate($this->perPage),
            'schoolClasses' => $activeYear ? SchoolClass::where('academic_year_id', $activeYear->id)->orderBy('grade_level')->orderBy('name')->get() : collect(),
            'activeYear' => $activeYear,
        ]);
    }

    protected function modelClass(): string
    {
        return Student::class;
    }

    protected function resetForm(): void
    {
        $this->reset([
            'nis', 'nisn', 'nik', 'name', 'nickname', 'pob', 'dob', 'phone', 'address',
            'guardian_name', 'guardian_phone', 'guardian_occupation', 'target_class_id',
        ]);
        $this->gender = 'L';
        $this->status = 'active';
        $this->guardian_relationship = 'Ayah';
    }

    protected function fillForm($record): void
    {
        $this->nis = $record->nis;
        $this->nisn = (string) $record->nisn;
        $this->nik = (string) $record->nik;
        $this->name = $record->name;
        $this->nickname = (string) $record->nickname;
        $this->gender = (string) $record->gender;
        $this->pob = (string) $record->pob;
        $this->dob = $record->dob?->format('Y-m-d') ?? '';
        $this->phone = (string) $record->phone;
        $this->address = (string) $record->address;
        $this->status = (string) $record->status;
        $this->target_class_id = $record->currentClass()?->id;

        if ($record->guardian) {
            $this->guardian_name = $record->guardian->name;
            $this->guardian_phone = $record->guardian->phone;
            $this->guardian_relationship = (string) $record->guardian->relationship;
            $this->guardian_occupation = (string) $record->guardian->occupation;
        }
    }

    protected function rules(): array
    {
        return [
            'nis' => ['required', 'string', 'max:50', Rule::unique('students', 'nis')->ignore($this->editingId)],
            'nisn' => ['nullable', 'string', 'max:20', Rule::unique('students', 'nisn')->ignore($this->editingId)],
            'nik' => ['nullable', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:150'],
            'nickname' => ['nullable', 'string', 'max:50'],
            'gender' => ['required', 'in:L,P'],
            'pob' => ['nullable', 'string', 'max:100'],
            'dob' => ['nullable', 'date'],
            'phone' => Phone::rules(required: false),
            'address' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:active,graduated,transferred,dropped_out'],
            'target_class_id' => ['nullable', 'exists:classes,id'],
            'guardian_name' => ['required', 'string', 'max:150'],
            'guardian_phone' => Phone::rules(),
            'guardian_relationship' => ['required', 'string', 'max:50'],
            'guardian_occupation' => ['nullable', 'string', 'max:100'],
        ];
    }
}
