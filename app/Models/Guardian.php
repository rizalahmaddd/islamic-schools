<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\GuardianFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guardian extends Model
{
    use Auditable;

    /** @use HasFactory<GuardianFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'nik',
        'name',
        'relationship',
        'phone',
        'email',
        'occupation',
        'address',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Student, $this>
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'guardian_id');
    }
}
