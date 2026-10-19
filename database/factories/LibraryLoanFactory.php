<?php

namespace Database\Factories;

use App\Models\LibraryBook;
use App\Models\LibraryLoan;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LibraryLoan>
 */
class LibraryLoanFactory extends Factory
{
    protected $model = LibraryLoan::class;

    public function definition(): array
    {
        $borrowDate = fake()->dateTimeBetween('-1 month', 'now');
        $dueDate = (clone $borrowDate)->modify('+7 days');

        return [
            'loan_code' => 'PIN-'.fake()->unique()->numerify('2026-####'),
            'library_book_id' => LibraryBook::factory(),
            'borrower_type' => 'student',
            'student_id' => Student::factory(),
            'teacher_id' => null,
            'borrow_date' => $borrowDate->format('Y-m-d'),
            'due_date' => $dueDate->format('Y-m-d'),
            'return_date' => null,
            'fine_amount' => 0,
            'status' => 'active',
            'notes' => fake()->optional()->sentence(),
            'processed_by' => User::factory(),
        ];
    }
}
