<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeeStructure>
 */
class FeeStructureFactory extends Factory
{
    protected $model = FeeStructure::class;

    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'fee_category_id' => FeeCategory::factory(),
            'grade_level' => '7',
            'default_amount' => 350000.00,
        ];
    }
}
