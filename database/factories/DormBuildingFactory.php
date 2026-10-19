<?php

namespace Database\Factories;

use App\Models\DormBuilding;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DormBuilding>
 */
class DormBuildingFactory extends Factory
{
    protected $model = DormBuilding::class;

    public function definition(): array
    {
        return [
            'name' => 'Gedung '.$this->faker->unique()->randomElement(['Abu Bakar', 'Umar bin Khattab', 'Utsman bin Affan', 'Ali bin Abi Thalib', 'Khadijah', 'Aisyah', 'Fathimah']),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'description' => 'Asrama pondok pesantren.',
        ];
    }
}
