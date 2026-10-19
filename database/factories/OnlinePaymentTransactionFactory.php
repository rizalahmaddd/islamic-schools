<?php

namespace Database\Factories;

use App\Models\OnlinePaymentTransaction;
use App\Models\Student;
use App\Models\StudentBill;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OnlinePaymentTransaction>
 */
class OnlinePaymentTransactionFactory extends Factory
{
    protected $model = OnlinePaymentTransaction::class;

    public function definition(): array
    {
        $amount = fake()->randomElement([250000, 500000, 750000, 1000000]);
        $adminFee = 4500;

        return [
            'order_id' => 'ORD-'.fake()->unique()->numerify('202610-####'),
            'student_bill_id' => StudentBill::factory(),
            'student_id' => Student::factory(),
            'payer_user_id' => null,
            'payment_channel' => 'QRIS',
            'payment_channel_name' => 'QRIS (Semua Pembayaran)',
            'amount' => $amount,
            'admin_fee' => $adminFee,
            'gross_amount' => $amount + $adminFee,
            'provider' => 'tripay',
            'reference' => 'DEV-T'.fake()->unique()->numerify('#########'),
            'checkout_url' => 'https://tripay.co.id/checkout/'.fake()->uuid(),
            'qr_url' => 'https://tripay.co.id/qr/'.fake()->uuid(),
            'qr_string' => '00020101021226590014ID.LINKAJA.WWW011893600011011122334402151234567890123455204581253033605802ID5914Sekolah Islam6007Jakarta6304'.fake()->lexify('????'),
            'va_number' => null,
            'status' => 'pending',
            'paid_at' => null,
            'expires_at' => now()->addDay(),
            'raw_payload' => [],
        ];
    }

    public function settled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'settlement',
            'paid_at' => now(),
        ]);
    }
}
