<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\OnlinePaymentTransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnlinePaymentTransaction extends Model
{
    use Auditable;

    /** @use HasFactory<OnlinePaymentTransactionFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'student_bill_id',
        'student_id',
        'payer_user_id',
        'payment_channel',
        'payment_channel_name',
        'amount',
        'admin_fee',
        'gross_amount',
        'provider',
        'reference',
        'checkout_url',
        'qr_url',
        'qr_string',
        'va_number',
        'status',
        'paid_at',
        'expires_at',
        'payment_transaction_id',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'admin_fee' => 'decimal:2',
            'gross_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
            'raw_payload' => 'array',
        ];
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSettled(): bool
    {
        return $this->status === 'settlement';
    }

    public function isExpired(): bool
    {
        return in_array($this->status, ['expired', 'failed'], true);
    }

    public function statusBadgeColor(): string
    {
        return match ($this->status) {
            'settlement' => 'emerald',
            'pending' => 'amber',
            'expired', 'failed' => 'rose',
            default => 'slate',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'settlement' => 'Lunas (Settled)',
            'pending' => 'Menunggu Pembayaran',
            'expired' => 'Kedaluwarsa',
            'failed' => 'Gagal',
            default => ucfirst((string) $this->status),
        };
    }

    /**
     * @return BelongsTo<StudentBill, $this>
     */
    public function studentBill(): BelongsTo
    {
        return $this->belongsTo(StudentBill::class);
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
    public function payerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_user_id');
    }

    /**
     * @return BelongsTo<PaymentTransaction, $this>
     */
    public function paymentTransaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class);
    }
}
