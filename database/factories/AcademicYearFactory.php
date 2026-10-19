<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    protected $model = AcademicYear::class;

    public function definition(): array
    {
        $year = fake()->numberBetween(2025, 2030);

        return [
            'name' => "{$year}/".($year + 1),
            'semester' => fake()->randomElement(['ganjil', 'genap']),
            'start_date' => "{$year}-07-15",
            'end_date' => ($year + 1).'-06-20',
            'is_active' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'is_active' => true,
        ]);
    }
}
