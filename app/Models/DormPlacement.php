<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\DormPlacementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DormPlacement extends Model
{
    use Auditable;

    /** @use HasFactory<DormPlacementFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'dorm_room_id',
        'academic_year_id',
        'bed_number',
        'placed_at',
    ];

    protected function casts(): array
    {
        return [
            'placed_at' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * @return BelongsTo<DormRoom, $this>
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(DormRoom::class, 'dorm_room_id');
    }

    /**
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
}
