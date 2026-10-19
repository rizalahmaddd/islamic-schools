<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\RfidCardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RfidCard extends Model
{
    use Auditable;

    /** @use HasFactory<RfidCardFactory> */
    use HasFactory;

    protected $fillable = [
        'card_uid',
        'student_id',
        'label',
        'is_active',
        'blocked_at',
        'blocked_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'blocked_at' => 'datetime',
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

    public function isBlocked(): bool
    {
        return ! $this->is_active || $this->blocked_at !== null;
    }
}
