<?php

namespace App\Http\Controllers;

use App\Models\AdmissionCandidate;
use App\Models\InventoryItem;
use App\Models\InventoryRoom;
use App\Models\PaymentTransaction;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentLeaveRequest;
use App\Services\BillingService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Dokumen cetak: halaman Blade biasa (bukan Livewire) yang dibuka di tab baru lalu dicetak atau
 * disimpan PDF lewat window.print() bawaan browser, memakai layout x-layouts.print.
 */
class PrintController extends Controller
{
    public function attendanceReport(SchoolClass $schoolClass, Request $request): View
    {
        $month = (int) ($request->input('month') ?: now()->month);
        $year = (int) ($request->input('year') ?: now()->year);

        $schoolClass->loadMissing(['academicYear', 'homeroomTeacher']);
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();

        $attendances = StudentAttendance::query()
            ->where('class_id', $schoolClass->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->groupBy('student_id');

        $students = Student::query()
            ->whereHas('enrollments', function ($q) use ($schoolClass) {
                $q->where('class_id', $schoolClass->id)->where('status', 'active');
            })
            ->orderBy('name')
            ->get();

        $recap = $students->map(function (Student $student) use ($attendances) {
            $records = $attendances->get($student->id, collect());
            $present = $records->where('status', 'present')->count();
            $sick = $records->where('status', 'sick')->count();
            $permit = $records->where('status', 'permit')->count();
            $absent = $records->where('status', 'absent')->count();
            $total = $present + $sick + $permit + $absent;
            $rate = $total > 0 ? round(($present / $total) * 100, 1) : 0.0;

            return [
                'student' => $student,
                'present' => $present,
                'sick' => $sick,
                'permit' => $permit,
                'absent' => $absent,
                'total_recorded' => $total,
                'rate' => $rate,
            ];
        });

        $monthName = Carbon::createFromDate($year, $month, 1)->translatedFormat('F');

        return view('print.attendance-report', [
            'schoolClass' => $schoolClass,
            'recap' => $recap,
            'month' => $month,
            'year' => $year,
            'monthName' => $monthName,
        ]);
    }

    public function receipt(PaymentTransaction $paymentTransaction): View
    {
        Gate::authorize('view-receipt', $paymentTransaction);

        $paymentTransaction->loadMissing([
            'student.currentEnrollment.schoolClass',
            'student.guardian',
            'receivedBy',
            'items.bill.feeCategory',
        ]);

        $terbilang = BillingService::terbilang((float) $paymentTransaction->total_amount).' Rupiah';

        return view('print.receipt', [
            'transaction' => $paymentTransaction,
            'student' => $paymentTransaction->student,
            'terbilang' => $terbilang,
        ]);
    }

    public function cashbook(Request $request): View
    {
        $startDate = $request->input('start_date', now()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $paymentMethod = $request->input('payment_method');

        $query = PaymentTransaction::query()
            ->with(['student.currentEnrollment.schoolClass', 'receivedBy', 'items.bill.feeCategory'])
            ->whereDate('paid_at', '>=', $startDate)
            ->whereDate('paid_at', '<=', $endDate)
            ->orderBy('paid_at');

        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }

        $transactions = $query->get();
        $totalAmount = (float) $transactions->sum('total_amount');
        $totalCash = (float) $transactions->where('payment_method', 'cash')->sum('total_amount');
        $totalNonCash = $totalAmount - $totalCash;

        return view('print.cashbook', [
            'transactions' => $transactions,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalAmount' => $totalAmount,
            'totalCash' => $totalCash,
            'totalNonCash' => $totalNonCash,
        ]);
    }

    public function studentLeavePass(StudentLeaveRequest $leave): View
    {
        $isStaff = Gate::allows('view-boarding') || Gate::allows('scan-gate-pass') || auth()->user()?->hasRole(['musyrif', 'satpam', 'admin', 'superadmin']);
        $isOwnChild = auth()->user()?->guardian?->students()->where('students.id', $leave->student_id)->exists() ?? false;

        abort_unless($isStaff || $isOwnChild, 403);

        $leave->loadMissing(['student.guardian', 'student.dormPlacements.room.building', 'approver', 'securityDepartureUser', 'securityReturnUser']);

        return view('print.leave-pass', [
            'leave' => $leave,
            'student' => $leave->student,
        ]);
    }

    public function admissionRegistrationProof(string $registrationNumber): View
    {
        $candidate = AdmissionCandidate::with('batch.academicYear')
            ->where('registration_number', $registrationNumber)
            ->firstOrFail();

        return view('print.admission-registration', [
            'candidate' => $candidate,
            'batch' => $candidate->batch,
        ]);
    }

    public function inventoryItemQrLabel(InventoryItem $item): View
    {
        abort_unless(
            Gate::allows('view-inventory') || auth()->user()?->hasRole(['admin', 'superadmin', 'tata_usaha', 'staff']),
            403
        );

        $item->loadMissing('room');

        return view('print.inventory-label', [
            'items' => [$item],
            'title' => 'Label Inventaris '.$item->code,
        ]);
    }

    public function inventoryRoomQrLabels(InventoryRoom $room): View
    {
        abort_unless(
            Gate::allows('view-inventory') || auth()->user()?->hasRole(['admin', 'superadmin', 'tata_usaha', 'staff']),
            403
        );

        $room->loadMissing('items.room');

        return view('print.inventory-label', [
            'items' => $room->items,
            'title' => 'Label Inventaris Ruangan '.$room->name,
        ]);
    }
}
