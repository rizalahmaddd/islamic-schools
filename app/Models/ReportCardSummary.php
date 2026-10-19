<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\ReportCardSummaryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportCardSummary extends Model
{
    use Auditable;

    /** @use HasFactory<ReportCardSummaryFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'class_id',
        'academic_year_id',
        'sick_count',
        'permit_count',
        'absent_count',
        'extracurricular_notes',
        'homeroom_notes',
        'promotion_status',
        'is_locked',
    ];

    protected function casts(): array
    {
        return [
            'sick_count' => 'integer',
            'permit_count' => 'integer',
            'absent_count' => 'integer',
            'is_locked' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * @return BelongsTo<SchoolClass, $this>
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    /**
     * @param  Builder<ReportCardSummary>  $query
     * @return Builder<ReportCardSummary>
     */
    public function scopeForClassAndYear(Builder $query, int $classId, int $academicYearId): Builder
    {
        return $query->where('class_id', $classId)->where('academic_year_id', $academicYearId);
    }
}
