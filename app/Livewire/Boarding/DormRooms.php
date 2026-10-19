<?php

namespace App\Livewire\Boarding;

use App\Livewire\Concerns\WithCrudActions;
use App\Models\AcademicYear;
use App\Models\DormBuilding;
use App\Models\DormPlacement;
use App\Models\DormRoom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app', ['heading' => 'Kamar & Gedung Asrama'])]
#[Title('Kamar & Gedung Asrama')]
class DormRooms extends Component
{
    use WithCrudActions;

    public string $activeTab = 'rooms'; // 'rooms' | 'placements'

    // Form fields for DormRoom
    public ?int $dorm_building_id = null;

    public string $room_number = '';

    public int $capacity = 8;

    public ?int $musyrif_id = null;

    public ?string $description = null;

    // Form fields for Building modal
    public bool $showBuildingModal = false;

    public ?int $editingBuildingId = null;

    public string $building_name = '';

    public string $building_gender = 'male';

    public ?string $building_description = null;

    // Placement form
    public bool $showPlacementModal = false;

    public ?int $placement_student_id = null;

    public ?int $placement_room_id = null;

    public string $placement_bed_number = '';

    // Filters
    public string $buildingFilter = '';

    public string $roomFilter = '';

    public string $academicYearFilter = '';

    public function mount(): void
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        if ($activeYear) {
            $this->academicYearFilter = (string) $activeYear->id;
        }

        $firstBuilding = DormBuilding::first();
        if ($firstBuilding) {
            $this->dorm_building_id = $firstBuilding->id;
        }
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    // --- CRUD DORM ROOM ---
    protected function modelClass(): string
    {
        return DormRoom::class;
    }

