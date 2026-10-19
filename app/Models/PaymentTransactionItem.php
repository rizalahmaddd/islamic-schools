<?php

namespace App\Models;

use Database\Factories\PaymentTransactionItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransactionItem extends Model
{
    /** @use HasFactory<PaymentTransactionItemFactory> */
    use HasFactory;

    protected $fillable = [
        'payment_transaction_id',
        'student_bill_id',
        'amount_allocated',
    ];

    protected function casts(): array
    {
        return [
            'amount_allocated' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<PaymentTransaction, $this>
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }

    /**
     * @return BelongsTo<StudentBill, $this>
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(StudentBill::class, 'student_bill_id');
    }
}
