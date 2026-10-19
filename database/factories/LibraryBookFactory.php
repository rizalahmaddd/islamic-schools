<?php

namespace Database\Factories;

use App\Models\LibraryBook;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LibraryBook>
 */
class LibraryBookFactory extends Factory
{
    protected $model = LibraryBook::class;

    public function definition(): array
    {
        $total = fake()->numberBetween(3, 10);

        return [
            'code' => 'BK-'.fake()->unique()->numerify('2026-####'),
            'isbn' => fake()->optional()->numerify('978-602-####-##-#'),
            'title' => fake()->randomElement(['Matan Al-Ghayah wat Taqrib', 'Riyadhus Shalihin', 'Bidayatul Hidayah', 'Nahwu Al-Wadhih', 'Tafsir Jalalain', 'Matematika Kelas 7 SMP/MTs']),
            'author' => fake()->randomElement(['Al-Qadhi Abu Syuja', 'Imam An-Nawawi', 'Imam Al-Ghazali', 'Ali Al-Jarim', 'Imam Jalaluddin']),
            'publisher' => fake()->randomElement(['Darul Kutub Al-Ilmiyyah', 'Darul Haq', 'Pustaka Arafah', 'Erlangga']),
            'publish_year' => fake()->numberBetween(2018, 2024),
            'category' => fake()->randomElement(['kitab_kuning', 'pelajaran', 'umum', 'tahfidz', 'kamus']),
            'call_number' => fake()->numerify('297.##').' '.fake()->lexify('???').' '.fake()->lexify('?'),
            'rack_location' => fake()->randomElement(['Rak A-01', 'Rak B-02', 'Rak C-03', 'Lemari Kitab 1']),
            'total_copies' => $total,
            'available_copies' => $total,
            'barcode_token' => 'LIB-'.Str::upper(Str::random(10)),
        ];
    }
}
