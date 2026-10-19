<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\StudentDailyWorshipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDailyWorship extends Model
{
    use Auditable;

    /** @use HasFactory<StudentDailyWorshipFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'date',
        'fajr',
        'dhuhr',
        'asr',
        'maghrib',
        'isha',
        'dhuha',
        'tahajjud',
        'tilawah_pages',
        'fasting_type',
        'notes',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'dhuha' => 'boolean',
            'tahajjud' => 'boolean',
            'tilawah_pages' => 'integer',
        ];
    }

    public function setDateAttribute(mixed $value): void
    {
        $this->attributes['date'] = $value instanceof \DateTimeInterface
            ? $value->format('Y-m-d')
            : substr((string) $value, 0, 10);
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
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
