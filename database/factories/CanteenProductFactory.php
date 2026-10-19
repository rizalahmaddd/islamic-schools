<?php

namespace Database\Factories;

use App\Models\CanteenMerchant;
use App\Models\CanteenProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CanteenProduct>
 */
class CanteenProductFactory extends Factory
{
    protected $model = CanteenProduct::class;

    public function definition(): array
    {
        return [
            'canteen_merchant_id' => CanteenMerchant::factory(),
            'code' => 'PRD-'.fake()->unique()->numerify('####'),
            'name' => fake()->randomElement(['Nasi Goreng Ayam', 'Roti Bakar Coklat', 'Susu UHT Coklat', 'Air Mineral 600ml', 'Mie Ayam Bakso', 'Buku Tulis Sidu 38']),
            'category' => fake()->randomElement(['makanan', 'minuman', 'snack', 'alat_tulis']),
            'price' => fake()->randomElement([3000, 5000, 7000, 10000, 12000, 15000]),
            'stock' => 100,
            'is_available' => true,
        ];
    }
}
