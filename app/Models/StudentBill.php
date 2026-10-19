<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\StudentBillFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentBill extends Model
{
    use Auditable;

    /** @use HasFactory<StudentBillFactory> */
    use HasFactory;

    public const MONTH_NAMES = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    protected $fillable = [
        'student_id',
        'fee_category_id',
        'academic_year_id',
        'month',
        'year',
        'amount_due',
        'amount_paid',
        'status',
        'due_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'month' => 'integer',
            'year' => 'integer',
            'amount_due' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'due_date' => 'date',
        ];
    }

    public function setDueDateAttribute(mixed $value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['due_date'] = null;

            return;
        }

        $this->attributes['due_date'] = $value instanceof \DateTimeInterface
            ? $value->format('Y-m-d')
            : substr((string) $value, 0, 10);
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->amount_due - (float) $this->amount_paid);
    }

    public function getMonthNameAttribute(): ?string
    {
        return $this->month ? (self::MONTH_NAMES[$this->month] ?? (string) $this->month) : null;
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * @param  Builder<StudentBill>  $query
     * @return Builder<StudentBill>
     */
    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->whereIn('status', ['unpaid', 'partially_paid']);
    }

    /**
     * @param  Builder<StudentBill>  $query
     * @return Builder<StudentBill>
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<FeeCategory, $this>
     */
    public function feeCategory(): BelongsTo
    {
        return $this->belongsTo(FeeCategory::class);
    }

    /**
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * @return HasMany<PaymentTransactionItem, $this>
     */
    public function transactionItems(): HasMany
    {
        return $this->hasMany(PaymentTransactionItem::class);
    }
}
