<?php

namespace Database\Factories;

use App\Models\PaymentTransaction;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentTransaction>
 */
class PaymentTransactionFactory extends Factory
{
    protected $model = PaymentTransaction::class;

    public function definition(): array
    {
        return [
            'receipt_number' => 'KW-2026-'.fake()->unique()->numerify('#####'),
            'student_id' => Student::factory(),
            'total_amount' => 350000.00,
            'payment_method' => 'cash',
            'received_by' => User::factory(),
            'paid_at' => now(),
            'notes' => 'Pembayaran SPP',
        ];
    }
}
