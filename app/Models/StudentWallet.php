<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\StudentWalletFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentWallet extends Model
{
    use Auditable;

    /** @use HasFactory<StudentWalletFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'balance',
        'daily_limit',
        'pin_hash',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'daily_limit' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return HasMany<WalletTransaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Menghitung total belanja santri hari ini untuk validasi daily limit.
     */
    public function spentToday(): float
    {
        return (float) $this->transactions()
            ->where('transaction_type', 'purchase')
            ->whereDate('created_at', now()->toDateString())
            ->sum('amount');
    }

    public function remainingDailyLimit(): float
    {
        return max(0, (float) $this->daily_limit - $this->spentToday());
    }
}
