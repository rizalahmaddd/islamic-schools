<?php

namespace App\Livewire\Inventory;

use App\Livewire\Concerns\WithCrudActions;
use App\Models\InventoryItem;
use App\Models\InventoryRoom;
use App\Services\InventoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app', ['heading' => 'Inventaris Barang & Aset'])]
#[Title('Inventaris Barang & Aset')]
class InventoryIndex extends Component
{
    use WithCrudActions;

    public ?int $inventory_room_id = null;

    public string $code = '';

    public string $name = '';

    public string $category = 'mebel';

    public ?string $brand = null;

    public string $condition = 'baik';

    public ?string $source_of_fund = 'yayasan';

    public ?string $purchase_date = null;

    public float $price = 0;

    public int $quantity = 1;

    public string $unit = 'unit';

    public ?string $notes = null;

    // Filters
    public string $roomFilter = '';

    public string $conditionFilter = '';

    public string $categoryFilter = '';

    public function canManage(): bool
    {
        return auth()->user()?->can('manage-inventory') ?? false;
    }

    protected function modelClass(): string
    {
        return InventoryItem::class;
    }

    protected function resetForm(): void
    {
        $this->inventory_room_id = InventoryRoom::query()->value('id');
        $this->code = '';
        $this->name = '';
        $this->category = 'mebel';
        $this->brand = null;
        $this->condition = 'baik';
        $this->source_of_fund = 'yayasan';
        $this->purchase_date = now()->format('Y-m-d');
        $this->price = 0;
        $this->quantity = 1;
        $this->unit = 'unit';
        $this->notes = null;
    }

    /**
     * @param  InventoryItem  $record
     */
    protected function fillForm($record): void
    {
        $this->inventory_room_id = $record->inventory_room_id;
        $this->code = $record->code;
        $this->name = $record->name;
        $this->category = $record->category;
        $this->brand = $record->brand;
        $this->condition = $record->condition;
        $this->purchase_date = $record->purchase_date instanceof \DateTimeInterface ? $record->purchase_date->format('Y-m-d') : ($record->purchase_date ? (string) $record->purchase_date : null);
        $this->price = (float) $record->price;
        $this->quantity = $record->quantity;
        $this->unit = $record->unit;
        $this->notes = $record->notes;
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'inventory_room_id' => ['required', 'exists:inventory_rooms,id'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('inventory_items', 'code')->ignore($this->editingId)],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:mebel,elektronik,alat_tulis,buku_referensi,peralatan_lab,kendaraan,lainnya'],
            'brand' => ['nullable', 'string', 'max:100'],
            'condition' => ['required', 'string', 'in:baik,rusak_ringan,rusak_berat,hilang'],
            'source_of_fund' => ['nullable', 'string', 'max:100'],
            'purchase_date' => ['nullable', 'date'],
            'price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit' => ['required', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function save(InventoryService $inventoryService): void
    {
        $this->authorizeManage();
        $validated = $this->validate();

        if ($this->editingId) {
            $item = InventoryItem::findOrFail($this->editingId);
            $item->update($validated);
            $this->notify(__('Barang inventaris berhasil diperbarui.'));
        } else {
            $inventoryService->createItem($validated);
            $this->notify(__('Barang inventaris baru dan label QR berhasil dibuat.'));
        }

        $this->closeModal();
    }

    public function updateCondition(int $id, string $condition, InventoryService $inventoryService): void
    {
        $this->authorizeManage();
        $item = InventoryItem::findOrFail($id);
        $inventoryService->updateCondition($item, $condition);
        $this->notify(__('Kondisi fisik barang berhasil diperbarui.'));
    }

    public function render(): View
    {
        $items = InventoryItem::query()
            ->with('room')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('code', 'like', "%{$this->search}%")
                        ->orWhere('brand', 'like', "%{$this->search}%")
                        ->orWhere('qr_code_token', 'like', "%{$this->search}%");
                });
            })
            ->when($this->roomFilter, fn ($q) => $q->where('inventory_room_id', $this->roomFilter))
            ->when($this->conditionFilter, fn ($q) => $q->where('condition', $this->conditionFilter))
            ->when($this->categoryFilter, fn ($q) => $q->where('category', $this->categoryFilter))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $rooms = InventoryRoom::query()->orderBy('name')->get(['id', 'name', 'code']);

        return view('livewire.inventory.inventory-index', [
            'items' => $items,
            'rooms' => $rooms,
        ]);
    }
}
