<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\ReportCardSummary;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportCardSummary>
 */
class ReportCardSummaryFactory extends Factory
{
    protected $model = ReportCardSummary::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'class_id' => SchoolClass::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'sick_count' => fake()->numberBetween(0, 3),
            'permit_count' => fake()->numberBetween(0, 2),
            'absent_count' => fake()->numberBetween(0, 1),
            'extracurricular_notes' => 'Aktif dalam kegiatan Pramuka dan Muhadhoroh.',
            'homeroom_notes' => 'Tingkatkan terus semangat belajar dan hafalan Al-Qur\'an.',
            'promotion_status' => 'promoted',
            'is_locked' => false,
        ];
    }
}
