<?php

namespace App\Livewire\Inventory;

use App\Livewire\Concerns\WithCrudActions;
use App\Models\InventoryRoom;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app', ['heading' => 'Ruangan & Lokasi Aset'])]
#[Title('Ruangan & Lokasi Aset')]
class RoomIndex extends Component
{
    use WithCrudActions;

    public string $code = '';

    public string $name = '';

    public ?string $building = null;

    public ?string $pic_name = null;

    public ?string $pic_phone = null;

    public ?string $description = null;

    public function canManage(): bool
    {
        return auth()->user()?->can('manage-inventory') ?? false;
    }

    protected function modelClass(): string
    {
        return InventoryRoom::class;
    }

    protected function resetForm(): void
    {
        $this->code = '';
        $this->name = '';
        $this->building = null;
        $this->pic_name = null;
        $this->pic_phone = null;
        $this->description = null;
    }

    /**
     * @param  InventoryRoom  $record
     */
    protected function fillForm($record): void
    {
        $this->code = $record->code;
        $this->name = $record->name;
        $this->building = $record->building;
        $this->pic_name = $record->pic_name;
        $this->pic_phone = $record->pic_phone;
        $this->description = $record->description;
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('inventory_rooms', 'code')->ignore($this->editingId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:255'],
            'pic_name' => ['nullable', 'string', 'max:255'],
            'pic_phone' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function save(): void
    {
        $this->authorizeManage();
        $validated = $this->validate();

        if ($this->editingId) {
            $room = InventoryRoom::findOrFail($this->editingId);
            $room->update($validated);
            $this->notify(__('Ruangan aset berhasil diperbarui.'));
        } else {
            InventoryRoom::create($validated);
            $this->notify(__('Ruangan aset baru berhasil ditambahkan.'));
        }

        $this->closeModal();
    }

    protected function guardDelete($record): ?string
    {
        if ($record->items()->exists()) {
            return __('Ruangan ini masih memiliki barang/aset inventaris. Pindahkan atau hapus aset terlebih dahulu.');
        }

        return null;
    }

    public function render(): View
    {
        $rooms = InventoryRoom::query()
            ->withCount('items')
            ->withSum('items', 'price')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('code', 'like', "%{$this->search}%")
                        ->orWhere('building', 'like', "%{$this->search}%")
                        ->orWhere('pic_name', 'like', "%{$this->search}%");
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.inventory.room-index', [
            'rooms' => $rooms,
        ]);
    }
}
