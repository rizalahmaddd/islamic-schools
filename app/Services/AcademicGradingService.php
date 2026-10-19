<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\ReportCardSummary;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentGrade;
use App\Models\SubjectAssignment;
use App\Models\TahfidzSession;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class AcademicGradingService
{
    public function __construct(
        protected TahfidzService $tahfidzService,
    ) {}

    /**
     * Menghitung nilai akhir dengan bobot:
     * 50% Rata-rata Formatif + 25% Asesmen Tengah Semester (UTS) + 25% Asesmen Akhir Semester (UAS).
     */
    public function calculateFinalScore(float $formative, float $midTerm, float $finalTerm): float
    {
        $calculated = ($formative * 0.50) + ($midTerm * 0.25) + ($finalTerm * 0.25);

        return round($calculated, 2);
    }

    /**
     * Menentukan predikat kelulusan capaian tujuan pembelajaran (TP / KKM).
     */
    public function determinePredicate(float $finalScore, float $passingGrade = 75.00): string
    {
        return match (true) {
            $finalScore >= 90.00 => 'A',
            $finalScore >= 80.00 => 'B',
            $finalScore >= $passingGrade => 'C',
            default => 'D',
        };
    }

    /**
     * Menghasilkan teks deskripsi capaian kompetensi otomatis sesuai Kurikulum Merdeka.
     *
     * @return array{achievement: string, improvement: string}
     */
    public function generateCompetenceDescriptions(string $subjectName, string $predicate): array
    {
        return match ($predicate) {
            'A' => [
                'achievement' => "Menunjukkan penguasaan yang sangat istimewa dalam seluruh capaian pembelajaran {$subjectName}.",
                'improvement' => 'Pertahankan prestasi dan terus kembangkan kemampuan literasi pada materi lanjutan.',
            ],
            'B' => [
                'achievement' => "Menunjukkan penguasaan yang baik dan tuntas dalam materi pokok {$subjectName}.",
                'improvement' => 'Perlu memperbanyak latihan soal terapan untuk memperkuat pemahaman mendalam.',
            ],
            'C' => [
                'achievement' => "Menunjukkan penguasaan cukup dalam sebagian besar kompetensi dasar {$subjectName}.",
                'improvement' => 'Perlu bimbingan dan latihan lebih giat terutama pada materi asesmen sumatif.',
            ],
            default => [
                'achievement' => "Masih membutuhkan pendampingan untuk mencapai ketuntasan kriteria tujuan pembelajaran {$subjectName}.",
                'improvement' => 'Perlu program remedial berkesinambungan dan pembinaan khusus bersama guru pengampu.',
            ],
        };
    }

    /**
     * Simpan atau perbarui nilai siswa untuk penugasan mata pelajaran tertentu.
     *
     * @param  array{formative_average?: float|numeric-string, mid_term_score?: float|numeric-string, final_term_score?: float|numeric-string, achievement_desc?: ?string, improvement_desc?: ?string}  $data
     */
    public function saveStudentGrade(SubjectAssignment $assignment, Student $student, array $data): StudentGrade
    {
        $formative = (float) ($data['formative_average'] ?? 0);
        $midTerm = (float) ($data['mid_term_score'] ?? 0);
        $finalTerm = (float) ($data['final_term_score'] ?? 0);

        $finalScore = $this->calculateFinalScore($formative, $midTerm, $finalTerm);
        $predicate = $this->determinePredicate($finalScore, (float) $assignment->passing_grade);

        $descriptions = $this->generateCompetenceDescriptions($assignment->subject->name, $predicate);
        $achievement = ! empty($data['achievement_desc']) ? (string) $data['achievement_desc'] : $descriptions['achievement'];
        $improvement = ! empty($data['improvement_desc']) ? (string) $data['improvement_desc'] : $descriptions['improvement'];

        return StudentGrade::updateOrCreate(
            [
                'subject_assignment_id' => $assignment->id,
                'student_id' => $student->id,
            ],
            [
                'formative_average' => $formative,
                'mid_term_score' => $midTerm,
                'final_term_score' => $finalTerm,
                'final_score' => $finalScore,
                'predicate' => $predicate,
                'achievement_desc' => $achievement,
                'improvement_desc' => $improvement,
            ]
        );
    }

    /**
     * Sinkronisasi data ketidakhadiran santri riil dari StudentAttendance ke Rapor.
     */
    public function syncReportCardAttendance(Student $student, SchoolClass $schoolClass, AcademicYear $academicYear): ReportCardSummary
    {
        $attendancesQuery = StudentAttendance::query()
            ->where('student_id', $student->id)
            ->where('class_id', $schoolClass->id);

        if ($academicYear->start_date && $academicYear->end_date) {
            $startDate = Carbon::parse($academicYear->start_date)->toDateString();
            $endDate = Carbon::parse($academicYear->end_date)->toDateString();

            $attendancesQuery->whereBetween('date', [$startDate, $endDate]);
        }

        $attendances = $attendancesQuery->get();

        $sick = $attendances->where('status', 'sick')->count();
        $permit = $attendances->where('status', 'permit')->count();
        $absent = $attendances->where('status', 'absent')->count();

        return ReportCardSummary::updateOrCreate(
            [
                'student_id' => $student->id,
                'class_id' => $schoolClass->id,
                'academic_year_id' => $academicYear->id,
            ],
            [
                'sick_count' => $sick,
                'permit_count' => $permit,
                'absent_count' => $absent,
            ]
        );
    }

    /**
     * Kumpulkan seluruh data agregat rapor siswa untuk tampilan & cetak resmi.
     *
     * @return array{
     *     student: Student,
     *     class: ?SchoolClass,
     *     academicYear: ?AcademicYear,
     *     grades: Collection<int, StudentGrade>,
     *     averageScore: float,
     *     summary: ?ReportCardSummary,
     *     tahfidzProgress: array{completed_juz_count: int, latest_session: ?TahfidzSession, total_sessions: int},
     *     violationPoints: int,
     *     meritPoints: int
     * }
     */
    public function getStudentReportCardData(Student $student, ?int $academicYearId = null): array
    {
        $student->loadMissing([
            'guardian',
            'currentEnrollment.schoolClass.homeroomTeacher',
            'currentEnrollment.academicYear',
        ]);

        $enrollment = $student->currentEnrollment;
        $class = $enrollment?->schoolClass;
        $academicYear = $academicYearId
            ? AcademicYear::find($academicYearId)
            : ($enrollment?->academicYear ?? AcademicYear::where('is_active', true)->first());

        $grades = collect();
        $averageScore = 0.0;

        if ($class && $academicYear) {
            $assignmentIds = SubjectAssignment::query()
                ->where('class_id', $class->id)
                ->where('academic_year_id', $academicYear->id)
                ->pluck('id');

            $grades = StudentGrade::query()
                ->with(['subjectAssignment.subject', 'subjectAssignment.teacher'])
                ->where('student_id', $student->id)
                ->whereIn('subject_assignment_id', $assignmentIds)
                ->get();

            if ($grades->isNotEmpty()) {
                $averageScore = round((float) $grades->avg('final_score'), 2);
            }
        }

        $summary = null;
        if ($class && $academicYear) {
            $summary = ReportCardSummary::where('student_id', $student->id)
                ->where('class_id', $class->id)
                ->where('academic_year_id', $academicYear->id)
                ->first();

            if (! $summary) {
                $summary = $this->syncReportCardAttendance($student, $class, $academicYear);
            }
        }

        $tahfidz = $this->tahfidzService->calculateStudentProgress($student);

        return [
            'student' => $student,
            'class' => $class,
            'academicYear' => $academicYear,
            'grades' => $grades,
            'averageScore' => $averageScore,
            'summary' => $summary,
            'tahfidzProgress' => [
                'completed_juz_count' => $tahfidz['completed_juz_count'],
                'latest_session' => $tahfidz['latest_session'],
                'total_sessions' => $tahfidz['total_sessions_count'],
            ],
            'violationPoints' => $student->totalViolationPoints(),
            'meritPoints' => $student->totalMeritPoints(),
        ];
    }
}
