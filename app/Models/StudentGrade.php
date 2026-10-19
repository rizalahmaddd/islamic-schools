<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\StudentGradeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentGrade extends Model
{
    use Auditable;

    /** @use HasFactory<StudentGradeFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'subject_assignment_id',
        'student_id',
        'formative_average',
        'mid_term_score',
        'final_term_score',
        'final_score',
        'predicate',
        'achievement_desc',
        'improvement_desc',
    ];

    protected function casts(): array
    {
        return [
            'formative_average' => 'decimal:2',
            'mid_term_score' => 'decimal:2',
            'final_term_score' => 'decimal:2',
            'final_score' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<SubjectAssignment, $this>
     */
    public function subjectAssignment(): BelongsTo
    {
        return $this->belongsTo(SubjectAssignment::class, 'subject_assignment_id');
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * @param  Builder<StudentGrade>  $query
     * @return Builder<StudentGrade>
     */
    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }
}
