<?php

namespace Database\Factories;

use App\Models\FeeCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeeCategory>
 */
class FeeCategoryFactory extends Factory
{
    protected $model = FeeCategory::class;

    public function definition(): array
    {
        return [
            'name' => 'SPP Bulanan',
            'type' => 'monthly',
            'is_active' => true,
        ];
    }
}
