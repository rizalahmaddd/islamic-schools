<div class="space-y-6">
    @if ($completedTransaction)
        {{-- Layar Sukses Transaksi Pembayaran --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-emerald-500/30 p-6 md:p-8 shadow-sm space-y-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl font-bold">
                    ✓
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Pembayaran Berhasil Diproses!</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        No. Kuitansi: <span class="font-mono font-semibold text-emerald-600 dark:text-emerald-400">{{ $completedTransaction->receipt_number }}</span>
                        • {{ $completedTransaction->paid_at->translatedFormat('d F Y H:i') }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-200 dark:border-slate-700/60 text-sm">
                <div>
                    <span class="text-xs text-slate-500 block">Siswa:</span>
                    <strong class="text-slate-900 dark:text-white">{{ $completedTransaction->student?->name }}</strong>
                    <span class="text-xs text-slate-500 block">NIS: {{ $completedTransaction->student?->nis }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-500 block">Total Diterima:</span>
                    <strong class="text-lg font-mono text-emerald-600 dark:text-emerald-400">
                        Rp {{ number_format((float) $completedTransaction->total_amount, 0, ',', '.') }}
                    </strong>
                    <span class="text-xs text-slate-500 block">Metode: {{ $completedTransaction->payment_method_label }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-500 block">Kasir / Penerima:</span>
                    <span class="text-slate-900 dark:text-white font-medium">{{ $completedTransaction->receivedBy?->name ?? '-' }}</span>
                </div>
            </div>

            @if ($waStatusMessage)
                <div class="p-3 text-xs rounded-lg {{ str_contains($waStatusMessage, 'berhasil') ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-300' : 'bg-amber-50 text-amber-800 dark:bg-amber-950/30 dark:text-amber-300' }}">
                    {{ $waStatusMessage }}
                </div>
            @endif

            <div class="flex flex-wrap items-center gap-3 pt-2">
                <x-primary-button
                    :href="route('finance.receipts.print', $completedTransaction->id)"
                    target="_blank"
                >
                    <i data-lucide="printer" class="w-4 h-4 mr-2"></i>
                    <span>Cetak Kuitansi Resmi</span>
                </x-primary-button>

                <x-secondary-button
                    wire:click="sendWhatsAppReceipt"
                    wire:loading.attr="disabled"
                    type="button"
                >
                    <x-loading-label target="sendWhatsAppReceipt" label="Kirim Kuitansi WhatsApp" loading="Mengirim WA..." />
                </x-secondary-button>

                <x-primary-button wire:click="newTransaction" type="button">
                    + Transaksi Baru
                </x-primary-button>
            </div>
        </div>
    @else
        {{-- Layar Kasir POS Standar --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- Kolom Kiri: Cari Siswa & Daftar Tagihan (8 Kolom) --}}
            <div class="lg:col-span-7 xl:col-span-8 space-y-6">
                {{-- Box Pencarian Siswa --}}
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/60 p-5 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            1. Identifikasi Santri / Siswa
                        </h3>
                        @if ($selectedStudent)
                            <x-text-button wire:click="clearStudent" class="text-rose-600 dark:text-rose-400">
                                Ganti Siswa
                            </x-text-button>
                        @endif
                    </div>

                    @if (! $selectedStudent)
                        <div class="relative">
                            <x-search-input
                                wire:model.live.debounce.300ms="studentSearch"
                                placeholder="Ketik Nama, NIS, atau scan barcode kartu santri..."
                                class="w-full"
                            />

                            @if ($searchResults->isNotEmpty())
                                <div class="absolute left-0 right-0 mt-2 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xl overflow-hidden z-30 divide-y divide-slate-100 dark:divide-slate-700">
                                    @foreach ($searchResults as $student)
                                        <button
                                            type="button"
                                            wire:click="selectStudent({{ $student->id }})"
                                            class="w-full text-left p-3 hover:bg-emerald-50 dark:hover:bg-slate-700/50 flex items-center justify-between transition"
                                        >
                                            <div>
                                                <div class="font-semibold text-slate-900 dark:text-white text-sm">{{ $student->name }}</div>
                                                <div class="text-xs text-slate-500 dark:text-slate-400">
                                                    NIS: <span class="font-mono">{{ $student->nis ?? '-' }}</span> • Kelas: {{ $student->currentEnrollment?->schoolClass?->name ?? 'Belum ada kelas' }}
                                                </div>
                                            </div>
                                            <span class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Pilih &rarr;</span>
                                        </button>
                                    @endforeach
                                </div>
                            @elseif (strlen(trim($studentSearch)) >= 2)
                                <div class="mt-2 text-xs text-slate-500 italic p-2">
                                    Tidak ditemukan siswa dengan kata kunci "{{ $studentSearch }}".
                                </div>
                            @endif
                        </div>
                    @else
                        {{-- Kartu Info Siswa Terpilih --}}
                        <div class="flex items-center gap-4 p-3.5 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-200 dark:border-slate-700/60">
                            <div class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-lg">
                                {{ strtoupper(substr($selectedStudent->name, 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-slate-900 dark:text-white truncate">{{ $selectedStudent->name }}</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    NIS: <span class="font-mono font-medium text-slate-700 dark:text-slate-300">{{ $selectedStudent->nis }}</span>
                                    • Kelas: <span class="font-medium text-slate-700 dark:text-slate-300">{{ $selectedStudent->currentEnrollment?->schoolClass?->name ?? '-' }}</span>
                                    • Wali: {{ $selectedStudent->guardian?->name ?? '-' }} ({{ $selectedStudent->guardian?->phone ?? 'No HP belum ada' }})
                                </p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Box Tagihan Santri Aktif --}}
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/60 p-5 shadow-sm space-y-4">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        2. Pilih Tagihan yang Akan Dibayar
                    </h3>

                    @if (! $selectedStudent)
                        <div class="py-10 text-center text-slate-400 text-sm">
                            Cari dan pilih santri di atas terlebih dahulu untuk menampilkan daftar tagihan aktif.
                        </div>
                    @elseif ($unpaidBills->isEmpty())
                        <div class="py-10 text-center space-y-2">
                            <div class="text-emerald-500 text-3xl font-bold">✓</div>
                            <div class="font-semibold text-slate-900 dark:text-white">Alhamdulillah! Tidak Ada Tagihan Tertunggak</div>
                            <p class="text-xs text-slate-500">Seluruh pos biaya pendidikan siswa ini telah lunas.</p>
                        </div>
                    @else
                        <div class="divide-y divide-slate-100 dark:divide-slate-700/60 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                            @foreach ($unpaidBills as $bill)
                                @php
                                    $isSelected = in_array($bill->id, $selectedBills, true);
                                    $rem = (float) $bill->remaining_amount;
                                @endphp
                                <div class="p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 {{ $isSelected ? 'bg-emerald-50/50 dark:bg-emerald-950/20' : 'hover:bg-slate-50 dark:hover:bg-slate-900/40' }} transition">
                                    <div class="flex items-start gap-3">
                                        <x-checkbox
                                            id="bill-{{ $bill->id }}"
                                            wire:click="toggleBill({{ $bill->id }})"
                                            :checked="$isSelected"
                                            class="mt-1"
                                        />
                                        <label for="bill-{{ $bill->id }}" class="cursor-pointer">
                                            <div class="font-semibold text-slate-900 dark:text-white text-sm">
                                                {{ $bill->feeCategory?->name }}
                                                @if ($bill->month)
                                                    <span class="text-emerald-700 dark:text-emerald-400 font-normal">({{ $bill->month_name }} {{ $bill->year }})</span>
                                                @else
                                                    <span class="text-slate-500 font-normal">({{ $bill->year }})</span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                                Jatuh Tempo: {{ $bill->due_date?->format('d/m/Y') ?? '-' }}
                                                @if ($bill->status === 'partially_paid')
                                                    • <x-badge color="amber">Sudah Dicicil Rp {{ number_format((float) $bill->amount_paid, 0, ',', '.') }}</x-badge>
                                                @endif
                                            </div>
                                        </label>
                                    </div>

                                    <div class="flex items-center gap-3 sm:self-center">
                                        <div class="text-right">
                                            <span class="text-xs text-slate-400 block">Sisa Tagihan</span>
                                            <span class="font-mono font-bold text-slate-900 dark:text-white text-sm">
                                                Rp {{ number_format($rem, 0, ',', '.') }}
                                            </span>
                                        </div>

                                        @if ($isSelected)
                                            <div class="w-36">
                                                <x-text-input
                                                    type="number"
                                                    wire:model.live.debounce.300ms="allocations.{{ $bill->id }}"
                                                    max="{{ $rem }}"
                                                    min="1000"
                                                    class="text-right text-xs font-mono py-1.5"
                                                    placeholder="Alokasi Rp"
                                                />
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Kolom Kanan: Ringkasan Checkout POS (4 Kolom) --}}
            <div class="lg:col-span-5 xl:col-span-4 space-y-6 sticky top-6">
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/60 p-5 shadow-sm space-y-5">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        3. Pembayaran & Kasir
                    </h3>

                    {{-- Total Display Jumbo --}}
                    <div class="p-4 bg-emerald-500/10 dark:bg-emerald-500/20 rounded-xl border border-emerald-500/30 text-center">
                        <span class="text-xs uppercase font-semibold tracking-wider text-emerald-800 dark:text-emerald-300 block mb-1">Total Tagihan Terpilih</span>
                        <div class="text-3xl font-extrabold font-mono text-emerald-700 dark:text-emerald-400">
                            Rp {{ number_format($this->totalToPay, 0, ',', '.') }}
                        </div>
                        <span class="text-[11px] text-slate-500 block mt-1">{{ count($selectedBills) }} pos biaya dipilih</span>
                    </div>

                    {{-- Metode Pembayaran --}}
                    <div>
                        <x-input-label for="paymentMethod" value="Metode Pembayaran" />
                        <x-select id="paymentMethod" wire:model.live="paymentMethod" class="w-full mt-1">
                            <option value="cash">Tunai (Cash di Kasir TU)</option>
                            <option value="transfer">Transfer Bank Resmi</option>
                            <option value="qris">QRIS / Pembayaran Digital</option>
                        </x-select>
                    </div>

                    {{-- Uang Diterima & Kembalian (Khusus Tunai) --}}
                    @if ($paymentMethod === 'cash')
                        <div class="space-y-3 pt-2 border-t border-slate-100 dark:border-slate-700">
                            <div>
                                <x-input-label for="cashTendered" value="Uang Diterima (Rp)" />
                                <x-text-input
                                    id="cashTendered"
                                    type="number"
                                    wire:model.live="cashTendered"
                                    placeholder="Contoh: 500000"
                                    class="w-full mt-1 font-mono text-base"
                                />
                            </div>

                            @if ($this->totalToPay > 0)
                                <x-secondary-button type="button" size="sm" wire:click="$set('cashTendered', {{ (int) $this->totalToPay }})" class="w-full justify-center">
                                    Uang Pas (Rp {{ number_format($this->totalToPay, 0, ',', '.') }})
                                </x-secondary-button>
                            @endif

                            @if ((float) $cashTendered > 0 && (float) $cashTendered < $this->totalToPay)
                                <div role="alert" class="flex items-center justify-between p-3 rounded-lg border border-rose-500/30 bg-rose-500/10 text-sm">
                                    <span class="text-rose-300">Uang kurang:</span>
                                    <span class="font-mono font-bold text-rose-300 text-base">
                                        Rp {{ number_format($this->totalToPay - (float) $cashTendered, 0, ',', '.') }}
                                    </span>
                                </div>
                            @elseif ((float) $cashTendered > 0)
                                <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-700 text-sm">
                                    <span class="text-slate-600 dark:text-slate-400">Kembalian:</span>
                                    <span class="font-mono font-bold text-slate-900 dark:text-white text-base">
                                        Rp {{ number_format($this->changeAmount, 0, ',', '.') }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Catatan Opsional --}}
                    <div>
                        <x-input-label for="notes" value="Catatan Transaksi (Opsional)" />
                        <x-text-input
                            id="notes"
                            type="text"
                            wire:model="notes"
                            placeholder="Mis: Titipan kakek santri"
                            class="w-full mt-1 text-xs"
                        />
                    </div>

                    {{-- Tombol Checkout --}}
                    <div class="pt-2">
                        <x-primary-button
                            wire:click="processPayment"
                            wire:loading.attr="disabled"
                            type="button"
                            class="w-full justify-center py-3 text-base font-bold shadow-md shadow-emerald-600/20"
                            :disabled="! $selectedStudent || empty($selectedBills) || $this->totalToPay <= 0 || ($paymentMethod === 'cash' && (float) $cashTendered > 0 && (float) $cashTendered < $this->totalToPay)"
                        >
                            <x-loading-label target="processPayment" label="Proses Pembayaran (Cetak Kuitansi)" loading="Memproses Kuitansi..." />
                        </x-primary-button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
