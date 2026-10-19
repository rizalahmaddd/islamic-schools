<?php

namespace Database\Factories;

use App\Models\CanteenMerchant;
use App\Models\StudentWallet;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WalletTransaction>
 */
class WalletTransactionFactory extends Factory
{
    protected $model = WalletTransaction::class;

    public function definition(): array
    {
        $amount = 15000;
        $before = 50000;
        $after = 35000;

        return [
            'reference_number' => 'WLT-'.fake()->unique()->numerify('2026-####'),
            'student_wallet_id' => StudentWallet::factory(),
            'rfid_card_id' => null,
            'transaction_type' => 'purchase',
            'amount' => $amount,
            'balance_before' => $before,
            'balance_after' => $after,
            'canteen_merchant_id' => CanteenMerchant::factory(),
            'description' => 'Pembelian di Kantin Utama',
            'cashier_user_id' => User::factory(),
        ];
    }
}
