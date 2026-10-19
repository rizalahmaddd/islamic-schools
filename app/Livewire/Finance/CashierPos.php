<?php

namespace App\Livewire\Finance;

use App\Models\PaymentTransaction;
use App\Models\PaymentTransactionItem;
use App\Models\Student;
use App\Models\StudentBill;
use App\Services\DocumentNumberGenerator;
use App\Services\FonnteService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app', ['heading' => 'Kasir SPP & Pembayaran'])]
#[Title('Kasir SPP & Pembayaran')]
class CashierPos extends Component
{
    public string $studentSearch = '';

    public ?int $selectedStudentId = null;

    /** @var array<int> */
    public array $selectedBills = [];

    /** @var array<int, float> */
    public array $allocations = [];

    public string $paymentMethod = 'cash'; // cash, transfer, qris

    public float|string $cashTendered = '';

    public string $notes = '';

    public ?int $completedTransactionId = null;

    public ?string $waStatusMessage = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('cashier-finance') || auth()->user()->can('manage-finance'), 403);
    }

    public function selectStudent(int $studentId): void
    {
        $this->selectedStudentId = $studentId;
        $this->studentSearch = '';
        $this->selectedBills = [];
        $this->allocations = [];
        $this->cashTendered = '';
        $this->notes = '';
        $this->completedTransactionId = null;
        $this->waStatusMessage = null;

        $unpaidBills = StudentBill::query()
            ->where('student_id', $studentId)
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        foreach ($unpaidBills as $bill) {
            $this->selectedBills[] = $bill->id;
            $this->allocations[$bill->id] = (float) $bill->remaining_amount;
        }
    }

    public function clearStudent(): void
    {
        $this->selectedStudentId = null;
        $this->selectedBills = [];
        $this->allocations = [];
        $this->cashTendered = '';
        $this->notes = '';
        $this->completedTransactionId = null;
        $this->waStatusMessage = null;
    }

    public function toggleBill(int $billId): void
    {
        if (in_array($billId, $this->selectedBills, true)) {
            $this->selectedBills = array_values(array_diff($this->selectedBills, [$billId]));
            unset($this->allocations[$billId]);
        } else {
            $this->selectedBills[] = $billId;
            $bill = StudentBill::query()->find($billId);
            if ($bill) {
                $this->allocations[$billId] = (float) $bill->remaining_amount;
            }
        }
    }

    public function updateAllocation(int $billId, mixed $amount): void
    {
        $bill = StudentBill::query()->find($billId);
        if (! $bill) {
            return;
        }

        $val = max(0, min((float) $amount, (float) $bill->remaining_amount));
        $this->allocations[$billId] = $val;
    }

    public function getTotalToPayProperty(): float
    {
        $total = 0.0;
        foreach ($this->selectedBills as $billId) {
            $total += (float) ($this->allocations[$billId] ?? 0);
        }

        return $total;
    }

    public function getChangeAmountProperty(): float
    {
        $tendered = (float) $this->cashTendered;
        if ($this->paymentMethod !== 'cash' || $tendered <= 0) {
            return 0.0;
        }

        return max(0.0, $tendered - $this->totalToPay);
    }

    public function processPayment(): void
    {
        abort_unless(auth()->user()->can('cashier-finance') || auth()->user()->can('manage-finance'), 403);

        if (! $this->selectedStudentId) {
            $this->dispatch('notify', message: 'Silakan pilih santri terlebih dahulu.', type: 'error');

            return;
        }

        if (empty($this->selectedBills) || $this->totalToPay <= 0) {
            $this->dispatch('notify', message: 'Pilih minimal satu pos tagihan dengan nominal bayar lebih dari Rp 0.', type: 'error');

            return;
        }

        $tendered = (float) $this->cashTendered;
        if ($this->paymentMethod === 'cash' && $tendered > 0 && $tendered < $this->totalToPay) {
            $this->dispatch('notify', message: 'Jumlah uang tunai yang diterima kurang dari total yang harus dibayar.', type: 'error');

            return;
        }

        $student = Student::query()->with('guardian')->findOrFail($this->selectedStudentId);

        try {
            $transaction = $this->recordPayment($student);
        } catch (StaleBillException) {
            $this->selectStudent($student->id);
            $this->dispatch('notify', message: 'Tagihan sudah berubah (kemungkinan dibayar kasir lain). Daftar tagihan dimuat ulang, silakan cek dan proses lagi.', type: 'error');

            return;
        }

        $this->completedTransactionId = $transaction->id;
        $this->dispatch('notify', message: "Pembayaran berhasil diproses! Kuitansi: {$transaction->receipt_number}", type: 'success');
    }

    protected function recordPayment(Student $student): PaymentTransaction
    {
        return DB::transaction(function () use ($student) {
            // Gunakan DocumentNumberGenerator bawaan starter kit (bebas race-condition berkat row-lock)
            $receiptNumber = app(DocumentNumberGenerator::class)->next(prefix: 'KW', pad: 5);

            $payment = PaymentTransaction::create([
                'receipt_number' => $receiptNumber,
                'student_id' => $student->id,
                'total_amount' => $this->totalToPay,
                'payment_method' => $this->paymentMethod,
                'received_by' => auth()->id(),
                'paid_at' => now(),
                'notes' => $this->notes ?: null,
            ]);

            foreach ($this->selectedBills as $billId) {
                $amountAllocated = (float) ($this->allocations[$billId] ?? 0);
                if ($amountAllocated <= 0) {
                    continue;
                }

                $bill = StudentBill::query()->where('student_id', $student->id)->lockForUpdate()->findOrFail($billId);

                // Tagihan bisa sudah dibayar kasir lain sejak halaman ini dibuka; cek ulang di bawah lock.
                if ($amountAllocated > $bill->remaining_amount + 0.005) {
                    throw new StaleBillException;
                }

                PaymentTransactionItem::create([
                    'payment_transaction_id' => $payment->id,
                    'student_bill_id' => $bill->id,
                    'amount_allocated' => $amountAllocated,
                ]);

                $newPaid = (float) $bill->amount_paid + $amountAllocated;
                $newStatus = $newPaid >= (float) $bill->amount_due ? 'paid' : 'partially_paid';

                $bill->update([
                    'amount_paid' => $newPaid,
                    'status' => $newStatus,
                ]);
            }

            return $payment;
        });
    }

    public function sendWhatsAppReceipt(): void
    {
        if (! $this->completedTransactionId) {
            return;
        }

        $transaction = PaymentTransaction::query()
            ->with(['student.guardian', 'student.currentEnrollment.schoolClass', 'items.bill.feeCategory'])
            ->find($this->completedTransactionId);

        if (! $transaction) {
            return;
        }

        $guardianPhone = $transaction->student?->guardian?->phone;
        if (! $guardianPhone) {
            $this->waStatusMessage = 'Nomor WhatsApp wali murid tidak terdaftar.';

            return;
        }

        $studentName = $transaction->student->name;
        $className = $transaction->student->currentEnrollment?->schoolClass?->name ?? '-';
        $formattedTotal = 'Rp '.number_format((float) $transaction->total_amount, 0, ',', '.');
        $date = $transaction->paid_at->translatedFormat('d F Y H:i');

        $itemLines = [];
        foreach ($transaction->items as $item) {
            $catName = $item->bill?->feeCategory?->name ?? 'Biaya Pendidikan';
            $period = $item->bill?->month ? "({$item->bill->month_name} {$item->bill->year})" : "({$item->bill?->year})";
            $sub = 'Rp '.number_format((float) $item->amount_allocated, 0, ',', '.');
            $itemLines[] = "- {$catName} {$period}: {$sub}";
        }
        $itemList = implode("\n", $itemLines);

        $message = "Assalamu'alaikum Warahmatullahi Wabarakatuh.\n\n"
            ."Yth. Orang Tua / Wali dari ananda *{$studentName}* (Kelas {$className}),\n\n"
            ."Alhamdulillah, kami informasikan bahwa pembayaran biaya pendidikan telah berhasil diterima oleh Kasir TU dengan rincian sbb:\n\n"
            ."📄 *No. Kuitansi:* {$transaction->receipt_number}\n"
            ."🗓️ *Waktu:* {$date}\n"
            ."💳 *Metode:* {$transaction->payment_method_label}\n"
            ."💰 *Total Bayar:* *{$formattedTotal}*\n\n"
            ."*Rincian Pembayaran:*\n{$itemList}\n\n"
            ."Jazakumullahu khairan katsiran atas kepercayaan Bapak/Ibu.\n\n"
            .'_Pesan otomatis dikirim oleh Sistem Informasi Sekolah & Pesantren_';

        $res = app(FonnteService::class)->send($guardianPhone, $message);

        if ($res['status']) {
            $this->waStatusMessage = "Kuitansi WhatsApp berhasil dikirim ke nomor {$guardianPhone}.";
            $this->dispatch('notify', message: 'Kuitansi berhasil dikirim via WhatsApp!', type: 'success');
        } else {
            $this->waStatusMessage = 'Gagal kirim WhatsApp: '.$res['message'];
            $this->dispatch('notify', message: $res['message'], type: 'error');
        }
    }

    public function newTransaction(): void
    {
        $this->clearStudent();
    }

    public function render(): View
    {
        $searchResults = collect();
        if (strlen(trim($this->studentSearch)) >= 2) {
            $searchResults = Student::query()
                ->with(['currentEnrollment.schoolClass', 'guardian'])
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->studentSearch.'%')
                        ->orWhere('nis', 'like', '%'.$this->studentSearch.'%')
                        ->orWhere('nisn', 'like', '%'.$this->studentSearch.'%');
                })
                ->limit(8)
                ->get();
        }

        $selectedStudent = null;
        $unpaidBills = collect();
        $completedTransaction = null;

        if ($this->selectedStudentId) {
            $selectedStudent = Student::query()
                ->with(['currentEnrollment.schoolClass', 'guardian'])
                ->find($this->selectedStudentId);

            $unpaidBills = StudentBill::query()
                ->with(['feeCategory', 'academicYear'])
                ->where('student_id', $this->selectedStudentId)
                ->whereIn('status', ['unpaid', 'partially_paid'])
                ->orderBy('year')
                ->orderBy('month')
                ->get();
        }

        if ($this->completedTransactionId) {
            $completedTransaction = PaymentTransaction::query()
                ->with(['student.guardian', 'receivedBy', 'items.bill.feeCategory'])
                ->find($this->completedTransactionId);
        }

        return view('livewire.finance.cashier-pos', [
            'searchResults' => $searchResults,
            'selectedStudent' => $selectedStudent,
            'unpaidBills' => $unpaidBills,
            'completedTransaction' => $completedTransaction,
        ]);
    }
}
