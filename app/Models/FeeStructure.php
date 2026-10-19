<?php

namespace App\Models;

use Database\Factories\FeeStructureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeStructure extends Model
{
    /** @use HasFactory<FeeStructureFactory> */
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'fee_category_id',
        'grade_level',
        'default_amount',
    ];

    protected function casts(): array
    {
        return [
            'default_amount' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * @return BelongsTo<FeeCategory, $this>
     */
    public function feeCategory(): BelongsTo
    {
        return $this->belongsTo(FeeCategory::class);
    }
}
