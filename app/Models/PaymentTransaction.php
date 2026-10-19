<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\PaymentTransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentTransaction extends Model
{
    use Auditable;

    /** @use HasFactory<PaymentTransactionFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'receipt_number',
        'student_id',
        'total_amount',
        'payment_method',
        'received_by',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash' => 'Tunai',
            'transfer' => 'Transfer Bank',
            'qris' => 'QRIS',
            default => ucfirst((string) $this->payment_method),
        };
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * @return HasMany<PaymentTransactionItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(PaymentTransactionItem::class);
    }
}
