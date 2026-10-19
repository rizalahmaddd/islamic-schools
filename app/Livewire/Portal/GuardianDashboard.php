<?php

namespace App\Livewire\Portal;

use App\Models\OnlinePaymentTransaction;
use App\Models\PaymentTransaction;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentBill;
use App\Models\StudentDailyWorship;
use App\Models\StudentWallet;
use App\Models\TahfidzSession;
use App\Models\WalletTransaction;
use App\Services\TripayService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app', ['heading' => 'Portal Mandiri Wali Murid'])]
#[Title('Portal Wali Murid')]
class GuardianDashboard extends Component
{
    public ?int $selectedStudentId = null;

    public string $activeTab = 'summary'; // 'summary', 'attendance', 'tahfidz', 'finance'

    public int $attendanceMonth = 1;

    public int $attendanceYear = 2026;

    public bool $showPaymentModal = false;

    public ?int $payingBillId = null;

    public string $selectedPaymentChannel = 'QRIS';

    public ?int $activeOnlineTxId = null;

    public function mount(): void
    {
        Gate::authorize('view-guardian-portal');

        $this->attendanceMonth = (int) now()->month;
        $this->attendanceYear = (int) now()->year;

        $students = $this->getStudents();
        $this->selectedStudentId = $students->first()?->id;
    }

    /**
     * @return Collection<int, Student>
     */
    public function getStudents(): Collection
    {
        $user = Auth::user();
        $guardian = $user->guardian;

        if ($guardian) {
            return $guardian->students()
                ->where('status', 'active')
                ->with(['currentEnrollment.schoolClass.homeroomTeacher', 'currentEnrollment.academicYear'])
                ->orderBy('name')
                ->get();
        }

        if ($user->isSuperAdmin()) {
            return Student::query()
                ->where('status', 'active')
                ->with(['currentEnrollment.schoolClass.homeroomTeacher', 'currentEnrollment.academicYear'])
                ->orderBy('name')
                ->limit(10)
                ->get();
        }

        return collect();
    }

    /**
     * Dropdown anak terikat langsung ke properti ini, jadi nilai dari browser harus dicek ulang:
     * wali hanya boleh melihat anaknya sendiri.
     */
    public function updatedSelectedStudentId(mixed $value): void
    {
        $students = $this->getStudents();

        if (! $students->contains('id', (int) $value)) {
            $this->selectedStudentId = $students->first()?->id;
        }
    }

