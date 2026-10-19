<?php

namespace Database\Factories;

use App\Models\DormBuilding;
use App\Models\DormRoom;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DormRoom>
 */
class DormRoomFactory extends Factory
{
    protected $model = DormRoom::class;

    public function definition(): array
    {
        return [
            'dorm_building_id' => DormBuilding::factory(),
            'room_number' => 'Kamar '.$this->faker->numberBetween(101, 399),
            'capacity' => 8,
            'musyrif_id' => User::factory(),
            'description' => 'Kamar santri.',
        ];
    }
}
