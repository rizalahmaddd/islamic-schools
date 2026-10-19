<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeachingJournal>
 */
class TeachingJournalFactory extends Factory
{
    protected $model = TeachingJournal::class;

    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'teacher_id' => Teacher::factory(),
            'class_id' => SchoolClass::factory(),
            'subject_id' => Subject::factory(),
            'date' => now()->toDateString(),
            'period_start' => 1,
            'period_end' => 2,
            'topic_taught' => 'Tahsin & Hafalan Surat Al-Mulk ayat 1-15',
            'learning_activities' => 'Murojaah klasikal dilanjutkan setoran santri bergantian',
            'notes' => 'Sebagian santri perlu penguatan makhraj huruf',
            'evidence_photo_path' => null,
        ];
    }
}
