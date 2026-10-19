<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithRealtimeRefresh;
use App\Models\AcademicYear;
use App\Models\AdmissionCandidate;
use App\Models\CanteenOrder;
use App\Models\DormPlacement;
use App\Models\DormRoom;
use App\Models\LibraryBook;
use App\Models\LibraryLoan;
use App\Models\PaymentTransaction;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentDailyWorship;
use App\Models\StudentLeaveRequest;
use App\Models\TahfidzSession;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

#[Layout('layouts.app', ['heading' => 'Dashboard'])]
#[Title('Dashboard')]
class Dashboard extends Component
{
    use WithRealtimeRefresh;

    public function mount()
    {
        $user = Auth::user();
        if ($user && ($user->hasRole('wali_murid') || ($user->can('view-guardian-portal') && ! $user->can('view-master-data')))) {
            return redirect()->route('portal.guardian');
        }
    }

    /**
     * 4 Indikator Utama Eksekutif (Tier 1)
     *
     * @return array<int, array{title: string, value: string, icon: string, tone: string, subtitle: ?string, href: ?string}>
     */
    public function primaryStats(): array
    {
        $user = Auth::user();
        $stats = [];

        // 1. Santri & Siswa Aktif
        if ($user->can('view-master-data')) {
            $activeStudents = Student::query()->where('status', 'active');
            $totalActive = (clone $activeStudents)->count();
            $maleCount = (clone $activeStudents)->where('gender', 'L')->count();
            $femaleCount = (clone $activeStudents)->where('gender', 'P')->count();

            $stats[] = [
                'title' => 'Santri & Siswa',
                'value' => number_format($totalActive, 0, ',', '.'),
                'icon' => 'graduation-cap',
                'tone' => 'emerald',
                'subtitle' => "{$maleCount} Ikhwan · {$femaleCount} Akhwat",
                'href' => route('master-data.students'),
            ];
        }

        // 2. Kehadiran Hari Ini
        if ($user->can('view-attendance')) {
            $todayAttendances = StudentAttendance::query()->whereDate('date', today())->get();
            $presentCount = $todayAttendances->where('status', 'present')->count();
            $totalRecorded = $todayAttendances->count();
            $rate = $totalRecorded > 0 ? round(($presentCount / $totalRecorded) * 100, 1) : 0;
            $sickPermitCount = $todayAttendances->whereIn('status', ['sick', 'permit'])->count();
            $absentCount = $todayAttendances->where('status', 'absent')->count();

            $stats[] = [
                'title' => 'Presensi Hari Ini',
                'value' => $totalRecorded > 0 ? "{$rate}% Hadir" : 'Belum Ada',
                'icon' => 'calendar-check',
                'tone' => $rate >= 90 ? 'emerald' : ($totalRecorded > 0 ? 'amber' : 'sky'),
                'subtitle' => $totalRecorded > 0 ? "{$presentCount} Hadir · {$sickPermitCount} Sakit/Izin · {$absentCount} Alpa" : 'Menunggu input absensi rombel',
                'href' => route('attendance.students'),
            ];
        }

        // 3. Setoran Tahfidz Hari Ini
        if ($user->can('view-tahfidz')) {
            $todayTahfidz = TahfidzSession::query()->whereDate('recorded_at', today())->count();
            $stats[] = [
                'title' => 'Setoran Tahfidz Hari Ini',
                'value' => number_format($todayTahfidz, 0, ',', '.').' Sesi',
                'icon' => 'book-open',
                'tone' => 'emerald',
                'subtitle' => 'Ziyadah & Murajaah 30 Juz',
                'href' => route('islamic.tahfidz'),
            ];
        }

        // 4. Penerimaan Kas SPP Bulan Ini
        if ($user->can('view-finance')) {
            $monthRevenue = PaymentTransaction::query()
                ->whereYear('paid_at', now()->year)
                ->whereMonth('paid_at', now()->month)
                ->sum('total_amount');
            $txCount = PaymentTransaction::query()
                ->whereYear('paid_at', now()->year)
                ->whereMonth('paid_at', now()->month)
                ->count();

            $stats[] = [
                'title' => 'Kas SPP Bulan Ini',
                'value' => 'Rp '.number_format((float) $monthRevenue, 0, ',', '.'),
                'icon' => 'wallet',
                'tone' => 'emerald',
                'subtitle' => "{$txCount} transaksi kuitansi TU",
                'href' => route('finance.transactions'),
            ];
        }

        return $stats;
    }

