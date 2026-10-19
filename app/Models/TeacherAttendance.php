<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\TeacherAttendanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAttendance extends Model
{
    use Auditable;

    /** @use HasFactory<TeacherAttendanceFactory> */
    use HasFactory;

    public const STATUS_PRESENT = 'present';

    public const STATUS_LATE = 'late';

    public const STATUS_PERMIT = 'permit';

    public const STATUS_SICK = 'sick';

    public const STATUS_ABSENT = 'absent';

    protected $fillable = [
        'teacher_id',
        'date',
        'check_in_time',
        'check_out_time',
        'method',
        'latitude',
        'longitude',
        'distance_meters',
        'status',
        'notes',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'latitude' => 'float',
            'longitude' => 'float',
            'distance_meters' => 'integer',
        ];
    }

    public function setDateAttribute(mixed $value): void
    {
        $this->attributes['date'] = $value instanceof \DateTimeInterface
            ? $value->format('Y-m-d')
            : substr((string) $value, 0, 10);
    }

    /**
     * @return BelongsTo<Teacher, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
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
            self::STATUS_PRESENT => 'Hadir Tepat Waktu',
            self::STATUS_LATE => 'Terlambat',
            self::STATUS_PERMIT => 'Izin',
            self::STATUS_SICK => 'Sakit',
            self::STATUS_ABSENT => 'Alpa',
            default => ucfirst((string) $this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PRESENT => 'emerald',
            self::STATUS_LATE => 'amber',
            self::STATUS_PERMIT => 'sky',
            self::STATUS_SICK => 'rose',
            self::STATUS_ABSENT => 'slate',
            default => 'slate',
        };
    }

    /**
     * Hitung jarak dua titik koordinat GPS menggunakan formula Haversine (hasil dalam meter).
     */
    public static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Radius bumi dalam meter
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($angle * $earthRadius, 1);
    }
}
