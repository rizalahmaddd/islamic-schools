<div class="space-y-6">
    {{-- Header Ringkasan & Stat Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Penerimaan Kas Hari Ini</span>
                <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400 mt-2">
                Rp {{ number_format($todayTotal, 0, ',', '.') }}
            </div>
            <span class="text-[11px] text-slate-400 block mt-1">Kas masuk TU per {{ now()->translatedFormat('d F Y') }}</span>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Penerimaan Kas Bulan Ini</span>
                <span class="p-2 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="text-2xl font-black font-mono text-sky-600 dark:text-sky-400 mt-2">
                Rp {{ number_format($monthTotal, 0, ',', '.') }}
            </div>
            <span class="text-[11px] text-slate-400 block mt-1">Akumulasi {{ now()->translatedFormat('F Y') }}</span>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Kuitansi Tercatat</span>
                <div class="text-2xl font-black text-slate-900 dark:text-white mt-2 font-mono">
                    {{ $transactions->total() }} <span class="text-xs font-normal text-slate-400">transaksi</span>
                </div>
                <span class="text-[11px] text-slate-400 block mt-1">Sesuai filter pencarian</span>
            </div>
            <x-primary-button
                :href="route('finance.cashier')"
                wire:navigate
                size="sm"
            >
                <i data-lucide="plus" class="w-3.5 h-3.5 mr-1"></i>
                <span>Buka Kasir</span>
            </x-primary-button>
        </div>
    </div>

    {{-- Toolbar Filter & Pencarian --}}
    <x-list-toolbar>
        <x-slot:search>
            <x-search-input wire:model.live.debounce.300ms="search" placeholder="Cari No. Kuitansi, Nama Siswa, atau NIS..." />
        </x-slot:search>

        <x-slot:filters>
            <div class="flex flex-wrap items-center gap-2">
                <x-text-input
                    type="date"
                    wire:model.live="dateFrom"
                    class="text-xs py-1.5 font-mono"
                    title="Dari Tanggal"
                />
                <span class="text-xs text-slate-400">s/d</span>
                <x-text-input
                    type="date"
                    wire:model.live="dateTo"
                    class="text-xs py-1.5 font-mono"
                    title="Sampai Tanggal"
                />

                <x-select wire:model.live="methodFilter" class="text-xs py-1.5">
                    <option value="all">Semua Metode Pembayaran</option>
                    <option value="cash">Tunai (Cash)</option>
                    <option value="transfer">Transfer Bank</option>
                    <option value="qris">QRIS</option>
                </x-select>
            </div>
        </x-slot:filters>

        <x-slot:actions>
            <x-secondary-button
                :href="route('finance.cashbook.print', ['start_date' => $dateFrom, 'end_date' => $dateTo, 'payment_method' => $methodFilter !== 'all' ? $methodFilter : null])"
                target="_blank"
                size="sm"
            >
                <i data-lucide="printer" class="w-3.5 h-3.5 mr-1.5 text-slate-400"></i>
                <span>Cetak Buku Kas</span>
            </x-secondary-button>

            <x-secondary-button
                type="button"
                wire:click="exportCsv"
                size="sm"
            >
                <i data-lucide="download" class="w-3.5 h-3.5 mr-1.5 text-slate-400"></i>
                <span>Ekspor CSV</span>
            </x-secondary-button>
        </x-slot:actions>
    </x-list-toolbar>

    {{-- Tabel Buku Kas & Transaksi Pembayaran --}}
    <x-table :pagination="$transactions">
        <x-slot:header>
            <x-table.th class="w-12 text-center">No</x-table.th>
            <x-table.th>No. Kuitansi</x-table.th>
            <x-table.th>Waktu</x-table.th>
            <x-table.th>Siswa &amp; Rombel</x-table.th>
            <x-table.th>Rincian Pos Biaya</x-table.th>
            <x-table.th class="text-center">Metode</x-table.th>
            <x-table.th class="text-right">Total Bayar</x-table.th>
            <x-table.th>Kasir</x-table.th>
            <x-table.th class="w-24 text-right">Aksi</x-table.th>
        </x-slot:header>

        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse ($transactions as $index => $tx)
                <x-table.tr wire:key="tx-{{ $tx->id }}">
                    <x-table.td class="text-center text-slate-400 font-mono text-xs">
                        {{ $transactions->firstItem() + $index }}
                    </x-table.td>
                    <x-table.td class="font-mono font-bold text-emerald-600 dark:text-emerald-400 text-xs">
                        <span class="inline-flex items-center gap-1.5">
                            <i data-lucide="receipt" class="w-3.5 h-3.5 text-emerald-500"></i>
                            <span>{{ $tx->receipt_number }}</span>
                        </span>
                    </x-table.td>
                    <x-table.td class="text-xs text-slate-600 dark:text-slate-400 font-mono">
                        {{ $tx->paid_at->format('d/m/Y H:i') }}
                    </x-table.td>
                    <x-table.td>
                        <div class="font-semibold text-slate-900 dark:text-white text-xs">{{ $tx->student?->name ?? '-' }}</div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">
                            NIS: {{ $tx->student?->nis ?? '-' }} &bull; {{ $tx->student?->currentEnrollment?->schoolClass?->name ?? '-' }}
                        </div>
                    </x-table.td>
                    <x-table.td class="text-xs">
                        @foreach ($tx->items as $item)
                            <div class="text-slate-700 dark:text-slate-300 text-[11px] flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>{{ $item->bill?->feeCategory?->name ?? 'SPP' }}</span>
                                @if ($item->bill?->month)
                                    <span class="text-slate-400">({{ $item->bill->month_name }})</span>
                                @endif
                            </div>
                        @endforeach
                    </x-table.td>
                    <x-table.td class="text-center">
                        <x-badge :color="$tx->payment_method === 'cash' ? 'emerald' : ($tx->payment_method === 'transfer' ? 'sky' : 'amber')">
                            {{ $tx->payment_method_label }}
                        </x-badge>
                    </x-table.td>
                    <x-table.td class="text-right font-mono font-bold text-slate-900 dark:text-white text-xs">
                        Rp {{ number_format((float) $tx->total_amount, 0, ',', '.') }}
                    </x-table.td>
                    <x-table.td class="text-xs text-slate-600 dark:text-slate-400">
                        {{ $tx->receivedBy?->name ?? '-' }}
                    </x-table.td>
                    <x-table.td class="text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a
                                href="{{ route('finance.receipts.print', $tx->id) }}"
                                target="_blank"
                                class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                title="Cetak Kuitansi"
                            >
                                <i data-lucide="printer" class="w-4 h-4"></i>
                            </a>
                            <button
                                type="button"
                                wire:click="viewDetails({{ $tx->id }})"
                                class="p-1.5 rounded-lg text-slate-500 hover:text-sky-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                                title="Lihat Rincian Transaksi"
                            >
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </x-table.td>
                </x-table.tr>
            @empty
                <x-table.tr>
                    <x-table.td colspan="9">
                        <x-empty-state
                            icon="receipt"
                            title="Belum Ada Transaksi Pembayaran"
                            description="Transaksi pembayaran kasir yang masuk pada rentang tanggal ini akan tampil di sini."
                        />
                    </x-table.td>
                </x-table.tr>
            @endforelse
        </tbody>
    </x-table>

    {{-- Modal Detail Transaksi Kuitansi --}}
    <x-modal name="view-transaction-details" max-width="md">
        @if ($viewingTransaction)
            <div class="p-6 space-y-4">
                <x-modal-header
                    :title="'Rincian Transaksi - '.$viewingTransaction->receipt_number"
                    :subtitle="$viewingTransaction->paid_at->translatedFormat('d F Y H:i').' • Kasir: '.($viewingTransaction->receivedBy?->name ?? '-')"
                />

                <div class="p-3.5 bg-slate-50 dark:bg-slate-900 rounded-xl space-y-2 text-xs border border-slate-100 dark:border-slate-800">
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Santri:</span>
                        <strong class="text-slate-900 dark:text-white">{{ $viewingTransaction->student?->name }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Wali Murid:</span>
                        <span class="text-slate-800 dark:text-slate-200">{{ $viewingTransaction->student?->guardian?->name ?? '-' }} ({{ $viewingTransaction->student?->guardian?->phone ?? '-' }})</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Metode Bayar:</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $viewingTransaction->payment_method_label }}</span>
                    </div>
                </div>

                <div class="space-y-2">
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300 block">Pos Biaya yang Dibayar:</span>
                    <div class="divide-y divide-slate-100 dark:divide-slate-800 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden text-xs">
                        @foreach ($viewingTransaction->items as $item)
                            <div class="p-2.5 flex items-center justify-between">
                                <div>
                                    <span class="font-medium text-slate-900 dark:text-white block">{{ $item->bill?->feeCategory?->name }}</span>
                                    <span class="text-[11px] text-slate-400">{{ $item->bill?->month ? $item->bill->month_name.' '.$item->bill->year : $item->bill?->year }}</span>
                                </div>
                                <span class="font-mono font-bold text-slate-900 dark:text-white">
                                    Rp {{ number_format((float) $item->amount_allocated, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-between p-3.5 bg-emerald-50 dark:bg-emerald-950/30 rounded-xl border border-emerald-200 dark:border-emerald-800/40 text-sm">
                    <span class="font-bold text-emerald-900 dark:text-emerald-300">Total Nominal:</span>
                    <span class="font-mono font-extrabold text-emerald-700 dark:text-emerald-400 text-base">
                        Rp {{ number_format((float) $viewingTransaction->total_amount, 0, ',', '.') }}
                    </span>
                </div>

                <x-modal-actions>
                    <a
                        href="{{ route('finance.receipts.print', $viewingTransaction->id) }}"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-xl shadow-xs transition"
                    >
                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                        <span>Cetak Kuitansi</span>
                    </a>
                    <x-secondary-button wire:click="closeDetails" type="button">
                        Tutup
                    </x-secondary-button>
                </x-modal-actions>
            </div>
        @endif
    </x-modal>
</div>
