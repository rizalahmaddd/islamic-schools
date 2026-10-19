<?php

namespace Database\Factories;

use App\Models\QuranSurah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuranSurah>
 */
class QuranSurahFactory extends Factory
{
    protected $model = QuranSurah::class;

    public function definition(): array
    {
        return [
            'number' => 1,
            'name_arabic' => 'الفاتحة',
            'name_latin' => 'Al-Fatihah',
            'total_verses' => 7,
            'juz_start' => 1,
            'revelation_type' => 'meccan',
        ];
    }
}
