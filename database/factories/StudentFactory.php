<?php

namespace Database\Factories;

use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'guardian_id' => Guardian::factory(),
            'nis' => fake()->unique()->numerify('2026####'),
            'nisn' => fake()->unique()->numerify('008#######'),
            'nik' => fake()->numerify('3201############'),
            'name' => fake()->name(),
            'nickname' => fake()->firstName(),
            'gender' => fake()->randomElement(['L', 'P']),
            'pob' => fake()->city(),
            'dob' => fake()->date('Y-m-d', '-13 years'),
            'phone' => '08'.fake()->numerify('##########'),
            'address' => fake()->address(),
            'status' => 'active',
            'photo_path' => null,
        ];
    }
}
