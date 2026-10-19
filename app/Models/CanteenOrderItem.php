<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CanteenOrderItem extends Model
{
    use Auditable;
    use HasFactory;

    protected $fillable = [
        'canteen_order_id',
        'canteen_product_id',
        'product_name',
        'quantity',
        'price',
        'subtotal',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<CanteenOrder, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(CanteenOrder::class, 'canteen_order_id');
    }

    /**
     * @return BelongsTo<CanteenProduct, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(CanteenProduct::class, 'canteen_product_id');
    }
}
