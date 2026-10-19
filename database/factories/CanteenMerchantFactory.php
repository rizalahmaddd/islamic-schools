<?php

namespace Database\Factories;

use App\Models\CanteenMerchant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CanteenMerchant>
 */
class CanteenMerchantFactory extends Factory
{
    protected $model = CanteenMerchant::class;

    public function definition(): array
    {
        return [
            'code' => 'MRC-'.fake()->unique()->numerify('###'),
            'name' => fake()->randomElement(['Kantin Utama Barakah', 'Koperasi Pondok Pesantren', 'Kantin Al-Falah', 'Kedai Minuman Segar']),
            'pic_name' => fake()->name(),
            'is_active' => true,
        ];
    }
}
