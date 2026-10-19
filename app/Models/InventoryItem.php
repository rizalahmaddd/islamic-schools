<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\InventoryItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItem extends Model
{
    use Auditable;

    /** @use HasFactory<InventoryItemFactory> */
    use HasFactory;

    protected $fillable = [
        'inventory_room_id',
        'code',
        'name',
        'category',
        'brand',
        'condition',
        'source_of_fund',
        'purchase_date',
        'price',
        'quantity',
        'unit',
        'qr_code_token',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'price' => 'decimal:2',
            'quantity' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<InventoryRoom, $this>
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(InventoryRoom::class, 'inventory_room_id');
    }
}
