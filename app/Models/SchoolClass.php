<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\SchoolClassFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolClass extends Model
{
    use Auditable;

    /** @use HasFactory<SchoolClassFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'classes';

    protected $fillable = [
        'academic_year_id',
        'name',
        'grade_level',
        'homeroom_teacher_id',
        'capacity',
    ];

    protected function casts(): array
    {
        return [
            'grade_level' => 'integer',
            'capacity' => 'integer',
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
     * @return BelongsTo<Teacher, $this>
     */
    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'homeroom_teacher_id');
    }

    /**
     * @return HasMany<StudentEnrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class, 'class_id');
    }

    /**
     * @return HasManyThrough<Student, StudentEnrollment, $this>
     */
    public function students(): HasManyThrough
    {
        return $this->hasManyThrough(
            Student::class,
            StudentEnrollment::class,
            'class_id',
            'id',
            'id',
            'student_id'
        );
    }

    /**
     * @return HasMany<StudentAttendance, $this>
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(StudentAttendance::class, 'class_id');
    }

    /**
     * @return HasMany<SubjectAssignment, $this>
     */
    public function subjectAssignments(): HasMany
    {
        return $this->hasMany(SubjectAssignment::class, 'class_id');
    }

    /**
     * @return HasMany<ReportCardSummary, $this>
     */
    public function reportCardSummaries(): HasMany
    {
        return $this->hasMany(ReportCardSummary::class, 'class_id');
    }
}
