<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentAttendance>
 */
class StudentAttendanceFactory extends Factory
{
    protected $model = StudentAttendance::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'class_id' => SchoolClass::factory(),
            'date' => now()->toDateString(),
            'status' => 'present',
            'notes' => null,
            'recorded_by' => User::factory(),
        ];
    }

    public function sick(): static
    {
        return $this->state(fn () => [
            'status' => 'sick',
            'notes' => 'Demam dan flu',
        ]);
    }

    public function permit(): static
    {
        return $this->state(fn () => [
            'status' => 'permit',
            'notes' => 'Acara keluarga di luar kota',
        ]);
    }

    public function absent(): static
    {
        return $this->state(fn () => [
            'status' => 'absent',
            'notes' => 'Tanpa keterangan',
        ]);
    }
}
