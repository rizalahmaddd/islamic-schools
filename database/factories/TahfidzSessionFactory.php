<?php

namespace Database\Factories;

use App\Models\QuranSurah;
use App\Models\Student;
use App\Models\TahfidzSession;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TahfidzSession>
 */
class TahfidzSessionFactory extends Factory
{
    protected $model = TahfidzSession::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'teacher_id' => Teacher::factory(),
            'session_type' => 'ziyadah',
            'surah_id' => QuranSurah::factory(),
            'verse_start' => 1,
            'verse_end' => 10,
            'juz' => 30,
            'grade' => 'A',
            'tajwid_notes' => 'Lancar dan tajwid sesuai kaidah',
            'recorded_at' => now(),
        ];
    }
}