    /**
     * 6 Indikator Operasional & Layanan Terpadu (Tier 2)
     *
     * @return array<int, array{title: string, value: string, icon: string, tone: string, subtitle: ?string, href: ?string}>
     */
    public function operationalStats(): array
    {
        $user = Auth::user();
        $stats = [];

        // 1. Santri Asrama
        if ($user->can('view-boarding')) {
            $dormPlaced = DormPlacement::query()->where('status', 'active')->count();
            $roomCount = DormRoom::query()->count();

            $stats[] = [
                'title' => 'Santri di Asrama',
                'value' => number_format($dormPlaced, 0, ',', '.').' Santri',
                'icon' => 'home',
                'tone' => 'amber',
                'subtitle' => "{$roomCount} kamar asrama terdata",
                'href' => route('boarding.rooms'),
            ];
        }

        // 2. Izin Santri Gerbang
        if ($user->can('scan-gate-pass') || $user->can('view-boarding')) {
            $activeLeaves = StudentLeaveRequest::query()
                ->whereIn('status', ['approved', 'departed'])
                ->count();
            $departedToday = StudentLeaveRequest::query()
                ->whereDate('actual_departure_at', today())
                ->count();

            $stats[] = [
                'title' => 'Izin Keluar Santri',
                'value' => number_format($activeLeaves, 0, ',', '.').' Santri',
                'icon' => 'shield-check',
                'tone' => 'emerald',
                'subtitle' => "{$departedToday} keluar hari ini",
                'href' => route('boarding.leaves'),
            ];
        }

        // 3. Transaksi Kantin Cashless
        if ($user->can('cashier-cashless') || $user->can('view-cashless')) {
            $todayOrders = CanteenOrder::query()
                ->whereDate('created_at', today())
                ->count();
            $todaySales = CanteenOrder::query()
                ->whereDate('created_at', today())
                ->where('status', 'completed')
                ->sum('total_amount');

            $stats[] = [
                'title' => 'Penjualan Kantin',
                'value' => 'Rp '.number_format((float) $todaySales, 0, ',', '.'),
                'icon' => 'shopping-cart',
                'tone' => 'emerald',
                'subtitle' => "{$todayOrders} pesanan hari ini",
                'href' => route('cashless.pos'),
            ];
        }

        // 4. PPDB Santri Baru
        if ($user->can('view-admissions')) {
            $candidateCount = AdmissionCandidate::query()->count();
            $verifiedCount = AdmissionCandidate::query()->whereIn('status', ['verified', 'passed', 'enrolled'])->count();

            $stats[] = [
                'title' => 'Calon Santri (PPDB)',
                'value' => number_format($candidateCount, 0, ',', '.').' Pendaftar',
                'icon' => 'user-plus',
                'tone' => 'sky',
                'subtitle' => "{$verifiedCount} berkas terverifikasi",
                'href' => route('admissions.candidates'),
            ];
        }

        // 5. Perpustakaan & Sirkulasi
        if ($user->can('view-library')) {
            $activeLoans = LibraryLoan::query()->where('status', 'borrowed')->count();
            $totalBooks = LibraryBook::query()->count();

            $stats[] = [
                'title' => 'Sirkulasi Pustaka',
                'value' => number_format($activeLoans, 0, ',', '.').' Dipinjam',
                'icon' => 'book-open',
                'tone' => 'amber',
                'subtitle' => "Dari {$totalBooks} judul katalog",
                'href' => route('library.circulation'),
            ];
        }

        // 6. Guru & Pendidik
        if ($user->can('view-master-data')) {
            $teacherCount = Teacher::query()->where('is_active', true)->count();
            $classCount = SchoolClass::query()->count();

            $stats[] = [
                'title' => 'Guru & Pendidik',
                'value' => number_format($teacherCount, 0, ',', '.'),
                'icon' => 'briefcase',
                'tone' => 'sky',
                'subtitle' => "{$classCount} rombel belajar",
                'href' => route('master-data.teachers'),
            ];
        }

        return $stats;
    }