    public function selectStudent(int $studentId): void
    {
        $students = $this->getStudents();
        if ($students->contains('id', $studentId)) {
            $this->selectedStudentId = $studentId;
        }
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['summary', 'attendance', 'tahfidz', 'finance', 'cashless'], true)) {
            $this->activeTab = $tab;
        }
    }

    public function getSelectedStudentProperty(): ?Student
    {
        if (! $this->selectedStudentId) {
            return null;
        }

        return Student::query()
            ->with([
                'currentEnrollment.schoolClass.homeroomTeacher',
                'currentEnrollment.academicYear',
                'guardian',
            ])
            ->find($this->selectedStudentId);
    }

    public function getTodayAttendanceProperty(): ?StudentAttendance
    {
        if (! $this->selectedStudentId) {
            return null;
        }

        return StudentAttendance::query()
            ->where('student_id', $this->selectedStudentId)
            ->whereDate('date', today())
            ->first();
    }

    public function getLatestTahfidzProperty(): ?TahfidzSession
    {
        if (! $this->selectedStudentId) {
            return null;
        }

        return TahfidzSession::query()
            ->with(['teacher', 'surah'])
            ->where('student_id', $this->selectedStudentId)
            ->latest('recorded_at')
            ->first();
    }

    public function getTodayWorshipProperty(): ?StudentDailyWorship
    {
        if (! $this->selectedStudentId) {
            return null;
        }

        return StudentDailyWorship::query()
            ->where('student_id', $this->selectedStudentId)
            ->whereDate('date', today())
            ->first();
    }

    /**
     * @return Collection<int, StudentBill>
     */
    public function getUnpaidBillsProperty(): Collection
    {
        if (! $this->selectedStudentId) {
            return collect();
        }

        return StudentBill::query()
            ->with('feeCategory')
            ->where('student_id', $this->selectedStudentId)
            ->unpaid()
            ->orderBy('due_date')
            ->get();
    }

    public function getTotalOutstandingProperty(): float
    {
        return (float) $this->unpaidBills->sum(fn (StudentBill $b) => $b->remaining_amount);
    }

    /**
     * @return array<int, array{juz: int, status: string, total_sessions: int, last_date: ?string}>
     */
    public function getTahfidzJuzProgressProperty(): array
    {
        if (! $this->selectedStudentId) {
            return [];
        }

        $sessions = TahfidzSession::query()
            ->where('student_id', $this->selectedStudentId)
            ->get()
            ->groupBy('juz');

        $result = [];
        for ($i = 1; $i <= 30; $i++) {
            $juzSessions = $sessions->get($i, collect());
            $hasMutqin = $juzSessions->whereIn('grade', ['A', 'Mumtaz'])->isNotEmpty();
            $count = $juzSessions->count();

            $status = 'unstarted';
            if ($hasMutqin) {
                $status = 'mutqin';
            } elseif ($count > 0) {
                $status = 'in_progress';
            }

            $result[] = [
                'juz' => $i,
                'status' => $status,
                'total_sessions' => $count,
                'last_date' => $juzSessions->max('recorded_at'),
            ];
        }

        return $result;
    }

    /**
     * @return Collection<int, StudentAttendance>
     */
    public function getMonthlyAttendancesProperty(): Collection
    {
        if (! $this->selectedStudentId) {
            return collect();
        }

        return StudentAttendance::query()
            ->where('student_id', $this->selectedStudentId)
            ->whereYear('date', $this->attendanceYear)
            ->whereMonth('date', $this->attendanceMonth)
            ->orderByDesc('date')
            ->get();
    }

    /**
     * @return array{present: int, sick: int, permit: int, absent: int, rate: float}
     */
    public function getAttendanceStatsProperty(): array
    {
        $records = $this->monthlyAttendances;
        $present = $records->where('status', 'present')->count();
        $sick = $records->where('status', 'sick')->count();
        $permit = $records->where('status', 'permit')->count();
        $absent = $records->where('status', 'absent')->count();
        $total = $present + $sick + $permit + $absent;

        return [
            'present' => $present,
            'sick' => $sick,
            'permit' => $permit,
            'absent' => $absent,
            'rate' => $total > 0 ? round(($present / $total) * 100, 1) : 0.0,
        ];
    }

    public function getWalletProperty(): ?StudentWallet
    {
        if (! $this->selectedStudent) {
            return null;
        }

        return $this->selectedStudent->wallet;
    }

    /**
     * @return Collection<int, WalletTransaction>
     */
    public function getRecentCanteenPurchasesProperty(): Collection
    {
        if (! $this->selectedStudent || ! $this->selectedStudent->wallet) {
            return collect();
        }

        return $this->selectedStudent->wallet->transactions()
            ->with(['merchant', 'order.items'])
            ->where('transaction_type', 'purchase')
            ->latest()
            ->limit(10)
            ->get();
    }

    /**
     * @return Collection<int, PaymentTransaction>
     */
    public function getRecentPaymentsProperty(): Collection
    {
        if (! $this->selectedStudentId) {
            return collect();
        }

        return PaymentTransaction::query()
            ->where('student_id', $this->selectedStudentId)
            ->with(['items.bill.feeCategory', 'receivedBy'])
            ->latest('paid_at')
            ->limit(10)
            ->get();
    }

    /**
     * Id tagihan dan transaksi datang dari browser, jadi hanya yang milik anak wali ini yang dilayani.
     */
    protected function ownBill(?int $billId): ?StudentBill
    {
        if (! $billId) {
            return null;
        }

        return StudentBill::with(['feeCategory', 'student.guardian'])
            ->whereIn('student_id', $this->getStudents()->pluck('id'))
            ->find($billId);
    }

    public function getActiveOnlineTxProperty(): ?OnlinePaymentTransaction
    {
        if (! $this->activeOnlineTxId) {
            return null;
        }

        return OnlinePaymentTransaction::query()
            ->whereIn('student_id', $this->getStudents()->pluck('id'))
            ->find($this->activeOnlineTxId);
    }

    public function getPayingBillProperty(): ?StudentBill
    {
        return $this->ownBill($this->payingBillId);
    }

    public function openPaymentModal(int $billId): void
    {
        abort_unless($this->ownBill($billId), 403);

        $this->payingBillId = $billId;

        $existing = OnlinePaymentTransaction::query()
            ->where('student_bill_id', $billId)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        $this->activeOnlineTxId = $existing?->id;
        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->payingBillId = null;
        $this->activeOnlineTxId = null;
    }

    public function createOnlinePayment(): void
    {
        if (! $this->payingBillId) {
            return;
        }

        $bill = $this->ownBill($this->payingBillId);
        abort_unless($bill, 403);

        /** @var TripayService $tripayService */
        $tripayService = app(TripayService::class);

        $tx = $tripayService->createClosedTransaction($bill, $this->selectedPaymentChannel, Auth::user());
        $this->activeOnlineTxId = $tx->id;
    }

    public function checkPaymentStatus(): void
    {
        $tx = $this->activeOnlineTx;
        if (! $tx) {
            return;
        }

        $tx->refresh();
        if ($tx->isSettled()) {
            session()->flash('status', 'Alhamdulillah! Pembayaran tagihan telah berhasil diverifikasi lunas.');
            $this->closePaymentModal();
        }
    }

    public function render()
    {
        $students = $this->getStudents();
        $selectedStudent = $this->selectedStudent;
        /** @var TripayService $tripayService */
        $tripayService = app(TripayService::class);

        return view('livewire.portal.guardian-dashboard', [
            'students' => $students,
            'selectedStudent' => $selectedStudent,
            'todayAttendance' => $this->todayAttendance,
            'latestTahfidz' => $this->latestTahfidz,
            'todayWorship' => $this->todayWorship,
            'unpaidBills' => $this->unpaidBills,
            'totalOutstanding' => $this->totalOutstanding,
            'juzProgress' => $this->tahfidzJuzProgress,
            'monthlyAttendances' => $this->monthlyAttendances,
            'attendanceStats' => $this->attendanceStats,
            'recentPayments' => $this->recentPayments,
            'paymentChannels' => $tripayService->getPaymentChannels(),
            'payingBill' => $this->payingBill,
            'activeOnlineTx' => $this->activeOnlineTx,
            'wallet' => $this->wallet,
            'canteenPurchases' => $this->recentCanteenPurchases,
            'months' => [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
            ],
        ]);
    }
}
