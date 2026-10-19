<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\AdmissionBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdmissionBatch>
 */
class AdmissionBatchFactory extends Factory
{
    protected $model = AdmissionBatch::class;

    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'name' => 'Gelombang '.$this->faker->numberBetween(1, 3).' - Jalur Reguler',
            'wave_number' => $this->faker->numberBetween(1, 3),
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->addMonths(2)->endOfMonth(),
            'quota' => 120,
            'registration_fee' => 250000,
            'description' => 'Pendaftaran penerimaan santri baru tahun ajaran.',
            'is_active' => true,
        ];
    }
}
