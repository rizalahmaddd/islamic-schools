<?php

namespace Database\Factories;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Teacher>
 */
class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'nip' => fake()->unique()->numerify('19##########00#'),
            'nuptk' => fake()->numerify('################'),
            'nik' => fake()->numerify('3201############'),
            'name' => fake()->name(),
            'gender' => fake()->randomElement(['L', 'P']),
            'phone' => '08'.fake()->numerify('##########'),
            'email' => fake()->unique()->safeEmail(),
            'specialization' => fake()->randomElement(['Tahfidz Quran', 'Bahasa Arab', 'Fiqih', 'Matematika', 'IPA', 'Bahasa Inggris']),
            'address' => fake()->address(),
            'is_active' => true,
        ];
    }
}
