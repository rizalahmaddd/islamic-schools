<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\AdmissionCandidateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionCandidate extends Model
{
    use Auditable;

    /** @use HasFactory<AdmissionCandidateFactory> */
    use HasFactory;

    protected $fillable = [
        'admission_batch_id',
        'registration_number',
        'student_name',
        'gender',
        'nisn',
        'nik',
        'pob',
        'dob',
        'previous_school',
        'guardian_name',
        'guardian_relationship',
        'guardian_phone',
        'guardian_email',
        'address',
        'status',
        'test_score_academic',
        'test_score_quran',
        'test_score_interview',
        'test_notes',
        'converted_student_id',
        'converted_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'test_score_academic' => 'decimal:2',
            'test_score_quran' => 'decimal:2',
            'test_score_interview' => 'decimal:2',
            'converted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<AdmissionBatch, $this>
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(AdmissionBatch::class, 'admission_batch_id');
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function convertedStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'converted_student_id');
    }

    public function averageScore(): ?float
    {
        $scores = array_filter([
            $this->test_score_academic,
            $this->test_score_quran,
            $this->test_score_interview,
        ], fn ($s) => $s !== null);

        if (empty($scores)) {
            return null;
        }

        return round(array_sum($scores) / count($scores), 2);
    }

    public function isRegistered(): bool
    {
        return $this->status === 'registered';
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function isTested(): bool
    {
        return $this->status === 'tested';
    }

    public function isPassed(): bool
    {
        return $this->status === 'passed';
    }

    public function isEnrolled(): bool
    {
        return $this->status === 'enrolled';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'registered' => 'Terdaftar (Baru)',
            'verified' => 'Berkas Terverifikasi',
            'tested' => 'Selesai Tes Seleksi',
            'passed' => 'Lulus Seleksi',
            'rejected' => 'Tidak Lulus',
            'enrolled' => 'Santri Aktif (Dikonversi)',
            default => ucfirst((string) $this->status),
        };
    }

    public function statusBadgeColor(): string
    {
        return match ($this->status) {
            'registered' => 'slate',
            'verified' => 'amber',
            'tested' => 'sky',
            'passed' => 'emerald',
            'enrolled' => 'emerald',
            'rejected' => 'rose',
            default => 'slate',
        };
    }
}
