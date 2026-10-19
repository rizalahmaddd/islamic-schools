<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\DormRoomFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DormRoom extends Model
{
    use Auditable;

    /** @use HasFactory<DormRoomFactory> */
    use HasFactory;

    protected $fillable = [
        'dorm_building_id',
        'room_number',
        'capacity',
        'musyrif_id',
        'description',
    ];

    /**
     * @return BelongsTo<DormBuilding, $this>
     */
    public function building(): BelongsTo
    {
        return $this->belongsTo(DormBuilding::class, 'dorm_building_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function musyrif(): BelongsTo
    {
        return $this->belongsTo(User::class, 'musyrif_id');
    }

    /**
     * @return HasMany<DormPlacement, $this>
     */
    public function placements(): HasMany
    {
        return $this->hasMany(DormPlacement::class, 'dorm_room_id');
    }

    public function occupiedCount(?int $academicYearId = null): int
    {
        $query = $this->placements();
        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        return $query->count();
    }

    public function availableBeds(?int $academicYearId = null): int
    {
        return max(0, $this->capacity - $this->occupiedCount($academicYearId));
    }
}
