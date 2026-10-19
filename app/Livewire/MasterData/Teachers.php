<?php

namespace App\Livewire\MasterData;

use App\Livewire\Concerns\WithCrudActions;
use App\Models\Teacher;
use App\Support\Phone;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app', ['heading' => 'Guru & Staf'])]
#[Title('Guru & Staf')]
class Teachers extends Component
{
    use WithCrudActions;

    public string $nip = '';

    public string $nuptk = '';

    public string $nik = '';

    public string $name = '';

    public string $gender = 'L';

    public string $phone = '';

    public string $email = '';

    public string $specialization = '';

    public string $address = '';

    public bool $is_active = true;

    public string $genderFilter = '';

    public string $statusFilter = '';

    public function save(): void
    {
        $this->authorizeManage();

        $validated = $this->validate();
        $isEditing = (bool) $this->editingId;

        $validated['phone'] = Phone::international($validated['phone']);

        if ($isEditing) {
            Teacher::findOrFail($this->editingId)->update($validated);
        } else {
            Teacher::create($validated);
        }

        $this->closeModal();
        $this->resetPage();
        $this->notify($isEditing ? __('Data guru diperbarui.') : __('Data guru ditambahkan.'));
    }

    public function export(string $format = 'xlsx')
    {
        $query = Teacher::query()
            ->when($this->search, function ($query) {
                $query->where(function ($search) {
                    $search->where('name', 'like', "%{$this->search}%")
                        ->orWhere('nip', 'like', "%{$this->search}%")
                        ->orWhere('phone', 'like', "%{$this->search}%")
                        ->orWhere('specialization', 'like', "%{$this->search}%");
                });
            })
            ->when($this->genderFilter, fn ($q) => $q->where('gender', $this->genderFilter))
            ->when($this->statusFilter !== '', fn ($q) => $q->where('is_active', $this->statusFilter === 'active'));

        $this->applySorting($query, [
            'name' => 'name',
            'nip' => 'nip',
            'gender' => 'gender',
            'phone' => 'phone',
            'specialization' => 'specialization',
            'is_active' => 'is_active',
        ], 'name', 'asc');

        $headers = ['NIP', 'Nama Guru / Ustadz', 'L/P', 'No Telepon/WA', 'Email', 'Bidang/Mata Pelajaran', 'Status'];

        $rows = $query->get()->map(fn (Teacher $t) => [
            $t->nip ?: '-',
            $t->name,
            $t->gender === 'L' ? 'Laki-laki' : 'Perempuan',
            $t->phone,
            $t->email ?: '-',
            $t->specialization ?: '-',
            $t->is_active ? 'Aktif' : 'Nonaktif',
        ]);

        return $this->exportFormattedResponse('guru-dan-staf', $headers, $rows, 'Data Guru & Tenaga Kependidikan', 'Seluruh data dewan guru dan ustadz', $format);
    }

    protected function guardDelete($record): ?string
    {
        if ($record->homeroomClasses()->exists()) {
            return __('Guru ini masih menjadi wali kelas. Ganti wali kelasnya terlebih dahulu.');
        }

        if ($record->subjectAssignments()->exists()) {
            return __('Guru ini masih punya pembagian tugas mengajar. Pindahkan penugasannya terlebih dahulu.');
        }

        return null;
    }

    public function render()
    {
        $query = Teacher::query()
            ->when($this->search, function ($query) {
                $query->where(function ($search) {
                    $search->where('name', 'like', "%{$this->search}%")
                        ->orWhere('nip', 'like', "%{$this->search}%")
                        ->orWhere('phone', 'like', "%{$this->search}%")
                        ->orWhere('specialization', 'like', "%{$this->search}%");
                });
            })
            ->when($this->genderFilter, fn ($q) => $q->where('gender', $this->genderFilter))
            ->when($this->statusFilter !== '', fn ($q) => $q->where('is_active', $this->statusFilter === 'active'));

        $this->applySorting($query, [
            'name' => 'name',
            'nip' => 'nip',
            'gender' => 'gender',
            'phone' => 'phone',
            'specialization' => 'specialization',
            'is_active' => 'is_active',
        ], 'name', 'asc');

        return view('livewire.master-data.teachers', [
            'teachers' => $query->paginate($this->perPage),
        ]);
    }

    protected function modelClass(): string
    {
        return Teacher::class;
    }

    protected function resetForm(): void
    {
        $this->reset(['nip', 'nuptk', 'nik', 'name', 'phone', 'email', 'specialization', 'address']);
        $this->gender = 'L';
        $this->is_active = true;
    }

    protected function fillForm($record): void
    {
        $this->nip = (string) $record->nip;
        $this->nuptk = (string) $record->nuptk;
        $this->nik = (string) $record->nik;
        $this->name = $record->name;
        $this->gender = (string) $record->gender;
        $this->phone = $record->phone;
        $this->email = (string) $record->email;
        $this->specialization = (string) $record->specialization;
        $this->address = (string) $record->address;
        $this->is_active = (bool) $record->is_active;
    }

    protected function rules(): array
    {
        return [
            'nip' => ['nullable', 'string', 'max:50', Rule::unique('teachers', 'nip')->ignore($this->editingId)],
            'nuptk' => ['nullable', 'string', 'max:50'],
            'nik' => ['nullable', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:150'],
            'gender' => ['required', 'in:L,P'],
            'phone' => Phone::rules(),
            'email' => ['nullable', 'email', 'max:150'],
            'specialization' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ];
    }
}
