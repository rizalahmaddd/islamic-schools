<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\DormPlacement;
use App\Models\DormRoom;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DormPlacement>
 */
class DormPlacementFactory extends Factory
{
    protected $model = DormPlacement::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'dorm_room_id' => DormRoom::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'bed_number' => 'Bed-'.$this->faker->numberBetween(1, 10),
            'placed_at' => now()->toDateString(),
        ];
    }
}
