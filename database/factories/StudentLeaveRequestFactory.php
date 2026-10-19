<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\StudentLeaveRequest;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<StudentLeaveRequest>
 */
class StudentLeaveRequestFactory extends Factory
{
    protected $model = StudentLeaveRequest::class;

    public function definition(): array
    {
        return [
            'leave_number' => 'IZN-'.now()->format('Ym').'-'.str_pad((string) $this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'student_id' => Student::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'leave_type' => $this->faker->randomElement(['weekend', 'sick', 'family', 'dispensation']),
            'reason' => 'Keperluan keluarga dan pemeriksaan kesehatan berkala.',
            'departure_plan' => now()->addDay()->setHour(8)->setMinute(0),
            'return_plan' => now()->addDays(2)->setHour(17)->setMinute(0),
            'pickup_person' => 'Orang Tua / Wali Murid (08123456789)',
            'status' => 'pending',
            'qr_pass_token' => Str::random(32),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved_mentor',
            'approved_at' => now(),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'approved_at' => now()->subHours(2),
            'actual_departure_at' => now()->subHour(),
        ]);
    }

    public function returned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'returned',
            'approved_at' => now()->subDays(2),
            'actual_departure_at' => now()->subDays(2)->addHour(),
            'actual_returned_at' => now()->subHour(),
        ]);
    }
}
