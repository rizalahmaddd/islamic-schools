<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolClass>
 */
class SchoolClassFactory extends Factory
{
    protected $model = SchoolClass::class;

    public function definition(): array
    {
        $grade = fake()->numberBetween(7, 9);
        $section = fake()->randomElement(['A', 'B', 'C', 'Tahfidz 1', 'Tahfidz 2']);

        return [
            'academic_year_id' => AcademicYear::factory(),
            'name' => "{$grade}{$section}",
            'grade_level' => $grade,
            'homeroom_teacher_id' => Teacher::factory(),
            'capacity' => 36,
        ];
    }
}
