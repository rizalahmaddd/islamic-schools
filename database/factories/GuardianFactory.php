<?php

namespace Database\Factories;

use App\Models\Guardian;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Guardian>
 */
class GuardianFactory extends Factory
{
    protected $model = Guardian::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'nik' => fake()->numerify('3201############'),
            'name' => fake()->name(),
            'relationship' => fake()->randomElement(['Ayah', 'Ibu', 'Wali']),
            'phone' => '08'.fake()->numerify('##########'),
            'email' => fake()->unique()->safeEmail(),
            'occupation' => fake()->randomElement(['PNS', 'Wiraswasta', 'Karyawan Swasta', 'Guru', 'Pedagang']),
            'address' => fake()->address(),
        ];
    }
}
