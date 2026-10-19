<div class="space-y-6 sm:space-y-7">
    {{-- Header Laporan Terpadu --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200 dark:border-slate-800">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                    <i data-lucide="file-bar-chart" class="w-5 h-5"></i>
                </span>
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        Pusat Laporan &amp; Rekapitulasi Terpadu
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        Monitoring analitik komprehensif: Keuangan SPP, Presensi, Tahfidz, Keasramaan, dan PPDB.
                    </p>
                </div>
            </div>
        </div>

        {{-- Actions: Cetak Dokumen & Export CSV --}}
        <div class="flex items-center gap-2.5 self-start sm:self-auto">
            <x-secondary-button
                type="button"
                onclick="window.print()"
                class="cursor-pointer"
                title="Cetak tampilan laporan saat ini"
            >
                <i data-lucide="printer" class="w-4 h-4 mr-1.5 text-slate-400"></i>
                <span>Cetak Dokumen</span>
            </x-secondary-button>

            <x-primary-button
                type="button"
                wire:click="exportCsv"
                class="cursor-pointer"
                title="Unduh seluruh data ringkasan dalam format CSV/Excel"
            >
                <i data-lucide="download" class="w-4 h-4 mr-1.5"></i>
                <span>Ekspor CSV</span>
            </x-primary-button>
        </div>
    </div>

    {{-- Filter Toolbar Terpadu --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 sm:p-5 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="filter" class="w-4 h-4 text-emerald-500"></i>
                Filter Parameter Laporan
            </span>
            <span class="text-[11px] text-slate-500 font-mono">
                Periode: {{ \Carbon\Carbon::parse($date_from)->translatedFormat('d M Y') }} &ndash; {{ \Carbon\Carbon::parse($date_to)->translatedFormat('d M Y') }}
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            {{-- Preset Periode --}}
            <div>
                <x-input-label value="Rentang Waktu Cepat" />
                <x-select wire:model.live="preset" class="w-full mt-1">
                    <option value="today">Hari Ini</option>
                    <option value="this_week">Minggu Ini</option>
                    <option value="this_month">Bulan Ini</option>
                    <option value="last_month">Bulan Lalu</option>
                    <option value="this_year">Tahun Ini</option>
                    <option value="custom">Kustom Tanggal</option>
                </x-select>
            </div>

            {{-- Tanggal Mulai --}}
            <div>
                <x-input-label value="Dari Tanggal" />
                <x-text-input type="date" wire:model.live="date_from" class="w-full mt-1 font-mono text-xs" />
            </div>

            {{-- Tanggal Selesai --}}
            <div>
                <x-input-label value="Sampai Tanggal" />
                <x-text-input type="date" wire:model.live="date_to" class="w-full mt-1 font-mono text-xs" />
            </div>

            {{-- Tahun Ajaran --}}
            <div>
                <x-input-label value="Tahun Ajaran" />
                <x-select wire:model.live="academic_year_id" class="w-full mt-1">
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">
                            {{ $year->name }} {{ $year->is_active ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </x-select>
            </div>
        </div>
    </div>

    {{-- Segmented Navigation Tabs --}}
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 no-scrollbar border-b border-slate-200 dark:border-slate-800">
        @php
            $tabs = [
                'executive' => ['label' => 'Ringkasan Eksekutif', 'icon' => 'layout-dashboard'],
                'finance' => ['label' => 'Keuangan & SPP', 'icon' => 'wallet'],
                'attendance' => ['label' => 'Presensi Harian', 'icon' => 'calendar-check'],
                'tahfidz' => ['label' => 'Tahfidz & Adab', 'icon' => 'book-marked'],
                'boarding' => ['label' => 'Keasramaan', 'icon' => 'home'],
                'admissions' => ['label' => 'PPDB Santri Baru', 'icon' => 'user-plus'],
            ];
        @endphp

        @foreach($tabs as $tabKey => $tabInfo)
            <button
                type="button"
                wire:click="setTab('{{ $tabKey }}')"
                class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-semibold whitespace-nowrap transition cursor-pointer {{ $activeTab === $tabKey ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}"
            >
                <i data-lucide="{{ $tabInfo['icon'] }}" class="w-4 h-4"></i>
                <span>{{ $tabInfo['label'] }}</span>
            </button>
        @endforeach
    </div>

    {{-- TAB 1: RINGKASAN EKSEKUTIF --}}
    @if($activeTab === 'executive')
        <div class="space-y-6">
            {{-- 4 Stat Cards Utama --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Penerimaan Kas (Periode Ini)</span>
                        <span class="rounded-xl bg-emerald-500/10 p-2 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                            <i data-lucide="wallet" class="w-4 h-4"></i>
                        </span>
                    </div>
                    <div class="mt-2 text-2xl font-black font-mono text-slate-900 dark:text-white">
                        Rp {{ number_format($financial['total_income'], 0, ',', '.') }}
                    </div>
                    <div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                        {{ $financial['total_transactions'] }} transaksi kuitansi tercatat
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Kehadiran Santri Rata-rata</span>
                        <span class="rounded-xl bg-sky-500/10 p-2 text-sky-600 dark:text-sky-400 border border-sky-500/20">
                            <i data-lucide="calendar-check" class="w-4 h-4"></i>
                        </span>
                    </div>
                    <div class="mt-2 text-2xl font-black font-mono text-slate-900 dark:text-white">
                        {{ $attendance['student_rate'] }}%
                    </div>
                    <div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                        {{ $attendance['student_present'] }} dari {{ $attendance['student_total'] }} absensi hadir
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Setoran Tahfidz Quran</span>
                        <span class="rounded-xl bg-emerald-500/10 p-2 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                            <i data-lucide="book-open" class="w-4 h-4"></i>
                        </span>
                    </div>
                    <div class="mt-2 text-2xl font-black font-mono text-slate-900 dark:text-white">
                        {{ $tahfidz['total_sessions'] }}
                    </div>
                    <div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                        {{ $tahfidz['ziyadah_count'] }} Ziyadah · {{ $tahfidz['murajaah_count'] }} Muraja'ah
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Hunian Asrama Santri</span>
                        <span class="rounded-xl bg-indigo-500/10 p-2 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                            <i data-lucide="home" class="w-4 h-4"></i>
                        </span>
                    </div>
                    <div class="mt-2 text-2xl font-black font-mono text-slate-900 dark:text-white">
                        {{ $boarding['occupancy_rate'] }}%
                    </div>
                    <div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                        {{ $boarding['total_occupied'] }} dari {{ $boarding['total_capacity'] }} ranjang terisi
                    </div>
                </div>
            </div>

            {{-- Ringkasan 2 Kolom --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Keuangan Overview --}}
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i data-lucide="pie-chart" class="w-4 h-4 text-emerald-500"></i>
                            Kolektibilitas Tagihan SPP
                        </h2>
                        <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 font-mono">
                            {{ $financial['collection_rate'] }}% Lunas
                        </span>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500 dark:text-slate-400">Total Tagihan Terbit:</span>
                            <span class="font-bold font-mono text-slate-900 dark:text-white">Rp {{ number_format($financial['total_billed'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500 dark:text-slate-400">Total Sudah Dibayar:</span>
                            <span class="font-bold font-mono text-emerald-600 dark:text-emerald-400">Rp {{ number_format($financial['total_paid'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500 dark:text-slate-400">Sisa Piutang / Tunggakan:</span>
                            <span class="font-bold font-mono text-rose-600 dark:text-rose-400">Rp {{ number_format($financial['total_remaining'], 0, ',', '.') }}</span>
                        </div>

                        {{-- Progress bar --}}
                        <div class="h-2 w-full rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full" style="width: {{ min(100, $financial['collection_rate']) }}%"></div>
                        </div>
                    </div>
                </div>

                {{-- Disiplin & Izin Asrama Overview --}}
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i data-lucide="shield-alert" class="w-4 h-4 text-amber-500"></i>
                            Kedisiplinan &amp; Perizinan Santri
                        </h2>
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                            Periode Aktif
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 p-3">
                            <span class="text-slate-500 dark:text-slate-400 block">Total Izin Keluar</span>
                            <span class="text-xl font-bold font-mono text-slate-900 dark:text-white mt-1 block">{{ $boarding['total_leaves'] }}</span>
                        </div>
                        <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 p-3">
                            <span class="text-slate-500 dark:text-slate-400 block">Sedang di Luar</span>
                            <span class="text-xl font-bold font-mono text-sky-600 dark:text-sky-400 mt-1 block">{{ $boarding['active_leaves'] }}</span>
                        </div>
                        <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 p-3">
                            <span class="text-slate-500 dark:text-slate-400 block">Terlambat Kembali</span>
                            <span class="text-xl font-bold font-mono text-rose-600 dark:text-rose-400 mt-1 block">{{ $boarding['overdue_leaves'] }}</span>
                        </div>
                        <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 p-3">
                            <span class="text-slate-500 dark:text-slate-400 block">Pelanggaran Adab</span>
                            <span class="text-xl font-bold font-mono text-amber-600 dark:text-amber-400 mt-1 block">{{ $tahfidz['violations_count'] }} ({{ $tahfidz['violation_points'] }} poin)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- TAB 2: KEUANGAN & SPP --}}
    @if($activeTab === 'finance')
        <div class="space-y-6">
            {{-- Statistik Keuangan --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Kas Masuk</span>
                    <div class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400 mt-1">
                        Rp {{ number_format($financial['total_income'], 0, ',', '.') }}
                    </div>
                    <span class="text-[11px] text-slate-400">{{ $financial['total_transactions'] }} transaksi kuitansi</span>
                </div>

                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Tagihan Sudah Dibayar</span>
                    <div class="text-2xl font-black font-mono text-sky-600 dark:text-sky-400 mt-1">
                        Rp {{ number_format($financial['total_paid'], 0, ',', '.') }}
                    </div>
                    <span class="text-[11px] text-slate-400">Dari total Rp {{ number_format($financial['total_billed'], 0, ',', '.') }}</span>
                </div>

                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Sisa Piutang / Tunggakan SPP</span>
                    <div class="text-2xl font-black font-mono text-rose-600 dark:text-rose-400 mt-1">
                        Rp {{ number_format($financial['total_remaining'], 0, ',', '.') }}
                    </div>
                    <span class="text-[11px] text-slate-400">Tingkat pelunasan: {{ $financial['collection_rate'] }}%</span>
                </div>
            </div>

            {{-- Rekap per Kategori Biaya --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden shadow-sm">
                <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="tag" class="w-4 h-4 text-emerald-500"></i>
                        Penerimaan Kas Berdasarkan Pos / Kategori Biaya
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="p-3.5">Nama Pos Biaya</th>
                                <th class="p-3.5">Kode</th>
                                <th class="p-3.5 text-right">Penerimaan Terkumpul</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($financial['income_by_category'] as $category)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                    <td class="p-3.5 font-bold text-slate-900 dark:text-white">{{ $category->name }}</td>
                                    <td class="p-3.5 font-mono text-slate-500 dark:text-slate-400">{{ $category->code }}</td>
                                    <td class="p-3.5 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                        Rp {{ number_format((float) ($category->collected_amount ?? 0), 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="p-8 text-center text-slate-400">Belum ada pos biaya yang tercatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 5 Tunggakan Terbesar --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden shadow-sm">
                <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-rose-500"></i>
                        Santri dengan Tunggakan Terbesar (Perlu Tindak Lanjut TU)
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="p-3.5">Nama Santri</th>
                                <th class="p-3.5">NIS</th>
                                <th class="p-3.5">Kelas / Rombel</th>
                                <th class="p-3.5 text-right">Nominal Tunggakan</th>
                                <th class="p-3.5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($financial['top_arrears'] as $arrearStudent)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                    <td class="p-3.5 font-bold text-slate-900 dark:text-white">{{ $arrearStudent->name }}</td>
                                    <td class="p-3.5 font-mono text-slate-500 dark:text-slate-400">{{ $arrearStudent->nis ?? '-' }}</td>
                                    <td class="p-3.5 text-slate-700 dark:text-slate-300">{{ $arrearStudent->schoolClass?->name ?? 'Belum ada kelas' }}</td>
                                    <td class="p-3.5 text-right font-mono font-bold text-rose-600 dark:text-rose-400">
                                        Rp {{ number_format((float) $arrearStudent->total_arrears, 0, ',', '.') }}
                                    </td>
                                    <td class="p-3.5 text-center">
                                        <a href="{{ route('finance.cashier') }}" class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 transition">
                                            Bayar di Kasir
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-slate-400">Alhamdulillah, tidak ada santri yang memiliki tunggakan!</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- TAB 3: PRESENSI HARIAN --}}
    @if($activeTab === 'attendance')
        <div class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
                    <span class="text-xs text-slate-500 dark:text-slate-400">Tingkat Kehadiran Siswa</span>
                    <div class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400 mt-1">
                        {{ $attendance['student_rate'] }}%
                    </div>
                    <span class="text-[11px] text-slate-400">{{ $attendance['student_present'] }} Hadir dari {{ $attendance['student_total'] }}</span>
                </div>

                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
                    <span class="text-xs text-slate-500 dark:text-slate-400">Sakit / Izin Santri</span>
                    <div class="text-2xl font-black font-mono text-sky-600 dark:text-sky-400 mt-1">
                        {{ $attendance['student_sick'] + $attendance['student_permit'] }}
                    </div>
                    <span class="text-[11px] text-slate-400">{{ $attendance['student_sick'] }} Sakit · {{ $attendance['student_permit'] }} Izin</span>
                </div>

                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
                    <span class="text-xs text-slate-500 dark:text-slate-400">Siswa Alpha (Tanpa Keterangan)</span>
                    <div class="text-2xl font-black font-mono text-rose-600 dark:text-rose-400 mt-1">
                        {{ $attendance['student_absent'] }}
                    </div>
                    <span class="text-[11px] text-slate-400">Perlu konfirmasi ke wali santri</span>
                </div>

                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
                    <span class="text-xs text-slate-500 dark:text-slate-400">Kehadiran Guru Pengampu</span>
                    <div class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400 mt-1">
                        {{ $attendance['teacher_rate'] }}%
                    </div>
                    <span class="text-[11px] text-slate-400">{{ $attendance['teacher_late'] }} Terlambat · {{ $attendance['teacher_absent'] }} Tidak Hadir</span>
                </div>
            </div>

            {{-- Rekap Kehadiran per Kelas dengan Visual Progress Bar --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden shadow-sm">
                <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="school" class="w-4 h-4 text-emerald-500"></i>
                        Rekapitulasi Kehadiran Siswa per Kelas / Rombel
                    </h2>
                    <a href="{{ route('attendance.reports') }}" wire:navigate class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">
                        Buka Rekap Bulanan Kelas &rarr;
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="p-3.5">Nama Kelas</th>
                                <th class="p-3.5">Tingkat</th>
                                <th class="p-3.5 text-center">Total Entri Presensi</th>
                                <th class="p-3.5 text-center">Jumlah Hadir</th>
                                <th class="p-3.5 w-48 text-center">Visual Ketercapaian</th>
                                <th class="p-3.5 text-right">% Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($attendance['classes_summary'] as $cls)
                                @php
                                    $rate = $cls->total_records > 0 ? round(($cls->present_records / $cls->total_records) * 100, 1) : 0;
                                @endphp
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                    <td class="p-3.5 font-bold text-slate-900 dark:text-white">{{ $cls->name }}</td>
                                    <td class="p-3.5 text-slate-500 dark:text-slate-400">Tingkat {{ $cls->level }}</td>
                                    <td class="p-3.5 text-center font-mono text-slate-700 dark:text-slate-300">{{ $cls->total_records }}</td>
                                    <td class="p-3.5 text-center font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ $cls->present_records }}</td>
                                    <td class="p-3.5">
                                        <div class="flex items-center gap-2">
                                            <div class="h-2 w-full rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                                <div class="h-full rounded-full {{ $rate >= 90 ? 'bg-emerald-500' : ($rate >= 75 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ min(100, $rate) }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-3.5 text-right font-mono font-bold {{ $rate >= 90 ? 'text-emerald-600 dark:text-emerald-400' : ($rate >= 75 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400') }}">
                                        {{ $rate }}%
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-slate-400">Belum ada kelas yang terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- TAB 4: TAHFIDZ & ADAB --}}
    @if($activeTab === 'tahfidz')
        <div class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Sesi Setoran Quran</span>
                    <div class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400 mt-1">
                        {{ $tahfidz['total_sessions'] }}
                    </div>
                    <span class="text-[11px] text-slate-400">Periode terpilih</span>
                </div>

                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Setoran Ziyadah (Baru)</span>
                    <div class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400 mt-1">
                        {{ $tahfidz['ziyadah_count'] }}
                    </div>
                    <span class="text-[11px] text-slate-400">Penambahan hafalan baru</span>
                </div>

                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Setoran Muraja'ah</span>
                    <div class="text-2xl font-black font-mono text-sky-600 dark:text-sky-400 mt-1">
                        {{ $tahfidz['murajaah_count'] }}
                    </div>
                    <span class="text-[11px] text-slate-400">Pengulangan hafalan mutqin</span>
                </div>

                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Poin Prestasi vs Pelanggaran</span>
                    <div class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400 mt-1">
                        +{{ $tahfidz['achievement_points'] }} / -{{ $tahfidz['violation_points'] }}
                    </div>
                    <span class="text-[11px] text-slate-400">{{ $tahfidz['achievements_count'] }} Prestasi · {{ $tahfidz['violations_count'] }} Pelanggaran</span>
                </div>
            </div>

            {{-- 5 Santri Paling Aktif Setoran --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden shadow-sm">
                <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="award" class="w-4 h-4 text-emerald-500"></i>
                        Santri Paling Aktif Setoran Tahfidz (Periode Ini)
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="p-3.5">Nama Santri</th>
                                <th class="p-3.5">Kelas</th>
                                <th class="p-3.5 text-center">Jumlah Setoran</th>
                                <th class="p-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($tahfidz['top_students'] as $topStudent)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                    <td class="p-3.5 font-bold text-slate-900 dark:text-white">{{ $topStudent->name }}</td>
                                    <td class="p-3.5 text-slate-500 dark:text-slate-400">{{ $topStudent->schoolClass?->name ?? '-' }}</td>
                                    <td class="p-3.5 text-center font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                        {{ $topStudent->tahfidz_sessions_count }} kali
                                    </td>
                                    <td class="p-3.5 text-right">
                                        <a href="{{ route('islamic.tahfidz') }}" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">
                                            Buka Buku Tahfidz &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-8 text-center text-slate-400">Belum ada setoran tahfidz pada rentang tanggal ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- TAB 5: KEASRAMAAN --}}
    @if($activeTab === 'boarding')
        <div class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Tingkat Hunian Asrama</span>
                    <div class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400 mt-1">
                        {{ $boarding['occupancy_rate'] }}%
                    </div>
                    <span class="text-[11px] text-slate-400">{{ $boarding['total_occupied'] }} dari {{ $boarding['total_capacity'] }} terisi</span>
                </div>

                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Kamar Asrama</span>
                    <div class="text-2xl font-black font-mono text-slate-900 dark:text-white mt-1">
                        {{ $boarding['total_rooms'] }} Kamar
                    </div>
                    <span class="text-[11px] text-slate-400">{{ $boarding['total_capacity'] - $boarding['total_occupied'] }} ranjang kosong</span>
                </div>

                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Santri Sedang Izin di Luar</span>
                    <div class="text-2xl font-black font-mono text-sky-600 dark:text-sky-400 mt-1">
                        {{ $boarding['active_leaves'] }} Santri
                    </div>
                    <span class="text-[11px] text-slate-400">Izin aktif gate pass</span>
                </div>

                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Terlambat Kembali ke Asrama</span>
                    <div class="text-2xl font-black font-mono text-rose-600 dark:text-rose-400 mt-1">
                        {{ $boarding['overdue_leaves'] }} Santri
                    </div>
                    <span class="text-[11px] text-slate-400">Melewati batas waktu tiket QR</span>
                </div>
            </div>

            {{-- Rekap Hunian Kamar --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden shadow-sm">
                <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="bed" class="w-4 h-4 text-indigo-500"></i>
                        Daftar Kamar Asrama &amp; Ketersediaan Ranjang
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="p-3.5">Nama Kamar</th>
                                <th class="p-3.5">Gedung</th>
                                <th class="p-3.5 text-center">Kapasitas</th>
                                <th class="p-3.5 text-center">Terisi</th>
                                <th class="p-3.5 text-center">Sisa Ranjang</th>
                                <th class="p-3.5 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($boarding['rooms_list'] as $room)
                                @php
                                    $remaining = $room->capacity - $room->occupied;
                                @endphp
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                    <td class="p-3.5 font-bold text-slate-900 dark:text-white">{{ $room->name }}</td>
                                    <td class="p-3.5 text-slate-500 dark:text-slate-400">{{ $room->building?->name ?? '-' }}</td>
                                    <td class="p-3.5 text-center font-mono">{{ $room->capacity }}</td>
                                    <td class="p-3.5 text-center font-mono font-bold text-slate-900 dark:text-white">{{ $room->occupied }}</td>
                                    <td class="p-3.5 text-center font-mono font-bold {{ $remaining > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">
                                        {{ $remaining }}
                                    </td>
                                    <td class="p-3.5 text-right">
                                        @if($remaining <= 0)
                                            <span class="rounded-full bg-rose-50 dark:bg-rose-950/40 px-2.5 py-0.5 text-[10px] font-bold text-rose-700 dark:text-rose-400">Penuh</span>
                                        @else
                                            <span class="rounded-full bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-0.5 text-[10px] font-bold text-emerald-700 dark:text-emerald-400">Tersedia {{ $remaining }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-slate-400">Belum ada kamar asrama yang terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- TAB 6: PPDB ONLINE --}}
    @if($activeTab === 'admissions')
        <div class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Pendaftar Calon Santri</span>
                    <div class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400 mt-1">
                        {{ $admissions['total_candidates'] }}
                    </div>
                    <span class="text-[11px] text-slate-400">{{ $admissions['male_candidates'] }} Ikhwan · {{ $admissions['female_candidates'] }} Akhwat</span>
                </div>

                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Lulus Seleksi Masuk</span>
                    <div class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400 mt-1">
                        {{ $admissions['passed'] }}
                    </div>
                    <span class="text-[11px] text-slate-400">Siap daftar ulang</span>
                </div>

                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Sudah Jadi Siswa Aktif</span>
                    <div class="text-2xl font-black font-mono text-sky-600 dark:text-sky-400 mt-1">
                        {{ $admissions['converted'] }}
                    </div>
                    <span class="text-[11px] text-slate-400">Telah terkonversi ke master siswa</span>
                </div>

                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Menunggu Verifikasi &amp; Tes</span>
                    <div class="text-2xl font-black font-mono text-amber-600 dark:text-amber-400 mt-1">
                        {{ $admissions['registered'] + $admissions['verified'] }}
                    </div>
                    <span class="text-[11px] text-slate-400">{{ $admissions['registered'] }} Berkas Baru · {{ $admissions['verified'] }} Terverifikasi</span>
                </div>
            </div>

            {{-- Rekap per Gelombang --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden shadow-sm">
                <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="calendar" class="w-4 h-4 text-emerald-500"></i>
                        Rekapitulasi Penerimaan per Gelombang PPDB
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="p-3.5">Nama Gelombang</th>
                                <th class="p-3.5">Biaya Formulir</th>
                                <th class="p-3.5 text-center">Pendaftar Masuk</th>
                                <th class="p-3.5 text-center">Lulus Seleksi</th>
                                <th class="p-3.5 text-center">Terkonversi Santri</th>
                                <th class="p-3.5 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($admissions['batches'] as $batch)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                    <td class="p-3.5 font-bold text-slate-900 dark:text-white">{{ $batch->name }}</td>
                                    <td class="p-3.5 font-mono text-slate-500 dark:text-slate-400">Rp {{ number_format((float) $batch->registration_fee, 0, ',', '.') }}</td>
                                    <td class="p-3.5 text-center font-mono font-bold text-slate-900 dark:text-white">{{ $batch->candidates_count }}</td>
                                    <td class="p-3.5 text-center font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ $batch->passed_count }}</td>
                                    <td class="p-3.5 text-center font-mono font-bold text-sky-600 dark:text-sky-400">{{ $batch->converted_count }}</td>
                                    <td class="p-3.5 text-right">
                                        @if($batch->is_active)
                                            <span class="rounded-full bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-0.5 text-[10px] font-bold text-emerald-700 dark:text-emerald-400">Aktif</span>
                                        @else
                                            <span class="rounded-full bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 text-[10px] font-medium text-slate-500">Ditutup</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-slate-400">Belum ada gelombang PPDB yang dibuat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
