<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\TeachingJournalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeachingJournal extends Model
{
    use Auditable;

    /** @use HasFactory<TeachingJournalFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'academic_year_id',
        'teacher_id',
        'class_id',
        'subject_id',
        'date',
        'period_start',
        'period_end',
        'topic_taught',
        'learning_activities',
        'notes',
        'evidence_photo_path',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'period_start' => 'integer',
            'period_end' => 'integer',
        ];
    }

    public function setDateAttribute(mixed $value): void
    {
        $this->attributes['date'] = $value instanceof \DateTimeInterface
            ? $value->format('Y-m-d')
            : substr((string) $value, 0, 10);
    }

    /**
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    /**
     * @return BelongsTo<Teacher, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    /**
     * @return BelongsTo<SchoolClass, $this>
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * @return BelongsTo<Subject, $this>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
