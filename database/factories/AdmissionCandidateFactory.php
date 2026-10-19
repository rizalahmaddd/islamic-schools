<?php

namespace Database\Factories;

use App\Models\AdmissionBatch;
use App\Models\AdmissionCandidate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdmissionCandidate>
 */
class AdmissionCandidateFactory extends Factory
{
    protected $model = AdmissionCandidate::class;

    public function definition(): array
    {
        return [
            'admission_batch_id' => AdmissionBatch::factory(),
            'registration_number' => 'REG-'.now()->format('Ym').'-'.str_pad((string) $this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'student_name' => $this->faker->name(),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'nisn' => $this->faker->numerify('00########'),
            'nik' => $this->faker->numerify('3201############'),
            'pob' => $this->faker->city(),
            'dob' => now()->subYears(12)->subDays($this->faker->numberBetween(1, 300)),
            'previous_school' => 'SD / MI '.$this->faker->city(),
            'guardian_name' => $this->faker->name(),
            'guardian_relationship' => $this->faker->randomElement(['Ayah', 'Ibu', 'Wali']),
            'guardian_phone' => $this->faker->numerify('0812########'),
            'guardian_email' => $this->faker->safeEmail(),
            'address' => $this->faker->address(),
            'status' => 'registered',
        ];
    }

    public function passed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'passed',
            'test_score_academic' => 85.0,
            'test_score_quran' => 90.0,
            'test_score_interview' => 88.0,
        ]);
    }

    public function enrolled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'enrolled',
            'test_score_academic' => 85.0,
            'test_score_quran' => 90.0,
            'test_score_interview' => 88.0,
            'converted_at' => now(),
        ]);
    }
}
