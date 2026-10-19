<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\SubjectAssignment;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubjectAssignment>
 */
class SubjectAssignmentFactory extends Factory
{
    protected $model = SubjectAssignment::class;

    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'class_id' => SchoolClass::factory(),
            'subject_id' => Subject::factory(),
            'teacher_id' => Teacher::factory(),
            'passing_grade' => 75.00,
        ];
    }
}
