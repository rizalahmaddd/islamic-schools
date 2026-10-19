<div class="space-y-6">
    @if ($students->isEmpty())
        <div class="py-12">
            <x-empty-state 
                icon="graduation-cap" 
                title="Belum Ada Data Santri Terhubung" 
                description="Akun wali murid Anda belum terhubung dengan data santri aktif. Pastikan nomor WhatsApp Anda sudah didaftarkan pada data santri di bagian Tata Usaha sekolah." 
            />
        </div>
    @else
        {{-- TOP CARD: Child Switcher & Student Information --}}
        <div class="bg-slate-900/90 rounded-2xl border border-slate-800/80 p-4 sm:p-6 shadow-sm backdrop-blur-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                {{-- Kiri: Identitas Santri --}}
                <div class="flex items-center gap-3.5 sm:gap-4 min-w-0">
                    <div class="relative shrink-0">
                        @if ($selectedStudent?->photo_path)
                            <img src="{{ asset('storage/' . $selectedStudent->photo_path) }}" alt="{{ $selectedStudent->name }}" class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl object-cover ring-2 ring-emerald-500/20">
                        @else
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xl select-none">
                                {{ strtoupper(substr($selectedStudent?->name ?? 'S', 0, 2)) }}
                            </div>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-lg sm:text-xl font-bold text-slate-100 truncate tracking-tight">{{ $selectedStudent?->name }}</h1>
                            @if ($selectedStudent)
                                <x-badge :color="$selectedStudent->statusBadgeColor()">{{ $selectedStudent->statusLabel() }}</x-badge>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-y-1 gap-x-3 text-xs text-slate-400 mt-1 font-medium">
                            <span>NISN: <strong class="text-slate-300 font-mono">{{ $selectedStudent?->nisn ?? $selectedStudent?->nis ?? '-' }}</strong></span>
                            <span>&bull;</span>
                            <span>Kelas: <strong class="text-emerald-400 font-semibold">{{ $selectedStudent?->currentClass()?->name ?? 'Belum ada kelas' }}</strong></span>
                            @if ($selectedStudent?->currentClass()?->homeroomTeacher)
                                <span class="hidden sm:inline">&bull;</span>
                                <span class="hidden sm:inline">Wali Kelas: <strong class="text-slate-300">{{ $selectedStudent->currentClass()->homeroomTeacher->name }}</strong></span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Kanan: Child Switcher jika memiliki lebih dari 1 anak --}}
                @if ($students->count() > 1)
                    <div class="shrink-0 w-full md:w-72 pt-3 md:pt-0 border-t md:border-t-0 border-slate-800/80">
                        <label for="child-switcher" class="block text-xs font-semibold text-slate-400 mb-1.5 flex items-center gap-1.5">
                            <i data-lucide="users-round" class="w-3.5 h-3.5 text-emerald-400"></i>
                            <span>Pilih Santri / Anak:</span>
                        </label>
                        <x-select id="child-switcher" wire:model.live="selectedStudentId">
                            @foreach ($students as $student)
                                <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->currentClass()?->name ?? 'Kelas -' }})</option>
                            @endforeach
                        </x-select>
                    </div>
                @endif
            </div>
        </div>

        {{-- SEGMENTED NAVIGATION TABS --}}
        <div class="overflow-x-auto overscroll-x-contain pb-1">
            <x-segmented class="inline-flex min-w-full sm:min-w-0">
                <x-tab-button wire:click="setTab('summary')" :active="$activeTab === 'summary'" icon="layout-dashboard">
                    Ringkasan Hari Ini
                </x-tab-button>
                <x-tab-button wire:click="setTab('attendance')" :active="$activeTab === 'attendance'" icon="calendar-check">
                    Presensi &amp; Kehadiran
                </x-tab-button>
                <x-tab-button wire:click="setTab('tahfidz')" :active="$activeTab === 'tahfidz'" icon="book-open">
                    Tahfidz &amp; E-Rapor
                </x-tab-button>
                <x-tab-button wire:click="setTab('finance')" :active="$activeTab === 'finance'" icon="wallet" :count="$unpaidBills->count() > 0 ? $unpaidBills->count() : null">
                    Tagihan SPP
                </x-tab-button>
                <x-tab-button wire:click="setTab('cashless')" :active="$activeTab === 'cashless'" icon="credit-card">
                    Kantin &amp; Saldo RFID
                </x-tab-button>
            </x-segmented>
        </div>

        {{-- TAB 1: RINGKASAN HARI INI --}}
        @if ($activeTab === 'summary')
            <div class="space-y-6">
                {{-- Quick Metric Cards --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- 1. Presensi Hari Ini --}}
                    <div class="bg-slate-900/80 rounded-xl border border-slate-800/80 p-4 shadow-xs">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-slate-400">Presensi Hari Ini</span>
                            <div class="p-2 rounded-lg bg-emerald-500/10 text-emerald-400">
                                <i data-lucide="calendar-check" class="w-4 h-4"></i>
                            </div>
                        </div>
                        <div class="mt-1">
                            @if ($todayAttendance)
                                @php
                                    $attColor = match($todayAttendance->status) {
                                        'present' => 'emerald',
                                        'sick' => 'amber',
                                        'permit' => 'sky',
                                        'absent' => 'rose',
                                        default => 'slate',
                                    };
                                    $attLabel = match($todayAttendance->status) {
                                        'present' => 'Hadir',
                                        'sick' => 'Sakit',
                                        'permit' => 'Izin',
                                        'absent' => 'Alpa / Tanpa Keterangan',
                                        default => ucfirst((string) $todayAttendance->status),
                                    };
                                @endphp
                                <x-badge :color="$attColor">{{ $attLabel }}</x-badge>
                                @if ($todayAttendance->check_in_time)
                                    <p class="text-[11px] text-slate-400 mt-2">
                                        Pukul: <strong class="text-slate-200 font-mono">{{ \Carbon\Carbon::parse($todayAttendance->check_in_time)->format('H:i') }} WIB</strong>
                                    </p>
                                @elseif ($todayAttendance->notes)
                                    <p class="text-[11px] text-slate-400 mt-2">Catatan: {{ $todayAttendance->notes }}</p>
                                @endif
                            @else
                                <x-badge color="slate">Belum Dipresensi</x-badge>
                                <p class="text-[11px] text-slate-400 mt-2">Presensi kelas belum diinput guru hari ini.</p>
                            @endif
                        </div>
                    </div>

                    {{-- 2. Hafalan Terakhir --}}
                    <div class="bg-slate-900/80 rounded-xl border border-slate-800/80 p-4 shadow-xs">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-slate-400">Setoran Tahfidz Terakhir</span>
                            <div class="p-2 rounded-lg bg-emerald-500/10 text-emerald-400">
                                <i data-lucide="book-marked" class="w-4 h-4"></i>
                            </div>
                        </div>
                        <div class="mt-1">
                            @if ($latestTahfidz)
                                <div class="text-sm font-bold text-slate-100 truncate">
                                    {{ $latestTahfidz->surah?->name_latin ?? 'Al-Qur\'an' }} (Ayat {{ $latestTahfidz->verse_start }}-{{ $latestTahfidz->verse_end }})
                                </div>
                                <div class="flex items-center gap-2 mt-2">
                                    <x-badge color="emerald">Juz {{ $latestTahfidz->juz }}</x-badge>
                                    <span class="text-xs text-slate-400 font-medium">Nilai: <strong class="text-emerald-400">{{ $latestTahfidz->grade }}</strong></span>
                                </div>
                            @else
                                <span class="text-sm font-semibold text-slate-400">Belum ada setoran</span>
                                <p class="text-[11px] text-slate-500 mt-2">Menunggu jadwal halaqah berikutnya.</p>
                            @endif
                        </div>
                    </div>

                    {{-- 3. Mutaba'ah Shalat Hari Ini --}}
                    <div class="bg-slate-900/80 rounded-xl border border-slate-800/80 p-4 shadow-xs">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-slate-400">Mutaba'ah Shalat Hari Ini</span>
                            <div class="p-2 rounded-lg bg-emerald-500/10 text-emerald-400">
                                <i data-lucide="calendar-check-2" class="w-4 h-4"></i>
                            </div>
                        </div>
                        <div class="mt-1">
                            @if ($todayWorship)
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    @foreach (['fajr' => 'Subuh', 'dhuhr' => 'Dzuhur', 'asr' => 'Ashar', 'maghrib' => 'Maghrib', 'isha' => 'Isya'] as $key => $prayerName)
                                        @php $prayed = (bool) $todayWorship->{$key}; @endphp
                                        <span @class([
                                            'px-2 py-0.5 rounded text-[10px] font-semibold flex items-center gap-1',
                                            'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' => $prayed,
                                            'bg-slate-800/60 text-slate-500 border border-slate-700/40' => ! $prayed,
                                        ])>
                                            <i data-lucide="{{ $prayed ? 'check' : 'minus' }}" class="w-2.5 h-2.5"></i>
                                            <span>{{ $prayerName }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-xs text-slate-400 font-medium">Belum diisi musyrif</span>
                                <p class="text-[11px] text-slate-500 mt-2">Pencatatan dilakukan tiap waktu shalat.</p>
                            @endif
                        </div>
                    </div>

                    {{-- 4. Poin Adab Santri --}}
                    <div class="bg-slate-900/80 rounded-xl border border-slate-800/80 p-4 shadow-xs">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-slate-400">Poin Adab &amp; Disiplin</span>
                            <div class="p-2 rounded-lg bg-emerald-500/10 text-emerald-400">
                                <i data-lucide="shield-check" class="w-4 h-4"></i>
                            </div>
                        </div>
                        <div class="mt-1 flex items-center justify-between">
                            <div>
                                <span class="text-xs text-slate-400">Pelanggaran:</span>
                                <div class="text-base font-bold {{ ($selectedStudent?->totalViolationPoints() ?? 0) > 0 ? 'text-rose-400' : 'text-slate-100' }}">
                                    {{ $selectedStudent?->totalViolationPoints() ?? 0 }} Poin
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-slate-400">Prestasi:</span>
                                <div class="text-base font-bold text-emerald-400">
                                    +{{ $selectedStudent?->totalMeritPoints() ?? 0 }} Poin
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Status Tagihan SPP Callout --}}
                <div @class([
                    'rounded-2xl border p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs',
                    'bg-rose-500/10 border-rose-500/25' => $totalOutstanding > 0,
                    'bg-emerald-500/10 border-emerald-500/25' => $totalOutstanding == 0,
                ])>
                    <div class="flex items-center gap-3.5">
                        <div @class([
                            'w-11 h-11 rounded-xl flex items-center justify-center shrink-0',
                            'bg-rose-500/20 text-rose-400' => $totalOutstanding > 0,
                            'bg-emerald-500/20 text-emerald-400' => $totalOutstanding == 0,
                        ])>
                            <i data-lucide="{{ $totalOutstanding > 0 ? 'alert-circle' : 'check-circle-2' }}" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <div class="text-xs font-semibold {{ $totalOutstanding > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                                {{ $totalOutstanding > 0 ? 'Terdapat Tagihan Menunggu Pembayaran' : 'Tagihan Santri Lunas' }}
                            </div>
                            <div class="text-lg sm:text-xl font-mono font-bold text-slate-100 mt-0.5">
                                Rp {{ number_format($totalOutstanding, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <x-primary-button wire:click="setTab('finance')" size="sm" class="w-full sm:w-auto">
                            <i data-lucide="receipt" class="w-4 h-4"></i>
                            <span>Lihat Rincian Tagihan</span>
                        </x-primary-button>
                    </div>
                </div>
            </div>
        @endif

        {{-- TAB 2: PRESENSI SANTRI --}}
        @if ($activeTab === 'attendance')
            <div class="space-y-6">
                {{-- Filter & Recap Banner --}}
                <div class="bg-slate-900/80 rounded-2xl border border-slate-800/80 p-4 sm:p-5 shadow-xs">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-sm font-bold text-slate-100">Rekap Kehadiran Santri</h2>
                            <p class="text-xs text-slate-400 mt-0.5">Pilih periode bulan dan tahun untuk melihat catatan presensi harian.</p>
                        </div>

                        <div class="flex items-center gap-2">
                            <x-select id="attendance-month" wire:model.live="attendanceMonth" class="text-xs">
                                @foreach ($months as $mNum => $mName)
                                    <option value="{{ $mNum }}">{{ $mName }}</option>
                                @endforeach
                            </x-select>

                            <x-select id="attendance-year" wire:model.live="attendanceYear" class="text-xs">
                                @for ($y = now()->year; $y >= now()->year - 2; $y--)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </x-select>
                        </div>
                    </div>

                    {{-- Stats Grid --}}
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mt-4 pt-4 border-t border-slate-800/80">
                        <div class="bg-slate-950/60 p-3 rounded-xl border border-slate-800/60 text-center">
                            <span class="text-[11px] text-slate-400 font-medium">Hadir</span>
                            <div class="text-lg font-bold text-emerald-400 font-mono mt-0.5">{{ $attendanceStats['present'] }}</div>
                        </div>
                        <div class="bg-slate-950/60 p-3 rounded-xl border border-slate-800/60 text-center">
                            <span class="text-[11px] text-slate-400 font-medium">Sakit</span>
                            <div class="text-lg font-bold text-amber-400 font-mono mt-0.5">{{ $attendanceStats['sick'] }}</div>
                        </div>
                        <div class="bg-slate-950/60 p-3 rounded-xl border border-slate-800/60 text-center">
                            <span class="text-[11px] text-slate-400 font-medium">Izin</span>
                            <div class="text-lg font-bold text-sky-400 font-mono mt-0.5">{{ $attendanceStats['permit'] }}</div>
                        </div>
                        <div class="bg-slate-950/60 p-3 rounded-xl border border-slate-800/60 text-center">
                            <span class="text-[11px] text-slate-400 font-medium">Alpa</span>
                            <div class="text-lg font-bold text-rose-400 font-mono mt-0.5">{{ $attendanceStats['absent'] }}</div>
                        </div>
                        <div class="col-span-2 sm:col-span-1 bg-slate-950/60 p-3 rounded-xl border border-slate-800/60 text-center">
                            <span class="text-[11px] text-slate-400 font-medium">Persentase</span>
                            <div class="text-lg font-bold text-emerald-400 font-mono mt-0.5">{{ $attendanceStats['rate'] }}%</div>
                        </div>
                    </div>
                </div>

                {{-- Tabel Catatan Presensi --}}
                <x-table>
                    <x-slot:header>
                        <tr>
                            <x-table.th>Tanggal</x-table.th>
                            <x-table.th>Waktu</x-table.th>
                            <x-table.th>Status</x-table.th>
                            <x-table.th>Keterangan</x-table.th>
                        </tr>
                    </x-slot:header>

                    @forelse ($monthlyAttendances as $att)
                        <x-table.tr>
                            <x-table.td class="font-medium text-slate-200">
                                {{ \Carbon\Carbon::parse($att->date)->translatedFormat('l, d F Y') }}
                            </x-table.td>
                            <x-table.td class="font-mono text-slate-300">
                                {{ $att->check_in_time ? \Carbon\Carbon::parse($att->check_in_time)->format('H:i') : '-' }} WIB
                            </x-table.td>
                            <x-table.td>
                                @php
                                    $c = match($att->status) {
                                        'present' => 'emerald',
                                        'sick' => 'amber',
                                        'permit' => 'sky',
                                        'absent' => 'rose',
                                        default => 'slate',
                                    };
                                    $lbl = match($att->status) {
                                        'present' => 'Hadir',
                                        'sick' => 'Sakit',
                                        'permit' => 'Izin',
                                        'absent' => 'Alpa',
                                        default => ucfirst((string) $att->status),
                                    };
                                @endphp
                                <x-badge :color="$c">{{ $lbl }}</x-badge>
                            </x-table.td>
                            <x-table.td class="text-slate-400">
                                {{ $att->notes ?: '-' }}
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8">
                                <x-empty-state 
                                    icon="calendar-x" 
                                    title="Tidak Ada Data Presensi" 
                                    description="Belum ada riwayat absensi tercatat pada bulan yang dipilih." 
                                />
                            </td>
                        </tr>
                    @endforelse
                </x-table>
            </div>
        @endif

        {{-- TAB 3: TAHFIDZ & E-RAPOR --}}
        @if ($activeTab === 'tahfidz')
            <div class="space-y-6">
                {{-- E-Rapor Download Banner --}}
                <div class="bg-gradient-to-r from-emerald-950/60 to-slate-900/90 rounded-2xl border border-emerald-500/30 p-4 sm:p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center shrink-0">
                            <i data-lucide="file-text" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-white tracking-tight">E-Rapor Digital Santri</h2>
                            <p class="text-xs text-slate-300 mt-0.5">Unduh atau cetak dokumen resmi E-Rapor Kurikulum Merdeka beserta Lampiran Rapor Tahfidz.</p>
                        </div>
                    </div>

                    <a href="{{ route('portal.report-cards.print', ['student' => $selectedStudent->id]) }}" target="_blank" class="inline-flex items-center justify-center gap-2 min-h-[42px] px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs shadow-md shadow-emerald-600/20 transition cursor-pointer">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        <span>Cetak / Unduh E-Rapor</span>
                    </a>
                </div>

                {{-- Visualisasi Grid 30 Juz Al-Qur'an --}}
                <div class="bg-slate-900/80 rounded-2xl border border-slate-800/80 p-4 sm:p-6 shadow-xs">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                        <div>
                            <h2 class="text-sm font-bold text-slate-100 flex items-center gap-2">
                                <i data-lucide="book-open" class="w-4 h-4 text-emerald-400"></i>
                                <span>Peta Capaian 30 Juz Al-Qur'an</span>
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">Status hafalan ziyadah dan muraja'ah santri pada tiap juz Al-Qur'an.</p>
                        </div>

                        {{-- Legend --}}
                        <div class="flex items-center gap-3 text-xs text-slate-400">
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded bg-emerald-500"></span>
                                <span>Mutqin / Lulus</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded bg-sky-500"></span>
                                <span>Sedang Ditempuh</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded bg-slate-800 border border-slate-700"></span>
                                <span>Belum</span>
                            </div>
                        </div>
                    </div>

                    {{-- 30 Juz Blocks Grid --}}
                    <div class="grid grid-cols-5 sm:grid-cols-6 md:grid-cols-10 gap-2.5 pt-2">
                        @foreach ($juzProgress as $item)
                            <div @class([
                                'p-2.5 rounded-xl border text-center transition select-none flex flex-col items-center justify-center',
                                'bg-emerald-500/15 border-emerald-500/40 text-emerald-300' => $item['status'] === 'mutqin',
                                'bg-sky-500/15 border-sky-500/40 text-sky-300' => $item['status'] === 'in_progress',
                                'bg-slate-950/60 border-slate-800 text-slate-500' => $item['status'] === 'unstarted',
                            ])>
                                <span class="text-[10px] font-semibold uppercase tracking-wider block">Juz</span>
                                <span class="text-base font-bold font-mono">{{ $item['juz'] }}</span>
                                <div class="mt-1">
                                    @if ($item['status'] === 'mutqin')
                                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-400"></i>
                                    @elseif ($item['status'] === 'in_progress')
                                        <i data-lucide="clock" class="w-3.5 h-3.5 text-sky-400"></i>
                                    @else
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-700 inline-block"></span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Riwayat Setoran Tahfidz Terakhir --}}
                <div class="bg-slate-900/80 rounded-2xl border border-slate-800/80 p-4 sm:p-6 shadow-xs">
                    <h3 class="text-sm font-bold text-slate-100 mb-3 flex items-center gap-2">
                        <i data-lucide="history" class="w-4 h-4 text-emerald-400"></i>
                        <span>Riwayat Setoran Hafalan Terbaru</span>
                    </h3>

                    @php
                        $recentSessions = \App\Models\TahfidzSession::with('teacher')
                            ->where('student_id', $selectedStudent->id)
                            ->latest('recorded_at')
                            ->take(8)
                            ->get();
                    @endphp

                    <x-table>
                        <x-slot:header>
                            <tr>
                                <x-table.th>Tanggal</x-table.th>
                                <x-table.th>Jenis</x-table.th>
                                <x-table.th>Surat &amp; Ayat</x-table.th>
                                <x-table.th>Juz</x-table.th>
                                <x-table.th>Nilai</x-table.th>
                                <x-table.th>Ustadz / Pembimbing</x-table.th>
                            </tr>
                        </x-slot:header>

                        @forelse ($recentSessions as $session)
                            <x-table.tr>
                                <x-table.td class="font-medium text-slate-200">
                                    {{ \Carbon\Carbon::parse($session->recorded_at)->translatedFormat('d M Y') }}
                                </x-table.td>
                                <x-table.td>
                                    <x-badge :color="$session->session_type === 'ziyadah' ? 'emerald' : 'sky'">
                                        {{ ucfirst((string) $session->session_type) }}
                                    </x-badge>
                                </x-table.td>
                                <x-table.td class="font-medium text-slate-100">
                                    {{ $session->surah?->name_latin ?? '-' }} (Ayat {{ $session->verse_start }}-{{ $session->verse_end }})
                                </x-table.td>
                                <x-table.td class="font-mono text-slate-300">
                                    Juz {{ $session->juz }}
                                </x-table.td>
                                <x-table.td>
                                    <x-badge color="emerald">{{ $session->grade }}</x-badge>
                                </x-table.td>
                                <x-table.td class="text-slate-400">
                                    {{ $session->teacher?->name ?? '-' }}
                                </x-table.td>
                            </x-table.tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8">
                                    <x-empty-state 
                                        icon="book-marked" 
                                        title="Belum Ada Setoran" 
                                        description="Riwayat setoran hafalan santri akan muncul di sini setelah diinput ustadz halaqah." 
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </x-table>
                </div>
            </div>
        @endif

        {{-- TAB 4: TAGIHAN SPP --}}
        @if ($activeTab === 'finance')
            <div class="space-y-6">
                {{-- Payment Methods / How to Pay Info Card --}}
                <div class="bg-slate-900/80 rounded-2xl border border-slate-800/80 p-4 sm:p-6 shadow-xs">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-sm font-bold text-slate-100 flex items-center gap-2">
                                <i data-lucide="wallet" class="w-4 h-4 text-emerald-400"></i>
                                <span>Informasi Pembayaran &amp; Rekening Sekolah</span>
                            </h2>
                            <p class="text-xs text-slate-400 mt-1">Pembayaran SPP dapat dilakukan langsung di Kasir Kantor Tata Usaha atau transfer bank resmi.</p>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-xs">
                            <span class="text-slate-400 block font-medium">Bank Syariah Indonesia (BSI):</span>
                            <span class="text-base font-mono font-bold text-emerald-400">7123456789</span>
                            <span class="text-slate-400 block text-[11px] mt-0.5">a.n. Yayasan Pendidikan Islam</span>
                        </div>
                    </div>
                </div>

                {{-- Daftar Tagihan Belum Lunas --}}
                <div class="space-y-3">
                    <h3 class="text-sm font-bold text-slate-100 flex items-center gap-2">
                        <i data-lucide="receipt" class="w-4 h-4 text-rose-400"></i>
                        <span>Rincian Tagihan SPP &amp; Biaya Pendidikan</span>
                    </h3>

                    @php
                        $allBills = \App\Models\StudentBill::with('feeCategory')
                            ->where('student_id', $selectedStudent->id)
                            ->orderByDesc('due_date')
                            ->get();
                    @endphp

                    <x-table>
                        <x-slot:header>
                            <tr>
                                <x-table.th>Pos Biaya</x-table.th>
                                <x-table.th>Jatuh Tempo</x-table.th>
                                <x-table.th>Nominal</x-table.th>
                                <x-table.th>Terbayar</x-table.th>
                                <x-table.th>Sisa Tagihan</x-table.th>
                                <x-table.th>Status</x-table.th>
                                <x-table.th>Aksi</x-table.th>
                            </tr>
                        </x-slot:header>

                        @forelse ($allBills as $bill)
                            @php
                                $remaining = $bill->remaining_amount;
                                $billStatusColor = match($bill->status) {
                                    'paid' => 'emerald',
                                    'partially_paid' => 'amber',
                                    default => 'rose',
                                };
                                $billStatusLabel = match($bill->status) {
                                    'paid' => 'Lunas',
                                    'partially_paid' => 'Sebagian',
                                    default => 'Belum Lunas',
                                };
                            @endphp
                            <x-table.tr>
                                <x-table.td class="font-medium text-slate-100">
                                    {{ $bill->feeCategory?->name ?? 'Biaya Pendidikan' }}
                                    @if ($bill->month && $bill->year)
                                        <span class="text-xs text-slate-400 block">{{ $months[(int)$bill->month] ?? '' }} {{ $bill->year }}</span>
                                    @endif
                                </x-table.td>
                                <x-table.td class="font-mono text-xs text-slate-300">
                                    {{ $bill->due_date ? \Carbon\Carbon::parse($bill->due_date)->translatedFormat('d M Y') : '-' }}
                                </x-table.td>
                                <x-table.td class="font-mono text-slate-200">
                                    Rp {{ number_format($bill->amount_due, 0, ',', '.') }}
                                </x-table.td>
                                <x-table.td class="font-mono text-emerald-400">
                                    Rp {{ number_format($bill->amount_paid, 0, ',', '.') }}
                                </x-table.td>
                                <x-table.td class="font-mono font-bold {{ $remaining > 0 ? 'text-rose-400' : 'text-slate-400' }}">
                                    Rp {{ number_format($remaining, 0, ',', '.') }}
                                </x-table.td>
                                <x-table.td>
                                    <x-badge :color="$billStatusColor">{{ $billStatusLabel }}</x-badge>
                                </x-table.td>
                                <x-table.td>
                                    @if ($remaining > 0)
                                        <x-primary-button size="xs" wire:click="openPaymentModal({{ $bill->id }})" class="whitespace-nowrap">
                                            <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
                                            <span>Bayar Online</span>
                                        </x-primary-button>
                                    @else
                                        <span class="text-xs text-slate-500 font-medium">Lunas</span>
                                    @endif
                                </x-table.td>
                            </x-table.tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8">
                                    <x-empty-state 
                                        icon="check-circle-2" 
                                        title="Tidak Ada Tagihan" 
                                        description="Santri tidak memiliki tagihan aktif saat ini." 
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </x-table>
                </div>

                {{-- Riwayat Kuitansi & Pembayaran --}}
                <div class="space-y-3 pt-2">
                    <h3 class="text-sm font-bold text-slate-100 flex items-center gap-2">
                        <i data-lucide="receipt" class="w-4 h-4 text-emerald-400"></i>
                        <span>Riwayat Pembayaran &amp; Kuitansi Resmi</span>
                    </h3>

                    <x-table>
                        <x-slot:header>
                            <tr>
                                <x-table.th>No. Kuitansi</x-table.th>
                                <x-table.th>Tanggal Bayar</x-table.th>
                                <x-table.th>Metode</x-table.th>
                                <x-table.th>Total Bayar</x-table.th>
                                <x-table.th>Aksi</x-table.th>
                            </tr>
                        </x-slot:header>

                        @forelse ($recentPayments as $payment)
                            <x-table.tr>
                                <x-table.td class="font-mono font-semibold text-emerald-400">
                                    {{ $payment->receipt_number }}
                                </x-table.td>
                                <x-table.td class="text-xs text-slate-300">
                                    {{ \Carbon\Carbon::parse($payment->paid_at)->translatedFormat('d M Y, H:i') }} WIB
                                </x-table.td>
                                <x-table.td>
                                    <x-badge color="slate">{{ ucfirst((string) $payment->payment_method) }}</x-badge>
                                </x-table.td>
                                <x-table.td class="font-mono font-bold text-slate-100">
                                    Rp {{ number_format($payment->total_amount, 0, ',', '.') }}
                                </x-table.td>
                                <x-table.td>
                                    <a href="{{ route('portal.receipts.print', ['paymentTransaction' => $payment->id]) }}" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition cursor-pointer">
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                        <span>Cetak Kuitansi</span>
                                    </a>
                                </x-table.td>
                            </x-table.tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8">
                                    <x-empty-state 
                                        icon="receipt" 
                                        title="Belum Ada Pembayaran" 
                                        description="Riwayat kuitansi pembayaran resmi kasir TU akan tercantum di sini." 
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </x-table>
                </div>
            </div>
        @endif

        {{-- TAB 5: KANTIN CASHLESS & SALDO RFID --}}
        @if ($activeTab === 'cashless')
            <div class="space-y-6">
                {{-- Wallet Summary Metrics --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    {{-- Saldo Dompet --}}
                    <div class="bg-slate-900/80 rounded-2xl border border-slate-800/80 p-5 shadow-xs">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-slate-400">Saldo Dompet Santri</span>
                            <div class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400">
                                <i data-lucide="wallet" class="w-5 h-5"></i>
                            </div>
                        </div>
                        <div class="text-2xl font-bold font-mono text-emerald-400">
                            Rp {{ number_format((float) ($wallet?->balance ?? 0), 0, ',', '.') }}
                        </div>
                        <p class="text-xs text-slate-500 mt-2">
                            Status: <span class="font-medium {{ ($wallet?->is_active ?? false) ? 'text-emerald-400' : 'text-rose-400' }}">{{ ($wallet?->is_active ?? false) ? 'Aktif' : 'Nonaktif / Terkunci' }}</span>
                        </p>
                    </div>

                    {{-- Limit Belanja Harian --}}
                    <div class="bg-slate-900/80 rounded-2xl border border-slate-800/80 p-5 shadow-xs">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-slate-400">Limit Belanja Harian</span>
                            <div class="p-2 rounded-xl bg-amber-500/10 text-amber-400">
                                <i data-lucide="shield-alert" class="w-5 h-5"></i>
                            </div>
                        </div>
                        <div class="text-2xl font-bold font-mono text-amber-400">
                            @if ($wallet && $wallet->daily_limit !== null && $wallet->daily_limit > 0)
                                Rp {{ number_format((float) $wallet->daily_limit, 0, ',', '.') }}
                            @else
                                <span class="text-slate-400 text-lg">Tanpa Batas</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-2">
                            Batas maksimal jajan per hari yang ditentukan wali
                        </p>
                    </div>

                    {{-- Terpakai Hari Ini --}}
                    <div class="bg-slate-900/80 rounded-2xl border border-slate-800/80 p-5 shadow-xs">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-slate-400">Jajan Hari Ini</span>
                            <div class="p-2 rounded-xl bg-sky-500/10 text-sky-400">
                                <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                            </div>
                        </div>
                        <div class="text-2xl font-bold font-mono text-sky-400">
                            Rp {{ number_format($wallet ? $wallet->spentToday() : 0.0, 0, ',', '.') }}
                        </div>
                        <p class="text-xs text-slate-500 mt-2">
                            @if ($wallet && $wallet->daily_limit !== null && $wallet->daily_limit > 0)
                                Sisa jatah: <span class="text-slate-300 font-mono">Rp {{ number_format($wallet->remainingDailyLimit(), 0, ',', '.') }}</span>
                            @else
                                Total belanja tercatat hari ini
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Riwayat Transaksi Jajan Kantin --}}
                <div class="bg-slate-900/80 rounded-2xl border border-slate-800/80 p-5 shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-100 flex items-center gap-2">
                                <i data-lucide="receipt" class="w-5 h-5 text-emerald-400"></i>
                                <span>Riwayat Jajan Kantin Terakhir</span>
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">Pantau transaksi belanja makanan &amp; minuman santri secara transparan dan rincian itemnya.</p>
                        </div>
                    </div>

                    <x-table>
                        <x-slot:header>
                            <tr>
                                <x-table.th>Waktu Transaksi</x-table.th>
                                <x-table.th>Merchant / Kantin</x-table.th>
                                <x-table.th>Rincian Menu Belanja</x-table.th>
                                <x-table.th>Nominal</x-table.th>
                                <x-table.th>Sisa Saldo</x-table.th>
                            </tr>
                        </x-slot:header>

                        @forelse ($canteenPurchases as $purchase)
                            <x-table.tr>
                                <x-table.td class="text-xs font-mono text-slate-300 whitespace-nowrap">
                                    {{ $purchase->created_at->translatedFormat('d M Y, H:i') }} WIB
                                </x-table.td>
                                <x-table.td>
                                    <div class="text-xs font-semibold text-slate-200">
                                        {{ $purchase->merchant?->name ?? 'Kantin Pesantren' }}
                                    </div>
                                    <span class="text-[10px] text-slate-500 font-mono">{{ $purchase->reference_number }}</span>
                                </x-table.td>
                                <x-table.td>
                                    @if ($purchase->order && $purchase->order->items->isNotEmpty())
                                        <div class="space-y-1">
                                            @foreach ($purchase->order->items as $item)
                                                <div class="text-xs text-slate-300 flex items-center gap-2">
                                                    <span class="font-medium text-slate-200">{{ $item->product_name }}</span>
                                                    <span class="text-slate-500 text-[11px]">x{{ $item->quantity }}</span>
                                                    <span class="text-slate-400 font-mono text-[11px]">(Rp {{ number_format($item->subtotal, 0, ',', '.') }})</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400">{{ $purchase->description ?? 'Belanja Kantin' }}</span>
                                    @endif
                                </x-table.td>
                                <x-table.td class="text-xs font-bold font-mono text-rose-400 whitespace-nowrap">
                                    -Rp {{ number_format((float) $purchase->amount, 0, ',', '.') }}
                                </x-table.td>
                                <x-table.td class="text-xs font-mono text-slate-300 whitespace-nowrap">
                                    Rp {{ number_format((float) $purchase->balance_after, 0, ',', '.') }}
                                </x-table.td>
                            </x-table.tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8">
                                    <x-empty-state 
                                        icon="shopping-bag" 
                                        title="Belum Ada Transaksi Belanja" 
                                        description="Riwayat belanja santri di kantin menggunakan Smart Card RFID akan muncul di sini." 
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </x-table>
                </div>
            </div>
        @endif
    @endif

    {{-- MODAL PEMBAYARAN ONLINE (TRIPAY QRIS / VIRTUAL ACCOUNT) --}}
    <x-modal name="online-payment-modal" :show="$showPaymentModal" max-width="lg">
        <div class="p-5 sm:p-6 space-y-5">
            <x-modal-header 
                title="{{ $activeOnlineTx ? 'Instruksi Pembayaran' : 'Pembayaran Tagihan Online' }}" 
                icon="{{ $activeOnlineTx ? 'qr-code' : 'credit-card' }}" 
                tone="emerald" 
                closeable="true" 
                closeAction="closePaymentModal"
            >
                {{ $activeOnlineTx ? 'Selesaikan pembayaran sebelum batas waktu berakhir.' : 'Pilih saluran pembayaran online via TriPay Payment Gateway.' }}
            </x-modal-header>

            @if ($payingBill)
                @if (! $activeOnlineTx)
                    {{-- Langkah 1: Pilih Metode Pembayaran --}}
                    <div class="space-y-4">
                        <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 space-y-2 text-xs">
                            <div class="flex items-center justify-between text-slate-400">
                                <span>Tagihan:</span>
                                <strong class="text-slate-200">{{ $payingBill->feeCategory?->name }} ({{ $payingBill->month_name }} {{ $payingBill->year }})</strong>
                            </div>
                            <div class="flex items-center justify-between text-slate-400">
                                <span>Nama Santri:</span>
                                <strong class="text-slate-200">{{ $payingBill->student->name }}</strong>
                            </div>
                            <div class="flex items-center justify-between pt-2 border-t border-slate-800/80">
                                <span class="font-semibold text-slate-300">Nominal Tagihan:</span>
                                <span class="text-base font-bold font-mono text-emerald-400">
                                    Rp {{ number_format($payingBill->remaining_amount, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <x-input-label for="payment-channel" value="Pilih Metode Pembayaran:" class="mb-1.5" />
                            <x-select id="payment-channel" wire:model.live="selectedPaymentChannel">
                                @foreach ($paymentChannels as $ch)
                                    <option value="{{ $ch['code'] }}">{{ $ch['name'] }}</option>
                                @endforeach
                            </x-select>
                            <p class="text-[11px] text-slate-400 mt-1.5 flex items-center gap-1">
                                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-400"></i>
                                <span>Terhubung otomatis dengan gateway resmi TriPay (Bebas double-charge).</span>
                            </p>
                        </div>
                    </div>

                    <x-modal-actions>
                        <x-secondary-button wire:click="closePaymentModal">Batal</x-secondary-button>
                        <x-primary-button wire:click="createOnlinePayment">
                            <x-loading-label target="createOnlinePayment">Lanjutkan Pembayaran</x-loading-label>
                        </x-primary-button>
                    </x-modal-actions>

                @else
                    {{-- Langkah 2: Tampilan Instruksi Bayar (QRIS / VA) --}}
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-950 border border-slate-800 text-xs">
                            <div>
                                <span class="text-slate-400 block text-[11px]">Order ID:</span>
                                <span class="font-mono font-bold text-slate-200">{{ $activeOnlineTx->order_id }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-slate-400 block text-[11px]">Metode:</span>
                                <span class="font-semibold text-emerald-400">{{ $activeOnlineTx->payment_channel_name }}</span>
                            </div>
                        </div>

                        @if ($activeOnlineTx->payment_channel === 'QRIS')
                            {{-- Tampilan QRIS --}}
                            <div class="bg-white p-5 rounded-2xl flex flex-col items-center justify-center text-center shadow-md">
                                <div class="font-bold text-slate-900 text-sm tracking-tight mb-2">QRIS Standar Pembayaran Nasional</div>
                                @if ($activeOnlineTx->qr_url)
                                    <img src="{{ $activeOnlineTx->qr_url }}" alt="QRIS" class="w-52 h-52 object-contain rounded-lg">
                                @else
                                    <div class="w-48 h-48 border-2 border-dashed border-slate-300 rounded-xl flex items-center justify-center text-slate-600 font-mono text-xs p-3">
                                        {{ substr($activeOnlineTx->qr_string ?? 'QRIS KODE VALID', 0, 80) }}...
                                    </div>
                                @endif
                                <p class="text-[11px] text-slate-600 mt-2 max-w-xs leading-tight">
                                    Buka aplikasi m-Banking atau e-Wallet (BCA, Mandiri, BRI, BSI, Gopay, Dana, OVO, ShopeePay), lalu scan QR di atas.
                                </p>
                            </div>
                        @else
                            {{-- Tampilan Virtual Account / Retail --}}
                            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 text-center space-y-2">
                                <span class="text-xs text-slate-400">Nomor Rekening Virtual Account:</span>
                                <div class="text-2xl font-mono font-extrabold text-emerald-400 tracking-wider select-all">
                                    {{ $activeOnlineTx->va_number ?? '-' }}
                                </div>
                                <p class="text-[11px] text-slate-500">
                                    Transfer tepat sejumlah total pembayaran ke nomor VA di atas.
                                </p>
                            </div>
                        @endif

                        <div class="p-3.5 rounded-xl bg-slate-950/80 border border-slate-800 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] text-slate-400 block">Total Tagihan + Admin:</span>
                                <span class="text-lg font-mono font-bold text-slate-100">
                                    Rp {{ number_format($activeOnlineTx->gross_amount, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="text-[11px] text-slate-400 block">Batas Waktu:</span>
                                <span class="text-xs font-mono text-rose-400 font-semibold">
                                    {{ $activeOnlineTx->expires_at ? \Carbon\Carbon::parse($activeOnlineTx->expires_at)->format('H:i') . ' WIB' : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <x-modal-actions>
                        <x-secondary-button wire:click="checkPaymentStatus">
                            <x-loading-label target="checkPaymentStatus">Cek Status Pembayaran</x-loading-label>
                        </x-secondary-button>
                        <x-primary-button wire:click="closePaymentModal">
                            Tutup
                        </x-primary-button>
                    </x-modal-actions>
                @endif
            @endif
        </div>
    </x-modal>
</div>
