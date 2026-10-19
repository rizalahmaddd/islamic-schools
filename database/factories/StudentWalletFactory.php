<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentWallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentWallet>
 */
class StudentWalletFactory extends Factory
{
    protected $model = StudentWallet::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'balance' => 50000,
            'daily_limit' => 30000,
            'pin_hash' => null,
            'is_active' => true,
        ];
    }
}
