<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->lexify('MAPEL-???'),
            'name' => fake()->randomElement([
                "Tahfidz & Tahsin Al-Qur'an",
                'Bahasa Arab',
                'Fiqih Ibadah',
                'Aqidah Akhlak',
                'Tarikh Islam (SKI)',
                'Matematika',
                'Bahasa Indonesia',
                'Bahasa Inggris',
                'IPA Terpadu',
            ]),
            'category' => 'islamic',
            'is_active' => true,
        ];
    }
}
