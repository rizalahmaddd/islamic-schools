<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\StudentAttendanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAttendance extends Model
{
    use Auditable;

    /** @use HasFactory<StudentAttendanceFactory> */
    use HasFactory;

    public const STATUS_PRESENT = 'present';

    public const STATUS_SICK = 'sick';

    public const STATUS_PERMIT = 'permit';

    public const STATUS_ABSENT = 'absent';

    protected $fillable = [
        'student_id',
        'class_id',
        'date',
        'status',
        'notes',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function setDateAttribute(mixed $value): void
    {
        $this->attributes['date'] = $value instanceof \DateTimeInterface
            ? $value->format('Y-m-d')
            : substr((string) $value, 0, 10);
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * @return BelongsTo<SchoolClass, $this>
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PRESENT => 'Hadir',
            self::STATUS_SICK => 'Sakit',
            self::STATUS_PERMIT => 'Izin',
            self::STATUS_ABSENT => 'Alpa',
            default => ucfirst((string) $this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PRESENT => 'emerald',
            self::STATUS_SICK => 'amber',
            self::STATUS_PERMIT => 'sky',
            self::STATUS_ABSENT => 'rose',
            default => 'slate',
        };
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_PRESENT => 'Hadir',
            self::STATUS_SICK => 'Sakit',
            self::STATUS_PERMIT => 'Izin',
            self::STATUS_ABSENT => 'Alpa',
        ];
    }
}
