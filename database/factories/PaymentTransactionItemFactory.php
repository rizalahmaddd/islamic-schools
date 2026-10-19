<?php

namespace Database\Factories;

use App\Models\PaymentTransaction;
use App\Models\PaymentTransactionItem;
use App\Models\StudentBill;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentTransactionItem>
 */
class PaymentTransactionItemFactory extends Factory
{
    protected $model = PaymentTransactionItem::class;

    public function definition(): array
    {
        return [
            'payment_transaction_id' => PaymentTransaction::factory(),
            'student_bill_id' => StudentBill::factory(),
            'amount_allocated' => 350000.00,
        ];
    }
}
