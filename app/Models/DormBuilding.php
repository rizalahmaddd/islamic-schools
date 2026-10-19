<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\DormBuildingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DormBuilding extends Model
{
    use Auditable;

    /** @use HasFactory<DormBuildingFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'gender',
        'description',
    ];

    /**
     * @return HasMany<DormRoom, $this>
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(DormRoom::class, 'dorm_building_id');
    }

    public function totalCapacity(): int
    {
        return (int) $this->rooms()->sum('capacity');
    }

    public function genderLabel(): string
    {
        return match ($this->gender) {
            'male' => 'Putra (Banin)',
            'female' => 'Putri (Banat)',
            default => ucfirst((string) $this->gender),
        };
    }
}
