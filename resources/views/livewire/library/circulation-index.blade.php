<div class="space-y-6">
    {{-- Header & Subtitle --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Sirkulasi Perpustakaan</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Meja layanan sirkulasi peminjaman buku santri & guru, pengembalian buku, dan hitung denda otomatis.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <x-secondary-button :href="route('library.books')" wire:navigate>
                <x-icon name="book-open" class="mr-1.5 h-4 w-4" />
                Katalog Buku
            </x-secondary-button>
            @can('manage-library')
                <x-primary-button wire:click="openLoanModal">
                    <x-icon name="plus" class="mr-1.5 h-4 w-4" />
                    Catat Peminjaman Baru
                </x-primary-button>
            @endcan
        </div>
    </div>

    {{-- Filter Tabs & Search --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="w-full sm:w-80">
            <x-search-input wire:model.live.debounce.300ms="search" placeholder="Cari kode pinjam, judul buku, nama santri..." />
        </div>

        <x-segmented>
            <x-tab-button :active="$statusFilter === 'active'" wire:click="$set('statusFilter', 'active')">
                Sedang Dipinjam
            </x-tab-button>
            <x-tab-button :active="$statusFilter === 'overdue'" wire:click="$set('statusFilter', 'overdue')">
                Terlambat
            </x-tab-button>
            <x-tab-button :active="$statusFilter === 'returned'" wire:click="$set('statusFilter', 'returned')">
                Sudah Kembali
            </x-tab-button>
            <x-tab-button :active="$statusFilter === 'all'" wire:click="$set('statusFilter', 'all')">
                Semua Riwayat
            </x-tab-button>
        </x-segmented>
    </div>

    {{-- Data Table --}}
    <x-table :pagination="$loans">
        <thead class="bg-slate-950 text-slate-400 font-semibold uppercase tracking-wider border-b border-slate-800">
            <tr>
                <x-table.th>Kode Pinjam</x-table.th>
                <x-table.th>Buku / Kitab</x-table.th>
                <x-table.th>Peminjam</x-table.th>
                <x-table.th>Tgl Pinjam</x-table.th>
                <x-table.th>Jatuh Tempo</x-table.th>
                <x-table.th class="text-center">Status</x-table.th>
                <x-table.th class="text-right">Denda</x-table.th>
                <x-table.th class="text-right">Aksi</x-table.th>
            </tr>
        </thead>

        <tbody class="divide-y divide-slate-800/60">
            @forelse ($loans as $loan)
                @php
                    /** @var \App\Models\LibraryLoan $loan */
                    $isOverdue = $loan->status === 'active' && now()->startOfDay()->gt(\Carbon\Carbon::parse($loan->due_date)->startOfDay());
                @endphp
                <x-table.tr wire:key="loan-{{ $loan->id }}">
                    <x-table.td class="font-mono text-xs font-bold text-slate-900 dark:text-white">
                        {{ $loan->loan_code }}
                    </x-table.td>
                    <x-table.td class="font-medium text-slate-900 dark:text-white">
                        <div>{{ $loan->book?->title ?? 'Buku dihapus' }}</div>
                        <div class="font-mono text-[11px] text-slate-500">{{ $loan->book?->code }} ({{ $loan->book?->rack_location ?: 'Rak Umum' }})</div>
                    </x-table.td>
                    <x-table.td class="text-xs">
                        <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $loan->borrower_name }}</div>
                        <div class="text-slate-500 capitalize">{{ $loan->borrower_type === 'student' ? 'Santri (NIS: '.($loan->student?->nis ?: '-').')' : 'Guru / Ustadz' }}</div>
                    </x-table.td>
                    <x-table.td class="text-xs text-slate-700 dark:text-slate-300">
                        {{ \Carbon\Carbon::parse($loan->borrow_date)->format('d/m/Y') }}
                    </x-table.td>
                    <x-table.td class="text-xs font-medium {{ $isOverdue ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-slate-700 dark:text-slate-300' }}">
                        {{ \Carbon\Carbon::parse($loan->due_date)->format('d/m/Y') }}
                    </x-table.td>
                    <x-table.td class="text-center">
                        @if ($loan->status === 'returned')
                            <x-badge color="emerald">Dikembalikan</x-badge>
                        @elseif ($isOverdue || $loan->status === 'overdue')
                            <x-badge color="rose">Terlambat</x-badge>
                        @else
                            <x-badge color="sky">Aktif Dipinjam</x-badge>
                        @endif
                    </x-table.td>
                    <x-table.td class="text-right font-mono text-xs">
                        @if ($loan->status === 'returned' && $loan->fine_amount > 0)
                            <span class="text-slate-700 dark:text-slate-300">Rp {{ number_format((float) $loan->fine_amount, 0, ',', '.') }}</span>
                        @elseif ($isOverdue)
                            @php
                                $overdueDays = max(0, \Carbon\Carbon::parse($loan->due_date)->startOfDay()->diffInDays(now()->startOfDay()));
                                $estFine = $overdueDays * 1000;
                            @endphp
                            <span class="font-bold text-rose-600 dark:text-rose-400">Rp {{ number_format((float) $estFine, 0, ',', '.') }} ({{ $overdueDays }} hr)</span>
                        @else
                            <span class="text-slate-400">Rp 0</span>
                        @endif
                    </x-table.td>
                    <x-table.td class="text-right">
                        @if ($loan->status !== 'returned')
                            @can('manage-library')
                                <x-primary-button size="xs" wire:click="openReturnModal({{ $loan->id }})">
                                    <x-icon name="check" class="mr-1 h-3.5 w-3.5" />
                                    Kembalikan
                                </x-primary-button>
                            @endcan
                        @else
                            <span class="text-[11px] text-slate-400">Selesai ({{ \Carbon\Carbon::parse($loan->return_date)->format('d/m/Y') }})</span>
                        @endif
                    </x-table.td>
                </x-table.tr>
            @empty
                <x-table.tr>
                    <x-table.td colspan="8" class="p-0">
                        <x-empty-state
                            icon="arrow-left-right"
                            title="Tidak ada transaksi peminjaman"
                            description="Catat peminjaman buku santri atau guru dengan mengeklik tombol di bawah ini."
                        >
                            @can('manage-library')
                                <x-slot:actions>
                                    <x-primary-button wire:click="openLoanModal">
                                        <x-icon name="plus" class="mr-1.5 h-4 w-4" />
                                        Catat Peminjaman Buku
                                    </x-primary-button>
                                </x-slot:actions>
                            @endcan
                        </x-empty-state>
                    </x-table.td>
                </x-table.tr>
            @endforelse
        </tbody>
    </x-table>

    {{-- Modal Catat Peminjaman Baru --}}
    <x-record-form-modal
        name="loan-modal"
        title="Catat Peminjaman Buku / Kitab"
        subtitle="Stok buku akan berkurang otomatis dan batas pengembalian dihitung otomatis."
        icon="book-open"
        max-width="lg"
    >
        <form wire:submit="saveLoan" id="loan-form" class="space-y-4">
            <div>
                <x-input-label for="selectedBookId" value="Pilih Buku / Kitab *" />
                <x-select id="selectedBookId" class="mt-1 block w-full" wire:model="selectedBookId" required>
                    <option value="">Pilih Buku yang Tersedia</option>
                    @foreach ($availableBooks as $b)
                        <option value="{{ $b->id }}">{{ $b->title }} (Stok: {{ $b->available_copies }} eks) [{{ $b->code }}]</option>
                    @endforeach
                </x-select>
                <x-input-error :messages="$errors->get('selectedBookId')" class="mt-1" />
                <x-input-error :messages="$errors->get('book_id')" class="mt-1" />
            </div>

            <div>
                <x-input-label value="Tipe Peminjam *" />
                <div class="mt-1 flex gap-4">
                    <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                        <input type="radio" value="student" wire:model.live="borrowerType" class="text-emerald-600 focus:ring-emerald-500" />
                        <span>Santri / Siswa</span>
                    </label>
                    <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                        <input type="radio" value="teacher" wire:model.live="borrowerType" class="text-emerald-600 focus:ring-emerald-500" />
                        <span>Guru / Ustadz / Staf</span>
                    </label>
                </div>
            </div>

            @if ($borrowerType === 'student')
                <div>
                    <x-input-label for="selectedStudentId" value="Pilih Santri / Siswa *" />
                    <x-select id="selectedStudentId" class="mt-1 block w-full" wire:model="selectedStudentId" required>
                        <option value="">Pilih Nama Santri</option>
                        @foreach ($students as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} (NIS: {{ $s->nis ?: '-' }})</option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('selectedStudentId')" class="mt-1" />
                    <x-input-error :messages="$errors->get('borrower_id')" class="mt-1" />
                </div>
            @else
                <div>
                    <x-input-label for="selectedTeacherId" value="Pilih Guru / Ustadz *" />
                    <x-select id="selectedTeacherId" class="mt-1 block w-full" wire:model="selectedTeacherId" required>
                        <option value="">Pilih Nama Guru / Staf</option>
                        @foreach ($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('selectedTeacherId')" class="mt-1" />
                </div>
            @endif

            <div>
                <x-input-label for="loanDays" value="Durasi Pinjam (Hari) *" />
                <x-text-input id="loanDays" type="number" min="1" max="60" class="mt-1 block w-full" wire:model="loanDays" required />
                <p class="mt-1 text-xs text-slate-500">Standar peminjaman kitab/buku adalah 7 hari. Denda keterlambatan: Rp 1.000 / hari.</p>
                <x-input-error :messages="$errors->get('loanDays')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="loanNotes" value="Catatan Peminjaman" />
                <x-textarea id="loanNotes" rows="2" class="mt-1 block w-full" wire:model="loanNotes" placeholder="Catatan nomor eksemplar fisik atau kondisi awal..." />
                <x-input-error :messages="$errors->get('loanNotes')" class="mt-1" />
            </div>
        </form>

        <x-slot:actions>
            <x-secondary-button wire:click="closeLoanModal">Batal</x-secondary-button>
            <x-primary-button type="submit" form="loan-form">
                <x-loading-label target="saveLoan" label="Proses Peminjaman" />
            </x-primary-button>
        </x-slot:actions>
    </x-record-form-modal>

    {{-- Modal Pengembalian Buku & Hitung Denda --}}
    <x-record-form-modal
        name="return-modal"
        title="Konfirmasi Pengembalian Buku"
        subtitle="Verifikasi kondisi fisik buku dan pelunasan denda keterlambatan."
        icon="check"
        max-width="md"
    >
        @if ($returningLoan)
            <form wire:submit="processReturn" id="return-form" class="space-y-4">
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-xs dark:border-slate-800 dark:bg-slate-900/50 space-y-1">
                    <div><span class="text-slate-500">Judul Buku:</span> <strong class="text-slate-900 dark:text-white">{{ $returningLoan->book?->title }}</strong></div>
                    <div><span class="text-slate-500">Peminjam:</span> <strong class="text-slate-800 dark:text-slate-200">{{ $returningLoan->borrower_name }}</strong></div>
                    <div><span class="text-slate-500">Tgl Pinjam / Jatuh Tempo:</span> {{ \Carbon\Carbon::parse($returningLoan->borrow_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($returningLoan->due_date)->format('d/m/Y') }}</div>
                </div>

                @if ($calculatedFine > 0)
                    <div class="rounded-lg border border-rose-200 bg-rose-50 p-3 dark:border-rose-900/50 dark:bg-rose-950/20">
                        <div class="flex items-center justify-between text-xs font-bold text-rose-700 dark:text-rose-400">
                            <span>Keterlambatan Terhitung:</span>
                            <span>Rp {{ number_format($calculatedFine, 0, ',', '.') }}</span>
                        </div>
                        <p class="mt-1 text-[11px] text-rose-600 dark:text-rose-300">
                            Terlambat melewati batas jatuh tempo. Denda dihitung Rp 1.000 per hari keterlambatan.
                        </p>
                    </div>
                @endif

                <div>
                    <x-input-label for="customFine" value="Total Denda Diterima (Rp) *" />
                    <x-text-input id="customFine" type="number" min="0" step="500" class="mt-1 block w-full" wire:model="customFine" required />
                    <p class="mt-1 text-[11px] text-slate-500">Ubah menjadi 0 jika denda dihapuskan / diberikan keringanan.</p>
                    <x-input-error :messages="$errors->get('customFine')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="returnNotes" value="Catatan Kondisi Pengembalian" />
                    <x-textarea id="returnNotes" rows="2" class="mt-1 block w-full" wire:model="returnNotes" placeholder="Catatan kondisi buku saat dikembalikan..." />
                    <x-input-error :messages="$errors->get('returnNotes')" class="mt-1" />
                </div>
            </form>
        @endif

        <x-slot:actions>
            <x-secondary-button wire:click="closeReturnModal">Batal</x-secondary-button>
            <x-primary-button type="submit" form="return-form">
                <x-loading-label target="processReturn" label="Terima Pengembalian" />
            </x-primary-button>
        </x-slot:actions>
    </x-record-form-modal>
</div>
