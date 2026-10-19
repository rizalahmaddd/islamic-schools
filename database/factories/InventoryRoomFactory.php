<?php

namespace Database\Factories;

use App\Models\InventoryRoom;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryRoom>
 */
class InventoryRoomFactory extends Factory
{
    protected $model = InventoryRoom::class;

    public function definition(): array
    {
        return [
            'code' => 'R-'.fake()->unique()->numerify('###'),
            'name' => fake()->randomElement(['Ruang Kelas 7A', 'Laboratorium Komputer', 'Perpustakaan Utama', 'Gudang Sarpras', 'Ruang Guru']),
            'building' => fake()->randomElement(['Gedung Abu Bakar Lt. 1', 'Gedung Umar Lt. 2', 'Gedung Utsman Lt. 1']),
            'pic_name' => fake()->name(),
            'pic_phone' => '08'.fake()->numerify('##########'),
            'description' => fake()->sentence(),
        ];
    }
}
