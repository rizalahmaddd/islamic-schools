<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\WalletTransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WalletTransaction extends Model
{
    use Auditable;

    /** @use HasFactory<WalletTransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'student_wallet_id',
        'rfid_card_id',
        'transaction_type',
        'amount',
        'balance_before',
        'balance_after',
        'canteen_merchant_id',
        'description',
        'cashier_user_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_before' => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<StudentWallet, $this>
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(StudentWallet::class, 'student_wallet_id');
    }

    /**
     * @return BelongsTo<RfidCard, $this>
     */
    public function rfidCard(): BelongsTo
    {
        return $this->belongsTo(RfidCard::class, 'rfid_card_id');
    }

    /**
     * @return BelongsTo<CanteenMerchant, $this>
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(CanteenMerchant::class, 'canteen_merchant_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_user_id');
    }

    /**
     * @return HasOne<CanteenOrder, $this>
     */
    public function order(): HasOne
    {
        return $this->hasOne(CanteenOrder::class);
    }
}