    /**
     * Indikator Admin & Notifikasi (Tier 3)
     *
     * @return array<int, array{title: string, value: string, icon: string, tone: string, subtitle: ?string, href: ?string}>
     */
    public function systemStats(): array
    {
        $user = Auth::user();
        $stats = [];

        if ($user->isSuperAdmin()) {
            $stats[] = [
                'title' => 'Pengguna Sistem',
                'value' => number_format(User::query()->count(), 0, ',', '.'),
                'icon' => 'user-cog',
                'tone' => 'slate',
                'subtitle' => 'Kelola peran & hak akses',
                'href' => route('settings.roles-and-permissions'),
            ];
        }

        $unread = $user->unreadNotifications()->count();
        if ($unread > 0) {
            $stats[] = [
                'title' => 'Notifikasi Baru',
                'value' => (string) $unread,
                'icon' => 'bell',
                'tone' => 'amber',
                'subtitle' => 'Pemberitahuan belum dibaca',
                'href' => null,
            ];
        }

        return $stats;
    }

    /**
     * Gabungan seluruh statistik untuk kompatibilitas ke belakang
     *
     * @return array<int, array{title: string, value: string, icon: string, tone: string, subtitle: ?string, href: ?string}>
     */
    public function stats(): array
    {
        return array_merge(
            $this->primaryStats(),
            $this->operationalStats(),
            $this->systemStats()
        );
    }

    /**
     * @return array<int, array{label: string, icon: string, href: string, hint: string}>
     */
    public function shortcuts(): array
    {
        $user = Auth::user();
        $links = [];

        if ($user->can('view-attendance')) {
            $links[] = [
                'label' => 'Presensi Siswa',
                'icon' => 'calendar-check',
                'href' => route('attendance.students'),
                'hint' => 'Input lembar kehadiran rombel cepat',
            ];
        }

        if ($user->can('cashier-finance') || $user->can('manage-finance')) {
            $links[] = [
                'label' => 'Kasir SPP (POS)',
                'icon' => 'credit-card',
                'href' => route('finance.cashier'),
                'hint' => 'Pembayaran tagihan & cetak kuitansi',
            ];
        }

        if ($user->can('view-tahfidz')) {
            $links[] = [
                'label' => 'Buku Tahfidz',
                'icon' => 'book-marked',
                'href' => route('islamic.tahfidz'),
                'hint' => 'Pencatatan setoran ayat & juz',
            ];
        }

        if ($user->can('cashier-cashless') || $user->can('manage-cashless')) {
            $links[] = [
                'label' => 'Kasir Kantin (POS)',
                'icon' => 'shopping-cart',
                'href' => route('cashless.pos'),
                'hint' => 'Transaksi cashless tap RFID santri',
            ];
        }

        if ($user->can('view-boarding')) {
            $links[] = [
                'label' => 'Perizinan Keluar',
                'icon' => 'file-text',
                'href' => route('boarding.leaves'),
                'hint' => 'Izin santri & tiket QR pos satpam',
            ];
        }

        if ($user->can('view-admissions')) {
            $links[] = [
                'label' => 'Pendaftaran PPDB',
                'icon' => 'user-plus',
                'href' => route('admissions.candidates'),
                'hint' => 'Calon santri baru & seleksi masuk',
            ];
        }

        if ($user->can('scan-gate-pass')) {
            $links[] = [
                'label' => 'Scanner Gerbang',
                'icon' => 'qr-code',
                'href' => route('boarding.scanner'),
                'hint' => 'Scan QR izin santri pos satpam',
            ];
        }

        if ($user->can('view-library')) {
            $links[] = [
                'label' => 'Sirkulasi Pustaka',
                'icon' => 'book-open',
                'href' => route('library.circulation'),
                'hint' => 'Peminjaman & pengembalian buku',
            ];
        }

        $links[] = [
            'label' => 'Profil Saya',
            'icon' => 'user-round',
            'href' => route('profile'),
            'hint' => 'Ubah kontak & kata sandi akun',
        ];

        return $links;
    }

