<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentDailyWorship;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentDailyWorship>
 */
class StudentDailyWorshipFactory extends Factory
{
    protected $model = StudentDailyWorship::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'date' => now()->toDateString(),
            'fajr' => 'jamaah',
            'dhuhr' => 'jamaah',
            'asr' => 'jamaah',
            'maghrib' => 'jamaah',
            'isha' => 'jamaah',
            'dhuha' => true,
            'tahajjud' => true,
            'tilawah_pages' => 4,
            'fasting_type' => 'none',
            'notes' => 'Ibadah tertib',
            'recorded_by' => User::factory(),
        ];
    }
}
