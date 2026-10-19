<?php

namespace Database\Factories;

use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherAttendance>
 */
class TeacherAttendanceFactory extends Factory
{
    protected $model = TeacherAttendance::class;

    public function definition(): array
    {
        return [
            'teacher_id' => Teacher::factory(),
            'date' => now()->toDateString(),
            'check_in_time' => '07:15:00',
            'check_out_time' => '15:00:00',
            'method' => 'gps',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'distance_meters' => 25,
            'status' => 'present',
            'notes' => null,
            'recorded_by' => User::factory(),
        ];
    }

    public function late(): static
    {
        return $this->state(fn () => [
            'check_in_time' => '07:45:00',
            'status' => 'late',
            'notes' => 'Macet perjalanan',
        ]);
    }

    public function permit(): static
    {
        return $this->state(fn () => [
            'check_in_time' => null,
            'check_out_time' => null,
            'status' => 'permit',
            'notes' => 'Izin keperluan keluarga',
        ]);
    }
}
