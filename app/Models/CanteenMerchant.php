<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\CanteenMerchantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CanteenMerchant extends Model
{
    use Auditable;

    /** @use HasFactory<CanteenMerchantFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'pic_name',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<CanteenProduct, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(CanteenProduct::class);
    }
}
