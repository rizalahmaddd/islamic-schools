<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\TeacherFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    use Auditable;

    /** @use HasFactory<TeacherFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'nip',
        'nuptk',
        'nik',
        'name',
        'gender',
        'phone',
        'email',
        'specialization',
        'address',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<Teacher>  $query
     * @return Builder<Teacher>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<SchoolClass, $this>
     */
    public function homeroomClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'homeroom_teacher_id');
    }

    /**
     * @return HasMany<TeacherAttendance, $this>
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(TeacherAttendance::class, 'teacher_id');
    }

    /**
     * @return HasMany<TeachingJournal, $this>
     */
    public function teachingJournals(): HasMany
    {
        return $this->hasMany(TeachingJournal::class, 'teacher_id');
    }

    /**
     * @return HasMany<SubjectAssignment, $this>
     */
    public function subjectAssignments(): HasMany
    {
        return $this->hasMany(SubjectAssignment::class, 'teacher_id');
    }
}
