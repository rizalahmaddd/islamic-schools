<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryRoom;
use Illuminate\Support\Str;

class InventoryService
{
    public function __construct(
        protected DocumentNumberGenerator $docGenerator,
    ) {}

    /**
     * Mendaftarkan barang inventaris baru beserta kode aset & token QR unik.
     *
     * @param  array<string, mixed>  $data
     */
    public function createItem(array $data): InventoryItem
    {
        $code = ! empty($data['code']) ? $data['code'] : $this->docGenerator->next(prefix: 'AST', pad: 4);
        $qrToken = 'AST-QR-'.Str::upper(Str::random(12));

        return InventoryItem::create(array_merge($data, [
            'code' => $code,
            'qr_code_token' => $qrToken,
        ]));
    }

    /**
     * Memindahkan barang inventaris ke ruangan lain.
     */
    public function moveItem(InventoryItem $item, InventoryRoom $targetRoom, ?string $reason = null): InventoryItem
    {
        $oldRoomName = $item->room?->name ?? 'Ruangan Lama';

        $notes = trim(($item->notes ? $item->notes."\n" : '').'['.now()->format('d/m/Y H:i')."] Dipindahkan dari {$oldRoomName} ke {$targetRoom->name}".($reason ? ": {$reason}" : ''));

        $item->update([
            'inventory_room_id' => $targetRoom->id,
            'notes' => $notes,
        ]);

        return $item->fresh('room');
    }

    /**
     * Memperbarui kondisi fisik barang aset.
     */
    public function updateCondition(InventoryItem $item, string $condition, ?string $notes = null): InventoryItem
    {
        $item->update([
            'condition' => $condition,
            'notes' => $notes ?? $item->notes,
        ]);

        return $item->fresh();
    }
}
