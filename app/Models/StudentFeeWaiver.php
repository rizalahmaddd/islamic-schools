<?php

namespace App\Models;

use Database\Factories\StudentFeeWaiverFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentFeeWaiver extends Model
{
    /** @use HasFactory<StudentFeeWaiverFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'fee_category_id',
        'academic_year_id',
        'discount_type',
        'discount_value',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
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
     * @return BelongsTo<FeeCategory, $this>
     */
    public function feeCategory(): BelongsTo
    {
        return $this->belongsTo(FeeCategory::class);
    }

    /**
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
