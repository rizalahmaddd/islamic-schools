<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\InventoryRoomFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryRoom extends Model
{
    use Auditable;

    /** @use HasFactory<InventoryRoomFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'building',
        'pic_name',
        'pic_phone',
        'description',
    ];

    /**
     * @return HasMany<InventoryItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }
}
