<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\SubjectFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use Auditable;

    /** @use HasFactory<SubjectFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'category',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<Subject>  $query
     * @return Builder<Subject>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @return HasMany<TeachingJournal, $this>
     */
    public function teachingJournals(): HasMany
    {
        return $this->hasMany(TeachingJournal::class, 'subject_id');
    }

    /**
     * @return HasMany<SubjectAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(SubjectAssignment::class, 'subject_id');
    }
}
