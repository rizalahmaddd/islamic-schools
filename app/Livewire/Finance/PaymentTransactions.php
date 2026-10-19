<?php

namespace App\Livewire\Finance;

use App\Livewire\Concerns\WithDataTable;
use App\Models\PaymentTransaction;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app', ['heading' => 'Buku Kas & Transaksi'])]
#[Title('Buku Kas & Transaksi')]
class PaymentTransactions extends Component
{
    use WithDataTable;

    public string $search = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $methodFilter = 'all';

    public ?int $viewingTransactionId = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('view-finance'), 403);

        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->toDateString();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function updatingMethodFilter(): void
    {
        $this->resetPage();
    }

    public function viewDetails(int $id): void
    {
        $this->viewingTransactionId = $id;
        $this->dispatch('open-modal', 'view-transaction-details');
    }

    public function closeDetails(): void
    {
        $this->viewingTransactionId = null;
        $this->dispatch('close-modal', 'view-transaction-details');
    }

    public function exportCsv(): StreamedResponse
    {
        abort_unless(auth()->user()->can('view-finance'), 403);

        $query = $this->buildQuery();
        $filename = 'rekap_buku_kas_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['No', 'No. Kuitansi', 'Tanggal & Waktu', 'NIS', 'Nama Siswa', 'Kelas', 'Metode Bayar', 'Total Pembayaran', 'Kasir / Penerima', 'Catatan']);

            $no = 1;
            $query->chunk(200, function ($transactions) use ($handle, &$no) {
                foreach ($transactions as $tx) {
                    fputcsv($handle, [
                        $no++,
                        $tx->receipt_number,
                        $tx->paid_at->format('Y-m-d H:i:s'),
                        $tx->student?->nis ?? '-',
                        $tx->student?->name ?? '-',
                        $tx->student?->currentEnrollment?->schoolClass?->name ?? '-',
                        $tx->payment_method_label,
                        $tx->total_amount,
                        $tx->receivedBy?->name ?? '-',
                        $tx->notes ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return Builder<PaymentTransaction>
     */
    protected function buildQuery(): Builder
    {
        /** @var Builder<PaymentTransaction> $query */
        $query = PaymentTransaction::query()
            ->with(['student.currentEnrollment.schoolClass', 'receivedBy', 'items.bill.feeCategory'])
            ->when($this->search !== '', function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('receipt_number', 'like', $term)
                        ->orWhereHas('student', fn ($sq) => $sq->where('name', 'like', $term)->orWhere('nis', 'like', $term));
                });
            })
            ->when($this->dateFrom !== '', fn ($q) => $q->whereDate('paid_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn ($q) => $q->whereDate('paid_at', '<=', $this->dateTo))
            ->when($this->methodFilter !== 'all', fn ($q) => $q->where('payment_method', $this->methodFilter))
            ->orderByDesc('paid_at');

        return $query;
    }

    public function render(): View
    {
        $transactions = $this->buildQuery()->paginate(15);

        // Stats summary
        $todayTotal = (float) PaymentTransaction::query()
            ->whereDate('paid_at', now()->toDateString())
            ->sum('total_amount');

        $monthTotal = (float) PaymentTransaction::query()
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('total_amount');

        $viewingTransaction = $this->viewingTransactionId
            ? PaymentTransaction::query()->with(['student.guardian', 'receivedBy', 'items.bill.feeCategory'])->find($this->viewingTransactionId)
            : null;

        return view('livewire.finance.payment-transactions', [
            'transactions' => $transactions,
            'todayTotal' => $todayTotal,
            'monthTotal' => $monthTotal,
            'viewingTransaction' => $viewingTransaction,
        ]);
    }
}