    /**
     * @return Collection<int, Activity>|null
     */
    public function recentActivities(): ?Collection
    {
        if (! Auth::user()->can('viewAny', Activity::class)) {
            return null;
        }

        return Activity::query()->with('causer')->latest('id')->limit(6)->get();
    }

    /**
     * @return array{has_records: bool, subuh: int, dzuhur: int, ashar: int, maghrib: int, isya: int, total_recorded: int}|null
     */
    public function getTodayWorshipOverviewProperty(): ?array
    {
        if (! Auth::user()->can('view-tahfidz') && ! Auth::user()->can('view-master-data')) {
            return null;
        }

        $records = StudentDailyWorship::query()->whereDate('date', today())->get();
        if ($records->isEmpty()) {
            return [
                'has_records' => false,
                'subuh' => 0,
                'dzuhur' => 0,
                'ashar' => 0,
                'maghrib' => 0,
                'isya' => 0,
                'total_recorded' => 0,
            ];
        }

        return [
            'has_records' => true,
            'subuh' => $records->where('fajr', 'jamaah')->count(),
            'dzuhur' => $records->where('dhuhr', 'jamaah')->count(),
            'ashar' => $records->where('asr', 'jamaah')->count(),
            'maghrib' => $records->where('maghrib', 'jamaah')->count(),
            'isya' => $records->where('isha', 'jamaah')->count(),
            'total_recorded' => $records->count(),
        ];
    }

    /**
     * @return Collection<int, PaymentTransaction>|null
     */
    public function getRecentTransactionsProperty(): ?Collection
    {
        if (! Auth::user()->can('view-finance')) {
            return null;
        }

        return PaymentTransaction::query()
            ->with(['student.currentEnrollment.schoolClass', 'items.bill.feeCategory', 'receivedBy'])
            ->latest('paid_at')
            ->limit(4)
            ->get();
    }

    /**
     * @return array{total: int, present: int, sick: int, permit: int, absent: int, rate: float}|null
     */
    public function getTodayAttendanceSummaryProperty(): ?array
    {
        if (! Auth::user()->can('view-attendance')) {
            return null;
        }

        $records = StudentAttendance::query()->whereDate('date', today())->get();
        $total = $records->count();
        $present = $records->where('status', 'present')->count();
        $sick = $records->where('status', 'sick')->count();
        $permit = $records->where('status', 'permit')->count();
        $absent = $records->where('status', 'absent')->count();

        return [
            'total' => $total,
            'present' => $present,
            'sick' => $sick,
            'permit' => $permit,
            'absent' => $absent,
            'rate' => $total > 0 ? round(($present / $total) * 100, 1) : 0.0,
        ];
    }

    public function getActiveAcademicYearProperty(): ?AcademicYear
    {
        return AcademicYear::query()->where('is_active', true)->first();
    }

    /**
     * @return array<int, string>
     */
    protected function realtimeEvents(): array
    {
        return ['student.changed', 'attendance.changed', 'payment.received'];
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'primaryStats' => $this->primaryStats(),
            'operationalStats' => $this->operationalStats(),
            'systemStats' => $this->systemStats(),
            'stats' => $this->stats(),
            'shortcuts' => $this->shortcuts(),
            'activities' => $this->recentActivities(),
            'academicYear' => $this->activeAcademicYear,
            'worshipOverview' => $this->todayWorshipOverview,
            'recentTransactions' => $this->recentTransactions,
            'attendanceSummary' => $this->todayAttendanceSummary,
        ]);
    }
}
