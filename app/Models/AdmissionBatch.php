<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\AdmissionBatchFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdmissionBatch extends Model
{
    use Auditable;

    /** @use HasFactory<AdmissionBatchFactory> */
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'name',
        'wave_number',
        'start_date',
        'end_date',
        'quota',
        'registration_fee',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'quota' => 'integer',
            'registration_fee' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    /**
     * @return HasMany<AdmissionCandidate, $this>
     */
    public function candidates(): HasMany
    {
        return $this->hasMany(AdmissionCandidate::class, 'admission_batch_id');
    }

    public function isOpen(): bool
    {
        $today = now()->toDateString();
        $start = $this->start_date instanceof \DateTimeInterface ? $this->start_date->format('Y-m-d') : (string) $this->start_date;
        $end = $this->end_date instanceof \DateTimeInterface ? $this->end_date->format('Y-m-d') : (string) $this->end_date;

        return $this->is_active && $start <= $today && $end >= $today;
    }

    public function isFull(): bool
    {
        return $this->quota > 0 && $this->remainingQuota() === 0;
    }

    /**
     * Alasan gelombang tidak menerima pendaftar baru, atau null kalau masih menerima.
     */
    public function closedReason(): ?string
    {
        return match (true) {
            ! $this->is_active => 'Gelombang pendaftaran ini sedang tidak aktif.',
            $this->start_date->isFuture() => 'Gelombang pendaftaran ini belum dibuka. Pendaftaran mulai '.$this->start_date->translatedFormat('d F Y').'.',
            $this->end_date->endOfDay()->isPast() => 'Gelombang pendaftaran ini sudah ditutup pada '.$this->end_date->translatedFormat('d F Y').'.',
            $this->isFull() => 'Kuota gelombang pendaftaran ini sudah penuh.',
            default => null,
        };
    }

    public function acceptsRegistrations(): bool
    {
        return $this->closedReason() === null;
    }

    public function registeredCount(): int
    {
        return $this->candidates()->count();
    }

    public function passedCount(): int
    {
        return $this->candidates()->whereIn('status', ['passed', 'enrolled'])->count();
    }

    public function enrolledCount(): int
    {
        return $this->candidates()->where('status', 'enrolled')->count();
    }

    public function remainingQuota(): int
    {
        return max(0, $this->quota - $this->passedCount());
    }
}