    protected function rules(): array
    {
        return [
            'dorm_building_id' => ['required', 'exists:dorm_buildings,id'],
            'room_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('dorm_rooms', 'room_number')
                    ->where('dorm_building_id', $this->dorm_building_id)
                    ->ignore($this->editingId),
            ],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'musyrif_id' => ['nullable', 'exists:users,id'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @param  DormRoom  $record
     */
    protected function fillForm($record): void
    {
        $this->dorm_building_id = $record->dorm_building_id;
        $this->room_number = $record->room_number;
        $this->capacity = $record->capacity;
        $this->musyrif_id = $record->musyrif_id;
        $this->description = $record->description;
    }

    protected function resetForm(): void
    {
        $this->room_number = '';
        $this->capacity = 8;
        $this->musyrif_id = null;
        $this->description = null;
        if (! $this->dorm_building_id) {
            $this->dorm_building_id = DormBuilding::first()?->id;
        }
    }

    public function save(): void
    {
        $this->authorizeManage();
        $validated = $this->validate();

        $isEditing = (bool) $this->editingId;
        if ($isEditing) {
            DormRoom::findOrFail($this->editingId)->update($validated);
        } else {
            DormRoom::create($validated);
        }

        $this->closeModal();
        $this->resetPage();
        $this->notify($isEditing ? __('Kamar asrama diperbarui.') : __('Kamar asrama baru ditambahkan.'));
    }

    // --- CRUD DORM BUILDING MODAL ---
    public function openCreateBuildingModal(): void
    {
        $this->authorizeManage();
        $this->editingBuildingId = null;
        $this->building_name = '';
        $this->building_gender = 'male';
        $this->building_description = null;
        $this->showBuildingModal = true;
        $this->dispatch('open-modal', 'building-form');
    }

    public function saveBuilding(): void
    {
        $this->authorizeManage();
        $this->validate([
            'building_name' => ['required', 'string', 'max:100'],
            'building_gender' => ['required', 'in:male,female'],
            'building_description' => ['nullable', 'string', 'max:500'],
        ]);

        DormBuilding::create([
            'name' => $this->building_name,
            'gender' => $this->building_gender,
            'description' => $this->building_description,
        ]);

        $this->showBuildingModal = false;
        $this->dispatch('close-modal', 'building-form');
        $this->notify(__('Gedung asrama baru berhasil ditambahkan.'));
    }

    // --- PLACEMENT ACTIONS ---
    public function openPlacementModal(?int $roomId = null): void
    {
        $this->authorizeManage();
        $this->placement_room_id = $roomId ?: ($this->roomFilter ? (int) $this->roomFilter : DormRoom::first()?->id);
        $this->placement_student_id = null;
        $this->placement_bed_number = '';
        $this->showPlacementModal = true;
        $this->dispatch('open-modal', 'placement-form');
    }

    public function savePlacement(): void
    {
        $this->authorizeManage();
        $this->validate([
            'placement_room_id' => ['required', 'exists:dorm_rooms,id'],
            'placement_student_id' => ['required', 'exists:students,id'],
            'placement_bed_number' => ['nullable', 'string', 'max:30'],
        ]);

        $academicYearId = (int) ($this->academicYearFilter ?: AcademicYear::where('is_active', true)->firstOrFail()->id);
        $room = DormRoom::findOrFail($this->placement_room_id);

        if ($room->availableBeds($academicYearId) <= 0) {
            $this->addError('placement_room_id', 'Kamar ini sudah penuh (kapasitas '.$room->capacity.' santri).');

            return;
        }

        // Cek apakah santri sudah ditempatkan di tahun ajaran ini
        DormPlacement::updateOrCreate(
            [
                'student_id' => $this->placement_student_id,
                'academic_year_id' => $academicYearId,
            ],
            [
                'dorm_room_id' => $this->placement_room_id,
                'bed_number' => $this->placement_bed_number ?: null,
                'placed_at' => now()->toDateString(),
            ]
        );

        $this->showPlacementModal = false;
        $this->dispatch('close-modal', 'placement-form');
        $this->notify(__('Santri berhasil ditempatkan di kamar asrama.'));
    }

    public function removePlacement(int $placementId): void
    {
        $this->authorizeManage();
        $placement = DormPlacement::findOrFail($placementId);
        $placement->delete();
        $this->notify(__('Penempatan santri di kamar berhasil dilepas.'));
    }

    protected function authorizeManage(): void
    {
        abort_unless(auth()->user()->can('boarding.manage') || auth()->user()->hasRole('superadmin'), 403);
    }

    public function render()
    {
        $academicYearId = (int) ($this->academicYearFilter ?: AcademicYear::where('is_active', true)->first()?->id);

        $buildings = DormBuilding::withCount('rooms')->orderBy('name')->get();
        $musyrikin = User::role(['musyrif', 'admin', 'guru', 'superadmin'])->orderBy('name')->get();
        $allRooms = DormRoom::with('building')->orderBy('room_number')->get();

        if ($this->activeTab === 'placements') {
            $placementsQuery = DormPlacement::query()
                ->with(['student.guardian', 'room.building', 'academicYear'])
                ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
                ->when($this->roomFilter, fn ($q) => $q->where('dorm_room_id', $this->roomFilter))
                ->when($this->buildingFilter, fn ($q) => $q->whereHas('room', fn ($r) => $r->where('dorm_building_id', $this->buildingFilter)))
                ->when($this->search, function ($q) {
                    $q->whereHas('student', fn ($s) => $s->where('name', 'like', "%{$this->search}%")
                        ->orWhere('nis', 'like', "%{$this->search}%"));
                })
                ->latest();

            $placements = $placementsQuery->paginate($this->perPage);

            // Santri yang belum ditempatkan di tahun ajaran aktif untuk pilihan modal
            $placedStudentIds = DormPlacement::where('academic_year_id', $academicYearId)->pluck('student_id');
            $availableStudents = Student::where('status', 'active')
                ->whereNotIn('id', $placedStudentIds)
                ->orderBy('name')
                ->limit(100)
                ->get();

            return view('livewire.boarding.dorm-rooms', [
                'buildings' => $buildings,
                'musyrikin' => $musyrikin,
                'allRooms' => $allRooms,
                'placements' => $placements,
                'availableStudents' => $availableStudents,
                'academicYears' => AcademicYear::orderByDesc('start_date')->get(),
            ]);
        }

        // Tab Rooms
        $roomsQuery = DormRoom::query()
            ->with(['building', 'musyrif'])
            ->withCount(['placements' => fn ($q) => $q->where('academic_year_id', $academicYearId)])
            ->when($this->buildingFilter, fn ($q) => $q->where('dorm_building_id', $this->buildingFilter))
            ->when($this->search, fn ($q) => $q->where(fn ($search) => $search->where('room_number', 'like', "%{$this->search}%")
                ->orWhere('description', 'like', "%{$this->search}%")))
            ->orderBy('room_number');

        $rooms = $roomsQuery->paginate($this->perPage);

        return view('livewire.boarding.dorm-rooms', [
            'buildings' => $buildings,
            'musyrikin' => $musyrikin,
            'allRooms' => $allRooms,
            'rooms' => $rooms,
            'academicYears' => AcademicYear::orderByDesc('start_date')->get(),
        ]);
    }
}
