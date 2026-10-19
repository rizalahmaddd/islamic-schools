<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentGrade;
use App\Models\SubjectAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentGrade>
 */
class StudentGradeFactory extends Factory
{
    protected $model = StudentGrade::class;

    public function definition(): array
    {
        $formative = fake()->randomFloat(2, 70, 95);
        $mid = fake()->randomFloat(2, 65, 95);
        $final = fake()->randomFloat(2, 70, 98);
        $score = round(($formative * 0.5) + ($mid * 0.25) + ($final * 0.25), 2);

        $predicate = match (true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            default => 'D',
        };

        return [
            'subject_assignment_id' => SubjectAssignment::factory(),
            'student_id' => Student::factory(),
            'formative_average' => $formative,
            'mid_term_score' => $mid,
            'final_term_score' => $final,
            'final_score' => $score,
            'predicate' => $predicate,
            'achievement_desc' => 'Menunjukkan pemahaman yang sangat baik dalam penguasaan materi pembelajaran.',
            'improvement_desc' => 'Perlu meningkatkan ketelitian dalam menyelesaikan tugas mandiri.',
        ];
    }
}
