<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use App\Models\InventoryRoom;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<InventoryItem>
 */
class InventoryItemFactory extends Factory
{
    protected $model = InventoryItem::class;

    public function definition(): array
    {
        return [
            'inventory_room_id' => InventoryRoom::factory(),
            'code' => 'AST-'.fake()->unique()->numerify('2026-####'),
            'name' => fake()->randomElement(['Laptop Asus Core i5', 'Proyektor Epson EB-X500', 'Meja Guru Kayu Jati', 'Kursi Siswa Futura', 'Mikroskop Binokuler Olympus']),
            'category' => fake()->randomElement(['mebel', 'elektronik', 'alat_tulis', 'buku_referensi', 'peralatan_lab', 'lainnya']),
            'brand' => fake()->randomElement(['Epson', 'Asus', 'Futura', 'Olympus', 'Informa']),
            'condition' => 'baik',
            'source_of_fund' => fake()->randomElement(['bos', 'yayasan', 'hibah', 'komite']),
            'purchase_date' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'price' => fake()->numberBetween(150000, 12000000),
            'quantity' => 1,
            'unit' => 'unit',
            'qr_code_token' => Str::random(24),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
