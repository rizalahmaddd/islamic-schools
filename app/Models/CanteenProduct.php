<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\CanteenProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CanteenProduct extends Model
{
    use Auditable;

    /** @use HasFactory<CanteenProductFactory> */
    use HasFactory;

    protected $fillable = [
        'canteen_merchant_id',
        'code',
        'name',
        'category',
        'price',
        'stock',
        'is_available',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'is_available' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<CanteenMerchant, $this>
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(CanteenMerchant::class, 'canteen_merchant_id');
    }
}
