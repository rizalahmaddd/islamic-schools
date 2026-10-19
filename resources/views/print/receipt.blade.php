<x-layouts.print :title="'Kuitansi Pembayaran - '.$transaction->receipt_number">
    <div class="paper-sheet bg-white text-slate-900 rounded-xl border border-slate-300 p-6 md:p-8 max-w-3xl mx-auto">
        <x-print-letterhead
            :title="'KUITANSI PEMBAYARAN RESMI'"
            :number="'NO: '.$transaction->receipt_number"
            :date="'Tanggal: '.$transaction->paid_at->translatedFormat('d F Y H:i')"
        >
            <div class="text-right text-xs text-slate-600">
                <div>Metode Bayar: <span class="font-bold uppercase text-slate-800">{{ $transaction->payment_method_label }}</span></div>
                <div>Status: <span class="font-bold text-emerald-700 uppercase">LUNAS / TERCATAT</span></div>
            </div>
        </x-print-letterhead>

        {{-- Detail Identitas Siswa --}}
        <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 my-4 text-xs">
            <div class="grid grid-cols-2 gap-y-2">
                <div>
                    <span class="text-slate-500">Nama Siswa / Santri:</span>
                    <strong class="text-slate-900 block text-sm">{{ $student->name }}</strong>
                </div>
                <div>
                    <span class="text-slate-500">Kelas / Rombel:</span>
                    <strong class="text-slate-900 block text-sm">{{ $student->currentEnrollment?->schoolClass?->name ?? '-' }} (Tingkat {{ $student->currentEnrollment?->schoolClass?->grade_level ?? '-' }})</strong>
                </div>
                <div>
                    <span class="text-slate-500">Nomor Induk (NIS / NISN):</span>
                    <span class="font-mono text-slate-800 block">{{ $student->nis ?? '-' }} / {{ $student->nisn ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-500">Wali Murid / Penyetor:</span>
                    <span class="text-slate-800 block">{{ $student->guardian?->name ?? 'Wali Santri' }}</span>
                </div>
            </div>
        </div>

        {{-- Tabel Rincian Pos Pembayaran --}}
        <table class="w-full text-xs text-left border-collapse border border-slate-300 mt-2">
            <thead class="bg-slate-100 text-slate-800 uppercase font-semibold">
                <tr>
                    <th class="border border-slate-300 p-2.5 w-12 text-center">No</th>
                    <th class="border border-slate-300 p-2.5">Rincian Pos Biaya / Keterangan</th>
                    <th class="border border-slate-300 p-2.5 w-32 text-center">Periode Tagihan</th>
                    <th class="border border-slate-300 p-2.5 w-36 text-right">Nominal Dibayar</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transaction->items as $index => $item)
                    <tr>
                        <td class="border border-slate-300 p-2.5 text-center text-slate-600">{{ $index + 1 }}</td>
                        <td class="border border-slate-300 p-2.5 font-medium text-slate-800">
                            {{ $item->bill?->feeCategory?->name ?? 'Biaya Pendidikan' }}
                            @if ($item->bill?->notes)
                                <span class="text-slate-500 block text-[11px] font-normal">{{ $item->bill->notes }}</span>
                            @endif
                        </td>
                        <td class="border border-slate-300 p-2.5 text-center text-slate-700">
                            @if ($item->bill?->month)
                                {{ $item->bill->month_name }} {{ $item->bill->year }}
                            @else
                                {{ $item->bill?->year ?? '-' }}
                            @endif
                        </td>
                        <td class="border border-slate-300 p-2.5 text-right font-mono font-semibold text-slate-900">
                            Rp {{ number_format((float) $item->amount_allocated, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-slate-50 font-bold">
                    <td colspan="3" class="border border-slate-300 p-3 text-right uppercase text-slate-700">Total Pembayaran:</td>
                    <td class="border border-slate-300 p-3 text-right font-mono text-base text-emerald-800">
                        Rp {{ number_format((float) $transaction->total_amount, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>

        {{-- Terbilang --}}
        <div class="mt-3 p-3 bg-emerald-50/60 border border-emerald-200/60 rounded-md text-xs">
            <span class="text-emerald-900 font-semibold">Terbilang:</span>
            <span class="italic text-emerald-950 font-medium"># {{ $terbilang }} #</span>
        </div>

        @if ($transaction->notes)
            <div class="mt-2 text-xs text-slate-600">
                <span class="font-semibold">Catatan:</span> {{ $transaction->notes }}
            </div>
        @endif

        {{-- Kolom Tanda Tangan --}}
        <div class="mt-8 grid grid-cols-2 text-xs text-center text-slate-800">
            <div>
                <p>Penyetor / Wali Santri,</p>
                <div class="h-16"></div>
                <p class="font-semibold underline decoration-dotted">{{ $student->guardian?->name ?? '....................................' }}</p>
            </div>
            <div>
                <p>Kasir / Bagian Keuangan TU,</p>
                <div class="h-16"></div>
                <p class="font-semibold underline decoration-dotted">{{ $transaction->receivedBy?->name ?? auth()->user()->name ?? 'Petugas TU' }}</p>
            </div>
        </div>

        <div class="mt-6 pt-3 border-t border-slate-200 text-[10px] text-slate-500 text-center">
            * Kuitansi ini dicetak otomatis melalui Sistem Informasi Sekolah dan sah sebagai bukti pembayaran resmi.
        </div>
    </div>
</x-layouts.print>
