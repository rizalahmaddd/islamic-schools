<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\StudentConductRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentConductRecord extends Model
{
    use Auditable;

    /** @use HasFactory<StudentConductRecordFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'date',
        'type',
        'points',
        'category',
        'description',
        'action_taken',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'points' => 'integer',
        ];
    }

    public function setDateAttribute(mixed $value): void
    {
        $this->attributes['date'] = $value instanceof \DateTimeInterface
            ? $value->format('Y-m-d')
            : substr((string) $value, 0, 10);
    }

    public function getTypeBadgeColor(): string
    {
        return $this->type === 'merit' ? 'emerald' : 'rose';
    }

    public function getTypeLabel(): string
    {
        return $this->type === 'merit' ? 'Prestasi / Kebaikan' : 'Pelanggaran Disiplin';
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
