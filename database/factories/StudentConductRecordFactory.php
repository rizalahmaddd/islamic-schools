<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentConductRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentConductRecord>
 */
class StudentConductRecordFactory extends Factory
{
    protected $model = StudentConductRecord::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'date' => now()->toDateString(),
            'type' => 'violation',
            'points' => 10,
            'category' => 'Kedisiplinan & Waktu',
            'description' => 'Terlambat halaqah pagi',
            'action_taken' => 'Teguran lisan & istighfar',
            'recorded_by' => User::factory(),
        ];
    }

    public function merit(): static
    {
        return $this->state(fn () => [
            'type' => 'merit',
            'points' => 20,
            'category' => 'Prestasi & Kebaikan',
            'description' => 'Juara 1 Lomba Tahfidz Tingkat Kabupaten',
            'action_taken' => 'Pemberian sertifikat & apresiasi di apel',
        ]);
    }
}
