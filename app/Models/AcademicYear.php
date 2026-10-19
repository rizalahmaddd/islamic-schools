<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\AcademicYearFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicYear extends Model
{
    use Auditable;

    /** @use HasFactory<AcademicYearFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'semester',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<SchoolClass, $this>
     */
    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'academic_year_id');
    }

    /**
     * @return HasMany<StudentEnrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class, 'academic_year_id');
    }

    /**
     * @return HasMany<FeeStructure, $this>
     */
    public function feeStructures(): HasMany
    {
        return $this->hasMany(FeeStructure::class, 'academic_year_id');
    }

    /**
     * @return HasMany<StudentBill, $this>
     */
    public function studentBills(): HasMany
    {
        return $this->hasMany(StudentBill::class, 'academic_year_id');
    }

    /**
     * @return HasMany<SubjectAssignment, $this>
     */
    public function subjectAssignments(): HasMany
    {
        return $this->hasMany(SubjectAssignment::class, 'academic_year_id');
    }

    /**
     * @return HasMany<ReportCardSummary, $this>
     */
    public function reportCardSummaries(): HasMany
    {
        return $this->hasMany(ReportCardSummary::class, 'academic_year_id');
    }

    /**
     * @param  Builder<AcademicYear>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function activate(): void
    {
        static::query()->where('id', '!=', $this->id)->update(['is_active' => false]);
        $this->update(['is_active' => true]);
    }
}
