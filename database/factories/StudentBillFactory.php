<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\FeeCategory;
use App\Models\Student;
use App\Models\StudentBill;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentBill>
 */
class StudentBillFactory extends Factory
{
    protected $model = StudentBill::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'fee_category_id' => FeeCategory::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'month' => 7,
            'year' => 2026,
            'amount_due' => 350000.00,
            'amount_paid' => 0.00,
            'status' => 'unpaid',
            'due_date' => '2026-07-10',
            'notes' => 'Tagihan SPP Juli 2026',
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount_paid' => $attributes['amount_due'] ?? 350000.00,
            'status' => 'paid',
        ]);
    }
}
