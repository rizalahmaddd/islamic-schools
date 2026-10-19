<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CanteenOrder extends Model
{
    use Auditable;
    use HasFactory;

    protected $fillable = [
        'order_number',
        'wallet_transaction_id',
        'canteen_merchant_id',
        'student_id',
        'total_amount',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<WalletTransaction, $this>
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class, 'wallet_transaction_id');
    }

    /**
     * @return BelongsTo<CanteenMerchant, $this>
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(CanteenMerchant::class, 'canteen_merchant_id');
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return HasMany<CanteenOrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(CanteenOrderItem::class);
    }
}
