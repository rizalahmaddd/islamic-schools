<div class="space-y-5 sm:space-y-6">
    {{-- Header & Command Bar --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 sm:p-5 shadow-sm space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-2.5">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                    <i data-lucide="file-bar-chart" class="w-5 h-5"></i>
                </span>
                <div>
                    <h1 class="text-base sm:text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span>Rekapitulasi Presensi Bulanan Santri</span>
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Analisis tingkat kehadiran rombel, akumulasi izin/sakit/alpa, dan cetak dokumen resmi.</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('attendance.students') }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition border border-slate-200 dark:border-slate-700">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Presensi Harian</span>
                </a>

                @if ($selectedClass)
                    <a
                        href="{{ route('attendance.reports.print', ['schoolClass' => $classId, 'month' => $month, 'year' => $year]) }}"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white hover:bg-slate-50 dark:bg-slate-800 dark:hover:bg-slate-700 transition border border-slate-200 dark:border-slate-700"
                    >
                        <i data-lucide="printer" class="w-4 h-4 text-emerald-500"></i>
                        <span>Cetak Dokumen</span>
                    </a>
                @endif

                <x-table.export-button action="export" label="Ekspor Laporan" />
            </div>
        </div>

        {{-- Filter Toolbar --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3.5 pt-3 border-t border-slate-100 dark:border-slate-800">
            <div>
                <x-input-label for="classId" value="Pilih Kelas / Rombel" />
                <x-select wire:model.live="classId" id="classId" class="w-full mt-1">
                    <option value="">Pilih Rombongan Belajar</option>
                    @foreach ($schoolClasses as $schoolClass)
                        <option value="{{ $schoolClass->id }}">{{ $schoolClass->name }} ({{ $schoolClass->academicYear?->name ?? '-' }})</option>
                    @endforeach
                </x-select>
            </div>

            <div>
                <x-input-label for="month" value="Bulan" />
                <x-select wire:model.live="month" id="month" class="w-full mt-1">
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}">{{ \Carbon\Carbon::createFromDate(null, $m, 1)->translatedFormat('F') }}</option>
                    @endforeach
                </x-select>
            </div>

            <div>
                <x-input-label for="year" value="Tahun" />
                <x-select wire:model.live="year" id="year" class="w-full mt-1">
                    @foreach (range(now()->year - 2, now()->year + 1) as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </x-select>
            </div>

            <div>
                <x-input-label for="search" value="Cari Santri" />
                <x-search-input wire:model.live.debounce.300ms="search" id="search" placeholder="Nama atau NIS..." class="mt-1" />
            </div>
        </div>
    </div>

    {{-- KPI Cards Ringkasan Rombel --}}
    @if ($selectedClass && $recap->isNotEmpty())
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 sm:gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Hari Pertemuan</span>
                <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1 font-mono">
                    {{ $totalMeetings }} <span class="text-xs font-normal text-slate-400">hari</span>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Presensi aktif tercatat</p>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-emerald-200 dark:border-emerald-500/20 p-4 shadow-sm">
                <span class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Rata-rata Kehadiran</span>
                <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1 font-mono">
                    {{ $overallRate }}%
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Akumulasi kehadiran rombel</p>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-amber-200 dark:border-amber-500/20 p-4 shadow-sm">
                <span class="text-xs text-amber-600 dark:text-amber-400 font-medium">Total Sakit (S)</span>
                <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1 font-mono">
                    {{ $recap->sum('sick') }} <span class="text-xs font-normal text-slate-400">kali</span>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Dengan surat keterangan</p>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-sky-200 dark:border-sky-500/20 p-4 shadow-sm">
                <span class="text-xs text-sky-600 dark:text-sky-400 font-medium">Total Izin (I)</span>
                <div class="text-2xl font-bold text-sky-600 dark:text-sky-400 mt-1 font-mono">
                    {{ $recap->sum('permit') }} <span class="text-xs font-normal text-slate-400">kali</span>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Izin resmi disetujui</p>
            </div>

            <div class="col-span-2 sm:col-span-1 bg-white dark:bg-slate-900 rounded-2xl border border-rose-200 dark:border-rose-500/20 p-4 shadow-sm">
                <span class="text-xs text-rose-600 dark:text-rose-400 font-medium">Total Alpa (A)</span>
                <div class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-1 font-mono">
                    {{ $recap->sum('absent') }} <span class="text-xs font-normal text-slate-400">kali</span>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Tanpa keterangan valid</p>
            </div>
        </div>
    @endif

    {{-- Tabel Rekapitulasi Presensi Santri --}}
    @if (! $selectedClass)
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <x-empty-state
                icon="layout-grid"
                title="Pilih Kelas Terlebih Dahulu"
                description="Pilih rombongan belajar di atas untuk menampilkan rekapitulasi presensi bulanan."
            />
        </div>
    @elseif ($recap->isEmpty())
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <x-empty-state
                icon="file-question"
                title="Tidak Ada Data Presensi"
                description="Belum ada catatan presensi yang cocok dengan filter kelas dan bulan yang dipilih."
            />
        </div>
    @else
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                        Daftar Kehadiran: {{ $selectedClass->name }}
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Wali Kelas: {{ $selectedClass->homeroomTeacher?->name ?? 'Belum ditentukan' }} &bull; Total {{ $recap->count() }} Santri
                    </p>
                </div>

                <div class="flex items-center gap-2 text-xs font-mono">
                    <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> H: Hadir</span>
                    <span class="inline-flex items-center gap-1 text-amber-600 dark:text-amber-400"><span class="w-2 h-2 rounded-full bg-amber-500"></span> S: Sakit</span>
                    <span class="inline-flex items-center gap-1 text-sky-600 dark:text-sky-400"><span class="w-2 h-2 rounded-full bg-sky-500"></span> I: Izin</span>
                    <span class="inline-flex items-center gap-1 text-rose-600 dark:text-rose-400"><span class="w-2 h-2 rounded-full bg-rose-500"></span> A: Alpa</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left border-collapse">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-3.5 py-3 w-12 text-center">No</th>
                            <th class="px-3.5 py-3">NIS</th>
                            <th class="px-3.5 py-3">Nama Santri / Siswa</th>
                            <th class="px-3.5 py-3 text-center">L/P</th>
                            <th class="px-3.5 py-3 text-center text-emerald-600 dark:text-emerald-400">H</th>
                            <th class="px-3.5 py-3 text-center text-amber-600 dark:text-amber-400">S</th>
                            <th class="px-3.5 py-3 text-center text-sky-600 dark:text-sky-400">I</th>
                            <th class="px-3.5 py-3 text-center text-rose-600 dark:text-rose-400">A</th>
                            <th class="px-3.5 py-3 text-center">Total</th>
                            <th class="px-3.5 py-3 w-36 text-center">Visual</th>
                            <th class="px-3.5 py-3 text-right">% Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($recap as $index => $row)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <td class="px-3.5 py-3 text-center text-slate-400 font-mono">{{ $index + 1 }}</td>
                                <td class="px-3.5 py-3 text-slate-500 dark:text-slate-400 font-mono">{{ $row['student']->nis }}</td>
                                <td class="px-3.5 py-3 font-semibold text-slate-900 dark:text-slate-100">
                                    <a href="{{ route('master-data.students.show', $row['student']) }}" wire:navigate class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">
                                        {{ $row['student']->name }}
                                    </a>
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded text-[10px] font-bold {{ $row['student']->gender === 'L' ? 'bg-sky-50 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-500/20' : 'bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-500/20' }}">
                                        {{ $row['student']->gender }}
                                    </span>
                                </td>
                                <td class="px-3.5 py-3 text-center font-mono font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50/50 dark:bg-emerald-500/5">{{ $row['present'] }}</td>
                                <td class="px-3.5 py-3 text-center font-mono font-semibold text-amber-600 dark:text-amber-400 bg-amber-50/50 dark:bg-amber-500/5">{{ $row['sick'] }}</td>
                                <td class="px-3.5 py-3 text-center font-mono font-semibold text-sky-600 dark:text-sky-400 bg-sky-50/50 dark:bg-sky-500/5">{{ $row['permit'] }}</td>
                                <td class="px-3.5 py-3 text-center font-mono font-bold text-rose-600 dark:text-rose-400 bg-rose-50/50 dark:bg-rose-500/5">{{ $row['absent'] }}</td>
                                <td class="px-3.5 py-3 text-center font-mono text-slate-700 dark:text-slate-300 font-medium">{{ $row['total_recorded'] }}</td>
                                <td class="px-3.5 py-3">
                                    <div class="h-2 w-full rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                        <div class="h-full rounded-full {{ $row['rate'] >= 90 ? 'bg-emerald-500' : ($row['rate'] >= 75 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ min(100, $row['rate']) }}%"></div>
                                    </div>
                                </td>
                                <td class="px-3.5 py-3 text-right">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-mono font-bold {{ $row['rate'] >= 90 ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20' : ($row['rate'] >= 75 ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20' : 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-500/20') }}">
                                        {{ $row['rate'] }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    {{-- Baris Akumulasi Total Rombel --}}
                    <tfoot class="bg-slate-50 dark:bg-slate-950 font-bold border-t-2 border-slate-200 dark:border-slate-800">
                        <tr>
                            <td colspan="4" class="px-3.5 py-3 text-slate-700 dark:text-slate-300">
                                Total Akumulasi Kelas ({{ $recap->count() }} Santri):
                            </td>
                            <td class="px-3.5 py-3 text-center font-mono text-emerald-600 dark:text-emerald-400 bg-emerald-50/50 dark:bg-emerald-500/10">
                                {{ $recap->sum('present') }}
                            </td>
                            <td class="px-3.5 py-3 text-center font-mono text-amber-600 dark:text-amber-400 bg-amber-50/50 dark:bg-amber-500/10">
                                {{ $recap->sum('sick') }}
                            </td>
                            <td class="px-3.5 py-3 text-center font-mono text-sky-600 dark:text-sky-400 bg-sky-50/50 dark:bg-sky-500/10">
                                {{ $recap->sum('permit') }}
                            </td>
                            <td class="px-3.5 py-3 text-center font-mono text-rose-600 dark:text-rose-400 bg-rose-50/50 dark:bg-rose-500/10">
                                {{ $recap->sum('absent') }}
                            </td>
                            <td class="px-3.5 py-3 text-center font-mono text-slate-900 dark:text-white">
                                {{ $recap->sum('total_recorded') }}
                            </td>
                            <td></td>
                            <td class="px-3.5 py-3 text-right font-mono text-sm text-emerald-600 dark:text-emerald-400">
                                {{ $overallRate }}%
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif
</div>
