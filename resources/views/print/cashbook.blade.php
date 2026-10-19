<x-layouts.print :title="'Buku Kas Penerimaan TU - '.$startDate.' s/d '.$endDate">
    <div class="paper-sheet bg-white text-slate-900 rounded-xl border border-slate-300 p-6 md:p-8">
        <x-print-letterhead
            :title="'BUKU KAS HARIAN PENERIMAAN TU'"
            :number="'REKAPITULASI PEMBAYARAN KASIR'"
            :date="'Periode: '.$startDate.' s/d '.$endDate"
        >
            <div class="text-right text-xs text-slate-600">
                <div>Dicetak: <strong>{{ now()->translatedFormat('d F Y H:i') }}</strong></div>
                <div>Oleh: <strong>{{ auth()->user()->name ?? 'Petugas TU' }}</strong></div>
            </div>
        </x-print-letterhead>

        {{-- Ringkasan Total --}}
        <div class="grid grid-cols-3 gap-3 my-4">
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg text-center">
                <span class="text-xs text-slate-500 block">Total Tunai (Cash)</span>
                <strong class="text-sm font-mono text-emerald-700">Rp {{ number_format($totalCash, 0, ',', '.') }}</strong>
            </div>
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg text-center">
                <span class="text-xs text-slate-500 block">Total Non-Tunai (Transfer/QRIS)</span>
                <strong class="text-sm font-mono text-sky-700">Rp {{ number_format($totalNonCash, 0, ',', '.') }}</strong>
            </div>
            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-center">
                <span class="text-xs text-emerald-800 block font-semibold">Total Seluruh Penerimaan</span>
                <strong class="text-base font-mono text-emerald-900">Rp {{ number_format($totalAmount, 0, ',', '.') }}</strong>
            </div>
        </div>

        {{-- Tabel Rincian Transaksi --}}
        <table class="w-full text-xs text-left border-collapse border border-slate-300 mt-2">
            <thead class="bg-slate-100 text-slate-800 uppercase font-semibold">
                <tr>
                    <th class="border border-slate-300 p-2 w-10 text-center">No</th>
                    <th class="border border-slate-300 p-2 w-32">No. Kuitansi</th>
                    <th class="border border-slate-300 p-2 w-28">Waktu</th>
                    <th class="border border-slate-300 p-2">Siswa / Rombel</th>
                    <th class="border border-slate-300 p-2">Rincian Pos Biaya</th>
                    <th class="border border-slate-300 p-2 w-20 text-center">Metode</th>
                    <th class="border border-slate-300 p-2 w-28 text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $index => $tx)
                    <tr>
                        <td class="border border-slate-300 p-2 text-center text-slate-600">{{ $index + 1 }}</td>
                        <td class="border border-slate-300 p-2 font-mono font-medium">{{ $tx->receipt_number }}</td>
                        <td class="border border-slate-300 p-2">{{ $tx->paid_at->format('d/m/Y H:i') }}</td>
                        <td class="border border-slate-300 p-2">
                            <strong>{{ $tx->student?->name ?? '-' }}</strong>
                            <span class="text-slate-500 block text-[11px]">{{ $tx->student?->currentEnrollment?->schoolClass?->name ?? '-' }}</span>
                        </td>
                        <td class="border border-slate-300 p-2">
                            @foreach ($tx->items as $item)
                                <div class="text-[11px] text-slate-700">
                                    • {{ $item->bill?->feeCategory?->name ?? 'SPP' }}
                                    @if ($item->bill?->month)
                                        ({{ $item->bill->month_name }})
                                    @endif
                                    : Rp {{ number_format((float) $item->amount_allocated, 0, ',', '.') }}
                                </div>
                            @endforeach
                        </td>
                        <td class="border border-slate-300 p-2 text-center uppercase font-semibold text-[11px]">
                            {{ $tx->payment_method }}
                        </td>
                        <td class="border border-slate-300 p-2 text-right font-mono font-bold text-slate-900">
                            Rp {{ number_format((float) $tx->total_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="border border-slate-300 p-4 text-center text-slate-500 italic">
                            Tidak ada transaksi pembayaran pada rentang tanggal ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="bg-slate-100 font-bold">
                    <td colspan="6" class="border border-slate-300 p-2.5 text-right uppercase text-slate-800">Total Kas Masuk:</td>
                    <td class="border border-slate-300 p-2.5 text-right font-mono text-emerald-800 font-bold text-sm">
                        Rp {{ number_format($totalAmount, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>

        {{-- Kolom Tanda Tangan --}}
        <div class="mt-8 grid grid-cols-2 text-xs text-center text-slate-800">
            <div>
                <p>Mengetahui,</p>
                <p class="font-semibold">Kepala Tata Usaha / Mudir</p>
                <div class="h-16"></div>
                <p class="font-semibold underline decoration-dotted">{{ \App\Models\Setting::get('principal_name', '....................................') }}</p>
            </div>
            <div>
                <p>Bendahara Sekolah / Kasir,</p>
                <div class="h-16"></div>
                <p class="font-semibold underline decoration-dotted">{{ auth()->user()->name ?? '....................................' }}</p>
            </div>
        </div>
    </div>
</x-layouts.print>
