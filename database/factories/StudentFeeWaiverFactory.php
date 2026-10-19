<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\FeeCategory;
use App\Models\Student;
use App\Models\StudentFeeWaiver;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentFeeWaiver>
 */
class StudentFeeWaiverFactory extends Factory
{
    protected $model = StudentFeeWaiver::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'fee_category_id' => FeeCategory::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'discount_type' => 'percentage',
            'discount_value' => 50.00,
            'reason' => 'Beasiswa Prestasi / Dhuafa',
        ];
    }
}
