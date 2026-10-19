<?php

namespace Database\Factories;

use App\Models\RfidCard;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RfidCard>
 */
class RfidCardFactory extends Factory
{
    protected $model = RfidCard::class;

    public function definition(): array
    {
        return [
            'card_uid' => fake()->unique()->numerify('##########'),
            'student_id' => Student::factory(),
            'label' => 'Kartu Utama',
            'is_active' => true,
            'blocked_at' => null,
            'blocked_reason' => null,
        ];
    }
}
