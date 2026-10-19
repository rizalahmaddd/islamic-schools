<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\FeeCategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeCategory extends Model
{
    use Auditable;

    /** @use HasFactory<FeeCategoryFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<FeeCategory>  $query
     * @return Builder<FeeCategory>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<FeeCategory>  $query
     * @return Builder<FeeCategory>
     */
    public function scopeMonthly(Builder $query): Builder
    {
        return $query->where('type', 'monthly');
    }

    /**
     * @return HasMany<FeeStructure, $this>
     */
    public function feeStructures(): HasMany
    {
        return $this->hasMany(FeeStructure::class);
    }

    /**
     * @return HasMany<StudentBill, $this>
     */
    public function bills(): HasMany
    {
        return $this->hasMany(StudentBill::class);
    }
}
