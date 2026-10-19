<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\TahfidzSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TahfidzSession extends Model
{
    use Auditable;

    /** @use HasFactory<TahfidzSessionFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'teacher_id',
        'session_type',
        'surah_id',
        'verse_start',
        'verse_end',
        'juz',
        'grade',
        'tajwid_notes',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'verse_start' => 'integer',
            'verse_end' => 'integer',
            'juz' => 'integer',
            'recorded_at' => 'datetime',
        ];
    }

    public function getGradeLabelAttribute(): string
    {
        return match ($this->grade) {
            'A' => 'Mumtaz (Sangat Baik / Lancar)',
            'B' => 'Jayyid Jiddan (Baik Sekali)',
            'C' => 'Jayyid (Baik / Cukup)',
            'D' => 'Maqbul (Perlu Diulang)',
            default => $this->grade,
        };
    }

    public function getSessionTypeLabelAttribute(): string
    {
        return match ($this->session_type) {
            'ziyadah' => 'Ziyadah (Hafalan Baru)',
            'murajaah' => 'Muraja\'ah (Mengulang)',
            default => ucfirst((string) $this->session_type),
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
     * @return BelongsTo<Teacher, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * @return BelongsTo<QuranSurah, $this>
     */
    public function surah(): BelongsTo
    {
        return $this->belongsTo(QuranSurah::class, 'surah_id');
    }
}
