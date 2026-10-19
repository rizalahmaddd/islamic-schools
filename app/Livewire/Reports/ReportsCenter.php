<?php

namespace App\Livewire\Reports;

use App\Models\AcademicYear;
use App\Models\AdmissionBatch;
use App\Models\AdmissionCandidate;
use App\Models\DormPlacement;
use App\Models\DormRoom;
use App\Models\FeeCategory;
use App\Models\PaymentTransaction;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentBill;
use App\Models\StudentConductRecord;
use App\Models\StudentLeaveRequest;
use App\Models\TahfidzSession;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app', ['heading' => 'Pusat Laporan'])]
#[Title('Pusat Laporan & Rekapitulasi Terpadu')]
class ReportsCenter extends Component
{
    public string $activeTab = 'executive';

    public string $preset = 'this_month';

    public string $date_from = '';

    public string $date_to = '';

    public ?int $academic_year_id = null;

    public ?int $class_id = null;

    public function mount(): void
    {
        Gate::authorize('view-reports');

        $activeYear = AcademicYear::where('is_active', true)->first();
        $this->academic_year_id = $activeYear?->id ?? AcademicYear::latest()->value('id');

        $this->applyPreset('this_month');
    }

    public function updatedPreset(string $val): void
    {
        $this->applyPreset($val);
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    private function applyPreset(string $preset): void
    {
        $today = Carbon::today();

        match ($preset) {
            'today' => [
                $this->date_from = $today->copy()->toDateString(),
                $this->date_to = $today->copy()->toDateString(),
            ],
            'this_week' => [
                $this->date_from = $today->copy()->startOfWeek()->toDateString(),
                $this->date_to = $today->copy()->endOfWeek()->toDateString(),
            ],
            'this_month' => [
                $this->date_from = $today->copy()->startOfMonth()->toDateString(),
                $this->date_to = $today->copy()->endOfMonth()->toDateString(),
            ],
            'last_month' => [
                $this->date_from = $today->copy()->subMonth()->startOfMonth()->toDateString(),
                $this->date_to = $today->copy()->subMonth()->endOfMonth()->toDateString(),
            ],
            'this_year' => [
                $this->date_from = $today->copy()->startOfYear()->toDateString(),
                $this->date_to = $today->copy()->endOfYear()->toDateString(),
            ],
            default => null,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function getFinancialMetrics(): array
    {
        $start = $this->date_from;
        $end = $this->date_to;

        $incomeQuery = PaymentTransaction::whereBetween('paid_at', [$start.' 00:00:00', $end.' 23:59:59']);

        $totalIncome = (float) $incomeQuery->sum('total_amount');
        $totalTransactions = $incomeQuery->count();

        // Tagihan dan Tunggakan
        $billsQuery = StudentBill::query();
        if ($this->academic_year_id) {
            $billsQuery->where('academic_year_id', $this->academic_year_id);
        }

        $totalBilled = (float) $billsQuery->sum('amount_due');
        $totalPaidBills = (float) $billsQuery->sum('amount_paid');
        $totalRemaining = max(0, $totalBilled - $totalPaidBills);

        $collectionRate = $totalBilled > 0 ? round(($totalPaidBills / $totalBilled) * 100, 1) : 0;

        // Rekap per Kategori Biaya
        $incomeByCategory = FeeCategory::withSum(['bills as collected_amount' => function ($q) use ($start, $end) {
            $q->where('status', 'paid')
                ->whereBetween('updated_at', [$start.' 00:00:00', $end.' 23:59:59']);
        }], 'amount_paid')->get();

        // 5 Siswa dengan Tunggakan Terbesar
        $topArrears = Student::where('status', 'active')
            ->with(['currentEnrollment.schoolClass'])
            ->withSum('bills as total_due', 'amount_due')
            ->withSum('bills as total_paid', 'amount_paid')
            ->get()
            ->map(function ($s) {
                $s->total_arrears = max(0, (float) ($s->total_due ?? 0) - (float) ($s->total_paid ?? 0));

                return $s;
            })
            ->filter(fn ($s) => $s->total_arrears > 0)
            ->sortByDesc('total_arrears')
            ->take(5)
            ->values();

        return [
            'total_income' => $totalIncome,
            'total_transactions' => $totalTransactions,
            'total_billed' => $totalBilled,
            'total_paid' => $totalPaidBills,
            'total_remaining' => $totalRemaining,
            'collection_rate' => $collectionRate,
            'income_by_category' => $incomeByCategory,
            'top_arrears' => $topArrears,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getAttendanceMetrics(): array
    {
        $start = $this->date_from;
        $end = $this->date_to;

        $studentAttQuery = StudentAttendance::whereBetween('date', [$start, $end]);

        if ($this->class_id) {
            $studentAttQuery->where('class_id', $this->class_id);
        }

        $studentTotal = $studentAttQuery->count();
        $studentPresent = (clone $studentAttQuery)->where('status', 'present')->count();
        $studentSick = (clone $studentAttQuery)->where('status', 'sick')->count();
        $studentPermit = (clone $studentAttQuery)->where('status', 'permit')->count();
        $studentAbsent = (clone $studentAttQuery)->where('status', 'absent')->count();

        $studentAttendanceRate = $studentTotal > 0 ? round(($studentPresent / $studentTotal) * 100, 1) : 0;

        // Presensi Guru
        $teacherAttQuery = TeacherAttendance::whereBetween('date', [$start, $end]);

        $teacherTotal = $teacherAttQuery->count();
        $teacherPresent = (clone $teacherAttQuery)->where('status', 'present')->count();
        $teacherLate = (clone $teacherAttQuery)->where('status', 'late')->count();
        $teacherSick = (clone $teacherAttQuery)->where('status', 'sick')->count();
        $teacherPermit = (clone $teacherAttQuery)->where('status', 'permit')->count();
        $teacherAbsent = (clone $teacherAttQuery)->where('status', 'absent')->count();

        $teacherAttendanceRate = $teacherTotal > 0 ? round((($teacherPresent + $teacherLate) / $teacherTotal) * 100, 1) : 0;

        // Rekap per Kelas
        $classes = SchoolClass::withCount([
            'attendances as total_records' => fn ($q) => $q->whereBetween('date', [$start, $end]),
            'attendances as present_records' => fn ($q) => $q->whereBetween('date', [$start, $end])->where('status', 'present'),
        ])->get();

        return [
            'student_total' => $studentTotal,
            'student_present' => $studentPresent,
            'student_sick' => $studentSick,
            'student_permit' => $studentPermit,
            'student_absent' => $studentAbsent,
            'student_rate' => $studentAttendanceRate,
            'teacher_total' => $teacherTotal,
            'teacher_present' => $teacherPresent,
            'teacher_late' => $teacherLate,
            'teacher_sick' => $teacherSick,
            'teacher_permit' => $teacherPermit,
            'teacher_absent' => $teacherAbsent,
            'teacher_rate' => $teacherAttendanceRate,
            'classes_summary' => $classes,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getTahfidzMetrics(): array
    {
        $start = $this->date_from;
        $end = $this->date_to;

        $tahfidzQuery = TahfidzSession::whereBetween('recorded_at', [$start.' 00:00:00', $end.' 23:59:59']);

        $totalSessions = $tahfidzQuery->count();
        $ziyadahCount = (clone $tahfidzQuery)->where('session_type', 'ziyadah')->count();
        $murajaahCount = (clone $tahfidzQuery)->where('session_type', 'murajaah')->count();

        // Santri dengan setoran terbanyak di periode ini
        $topStudents = Student::where('status', 'active')
            ->with(['currentEnrollment.schoolClass'])
            ->whereHas('tahfidzSessions', fn ($q) => $q->whereBetween('recorded_at', [$start.' 00:00:00', $end.' 23:59:59']))
            ->withCount(['tahfidzSessions' => fn ($q) => $q->whereBetween('recorded_at', [$start.' 00:00:00', $end.' 23:59:59'])])
            ->orderByDesc('tahfidz_sessions_count')
            ->limit(5)
            ->get();

        // Rekap Poin Adab
        $conductQuery = StudentConductRecord::whereBetween('date', [$start, $end]);

        $totalViolations = (clone $conductQuery)->where('type', 'violation')->count();
        $totalViolationPoints = (int) (clone $conductQuery)->where('type', 'violation')->sum('points');
        $totalAchievements = (clone $conductQuery)->where('type', 'merit')->count();
        $totalAchievementPoints = (int) (clone $conductQuery)->where('type', 'merit')->sum('points');

        return [
            'total_sessions' => $totalSessions,
            'ziyadah_count' => $ziyadahCount,
            'murajaah_count' => $murajaahCount,
            'top_students' => $topStudents,
            'violations_count' => $totalViolations,
            'violation_points' => $totalViolationPoints,
            'achievements_count' => $totalAchievements,
            'achievement_points' => $totalAchievementPoints,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getBoardingMetrics(): array
    {
        $start = $this->date_from;
        $end = $this->date_to;

        $totalRooms = DormRoom::count();
        $totalCapacity = (int) DormRoom::sum('capacity');
        $totalOccupied = DormPlacement::count();
        $occupancyRate = $totalCapacity > 0 ? round(($totalOccupied / $totalCapacity) * 100, 1) : 0;

        $leaveQuery = StudentLeaveRequest::whereBetween('departure_plan', [$start.' 00:00:00', $end.' 23:59:59']);

        $totalLeaves = $leaveQuery->count();
        $pendingLeaves = (clone $leaveQuery)->where('status', 'pending')->count();
        $approvedLeaves = (clone $leaveQuery)->where('status', 'approved')->count();
        $activeLeaves = (clone $leaveQuery)->where('status', 'active')->count();
        $completedLeaves = (clone $leaveQuery)->where('status', 'completed')->count();
        $overdueLeaves = (clone $leaveQuery)->where('status', 'overdue')->count();

        $rooms = DormRoom::with('building')->withCount('placements as occupied')->orderBy('dorm_building_id')->get();

        return [
            'total_rooms' => $totalRooms,
            'total_capacity' => $totalCapacity,
            'total_occupied' => $totalOccupied,
            'occupancy_rate' => $occupancyRate,
            'total_leaves' => $totalLeaves,
            'pending_leaves' => $pendingLeaves,
            'approved_leaves' => $approvedLeaves,
            'active_leaves' => $activeLeaves,
            'completed_leaves' => $completedLeaves,
            'overdue_leaves' => $overdueLeaves,
            'rooms_list' => $rooms,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getAdmissionsMetrics(): array
    {
        $batches = AdmissionBatch::withCount(['candidates', 'candidates as passed_count' => function ($q) {
            $q->where('status', 'passed');
        }, 'candidates as converted_count' => function ($q) {
            $q->where('status', 'converted');
        }])->get();

        $totalCandidates = AdmissionCandidate::count();
        $registered = AdmissionCandidate::where('status', 'registered')->count();
        $verified = AdmissionCandidate::where('status', 'verified')->count();
        $tested = AdmissionCandidate::where('status', 'tested')->count();
        $passed = AdmissionCandidate::where('status', 'passed')->count();
        $rejected = AdmissionCandidate::where('status', 'rejected')->count();
        $converted = AdmissionCandidate::where('status', 'converted')->count();

        $maleCandidates = AdmissionCandidate::where('gender', 'male')->count();
        $femaleCandidates = AdmissionCandidate::where('gender', 'female')->count();

        return [
            'batches' => $batches,
            'total_candidates' => $totalCandidates,
            'registered' => $registered,
            'verified' => $verified,
            'tested' => $tested,
            'passed' => $passed,
            'rejected' => $rejected,
            'converted' => $converted,
            'male_candidates' => $maleCandidates,
            'female_candidates' => $femaleCandidates,
        ];
    }

    public function exportCsv(): StreamedResponse
    {
        $tab = $this->activeTab;
        $filename = 'laporan-'.$tab.'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($tab) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fputcsv($handle, ['LAPORAN TERPADU SIM SEKOLAH & PESANTREN']);
            fputcsv($handle, ['Kategori Laporan', strtoupper($tab)]);
            fputcsv($handle, ['Periode', $this->date_from.' s/d '.$this->date_to]);
            fputcsv($handle, ['Tanggal Unduh', now()->translatedFormat('d F Y H:i:s')]);
            fputcsv($handle, []);

            if ($tab === 'finance' || $tab === 'executive') {
                $fin = $this->getFinancialMetrics();
                fputcsv($handle, ['--- RINGKASAN KEUANGAN ---']);
                fputcsv($handle, ['Indikator', 'Nilai']);
                fputcsv($handle, ['Total Penerimaan Kas', $fin['total_income']]);
                fputcsv($handle, ['Total Transaksi Pembayaran', $fin['total_transactions']]);
                fputcsv($handle, ['Total Nilai Tagihan Terbit', $fin['total_billed']]);
                fputcsv($handle, ['Total Tagihan Terbayar', $fin['total_paid']]);
                fputcsv($handle, ['Sisa Piutang / Tunggakan', $fin['total_remaining']]);
                fputcsv($handle, ['Tingkat Pelunasan (%)', $fin['collection_rate'].'%']);
                fputcsv($handle, []);
            }

            if ($tab === 'attendance' || $tab === 'executive') {
                $att = $this->getAttendanceMetrics();
                fputcsv($handle, ['--- RINGKASAN PRESENSI ---']);
                fputcsv($handle, ['Indikator', 'Nilai']);
                fputcsv($handle, ['Total Catatan Presensi Siswa', $att['student_total']]);
                fputcsv($handle, ['Tingkat Kehadiran Siswa (%)', $att['student_rate'].'%']);
                fputcsv($handle, ['Siswa Hadir', $att['student_present']]);
                fputcsv($handle, ['Siswa Sakit', $att['student_sick']]);
                fputcsv($handle, ['Siswa Izin', $att['student_permit']]);
                fputcsv($handle, ['Siswa Alpha', $att['student_absent']]);
                fputcsv($handle, ['Tingkat Kehadiran Guru (%)', $att['teacher_rate'].'%']);
                fputcsv($handle, []);
            }

            if ($tab === 'tahfidz' || $tab === 'executive') {
                $tah = $this->getTahfidzMetrics();
                fputcsv($handle, ['--- RINGKASAN TAHFIDZ & ADAB ---']);
                fputcsv($handle, ['Indikator', 'Nilai']);
                fputcsv($handle, ['Total Sesi Setoran Quran', $tah['total_sessions']]);
                fputcsv($handle, ['Setoran Ziyadah (Hafalan Baru)', $tah['ziyadah_count']]);
                fputcsv($handle, ['Setoran Murajaah (Mengulang)', $tah['murajaah_count']]);
                fputcsv($handle, ['Total Catatan Pelanggaran Adab', $tah['violations_count']]);
                fputcsv($handle, ['Total Poin Pelanggaran', $tah['violation_points']]);
                fputcsv($handle, ['Total Catatan Prestasi Santri', $tah['achievements_count']]);
                fputcsv($handle, ['Total Poin Prestasi', $tah['achievement_points']]);
                fputcsv($handle, []);
            }

            if ($tab === 'boarding' || $tab === 'executive') {
                $boa = $this->getBoardingMetrics();
                fputcsv($handle, ['--- RINGKASAN ASRAMA ---']);
                fputcsv($handle, ['Indikator', 'Nilai']);
                fputcsv($handle, ['Kapasitas Total Ranjang', $boa['total_capacity']]);
                fputcsv($handle, ['Ranjang Terisi', $boa['total_occupied']]);
                fputcsv($handle, ['Tingkat Okupansi (%)', $boa['occupancy_rate'].'%']);
                fputcsv($handle, ['Total Pengajuan Izin Santri', $boa['total_leaves']]);
                fputcsv($handle, ['Izin Aktif / Sedang di Luar', $boa['active_leaves']]);
                fputcsv($handle, ['Terlambat Kembali ke Asrama', $boa['overdue_leaves']]);
                fputcsv($handle, []);
            }

            if ($tab === 'admissions' || $tab === 'executive') {
                $adm = $this->getAdmissionsMetrics();
                fputcsv($handle, ['--- RINGKASAN PPDB ONLINE ---']);
                fputcsv($handle, ['Indikator', 'Nilai']);
                fputcsv($handle, ['Total Pendaftar Calon Santri', $adm['total_candidates']]);
                fputcsv($handle, ['Calon Santri Lulus', $adm['passed']]);
                fputcsv($handle, ['Sudah Konversi ke Siswa Aktif', $adm['converted']]);
                fputcsv($handle, ['Pendaftar Ikhwan (L)', $adm['male_candidates']]);
                fputcsv($handle, ['Pendaftar Akhwat (P)', $adm['female_candidates']]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function render(): View
    {
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $classes = SchoolClass::orderBy('level')->orderBy('name')->get();

        $totalActiveStudents = Student::where('status', 'active')->count();
        $totalActiveTeachers = Teacher::where('is_active', true)->count();

        $financial = $this->getFinancialMetrics();
        $attendance = $this->getAttendanceMetrics();
        $tahfidz = $this->getTahfidzMetrics();
        $boarding = $this->getBoardingMetrics();
        $admissions = $this->getAdmissionsMetrics();

        return view('livewire.reports.reports-center', [
            'academicYears' => $academicYears,
            'classes' => $classes,
            'totalActiveStudents' => $totalActiveStudents,
            'totalActiveTeachers' => $totalActiveTeachers,
            'financial' => $financial,
            'attendance' => $attendance,
            'tahfidz' => $tahfidz,
            'boarding' => $boarding,
            'admissions' => $admissions,
        ]);
    }
}
