<div class="space-y-6 sm:space-y-7">
    {{-- FOKAL POIN UTAMA: Banner Eksekutif Selamat Datang & Status Sekolah --}}
    <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-gradient-to-br dark:from-slate-900 dark:via-slate-900 dark:to-slate-800/95 border border-slate-200 dark:border-slate-800/90 p-5 sm:p-7 shadow-sm dark:shadow-xl">
        {{-- Decorative Glow Accent --}}
        <div class="absolute -top-12 -right-12 w-56 h-56 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none opacity-40 dark:opacity-100"></div>

        <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="space-y-2.5">
                <div class="flex items-center gap-2 flex-wrap">
                    @if ($academicYear)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700/60">
                            <i data-lucide="calendar" class="w-3 h-3 text-emerald-600 dark:text-emerald-400"></i>
                            <span>T.A. {{ $academicYear->name }} ({{ ucfirst((string) $academicYear->semester) }})</span>
                        </span>
                    @endif

                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100/80 text-slate-600 border border-slate-200 dark:bg-slate-800/70 dark:text-slate-400 dark:border-slate-700/50 font-mono">
                        <i data-lucide="clock" class="w-3 h-3 text-sky-600 dark:text-sky-400"></i>
                        <span>{{ now()->translatedFormat('l, d F Y') }}</span>
                    </span>

                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-400 dark:border-emerald-800/50">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Sistem Aktif</span>
                    </span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    {{ __('Assalamu\'alaikum, :name', ['name' => auth()->user()->name]) }}
                </h1>

                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-2xl leading-relaxed">
                    Sistem Informasi Manajemen Terpadu Pesantren &amp; Madrasah. Pantau absensi santri, setoran tahfidz, kasir SPP, dan operasional asrama dari satu tempat.
                </p>
            </div>

            <div class="flex items-center gap-2.5 shrink-0 pt-1 md:pt-0 flex-wrap">
                @can('view-reports')
                    <a href="{{ route('reports.center') }}" wire:navigate class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-sm transition cursor-pointer">
                        <i data-lucide="file-bar-chart" class="w-4 h-4"></i>
                        <span>Pusat Laporan</span>
                    </a>
                @endcan

                <a href="{{ route('profile') }}" wire:navigate class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700/80 dark:text-slate-200 dark:border-slate-700/60 transition cursor-pointer">
                    <i data-lucide="user-cog" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                    <span>Pengaturan Akun</span>
                </a>
            </div>
        </div>
    </div>

    @if (auth()->user()->roles->isEmpty())
        <div role="status" class="flex items-start gap-3 rounded-2xl border border-amber-200 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-500/10 p-4 sm:p-5">
            <i data-lucide="user-lock" class="w-5 h-5 shrink-0 text-amber-600 dark:text-amber-400 mt-0.5" aria-hidden="true"></i>
            <div class="space-y-1">
                <h2 class="text-sm font-bold text-amber-800 dark:text-amber-300">Akun Anda belum punya peran</h2>
                <p class="text-xs text-amber-700 dark:text-slate-300 leading-relaxed">Menu kerja baru muncul setelah admin sekolah memberi Anda peran (guru, bendahara, dan seterusnya). Hubungi admin untuk mengaktifkan akses. Sementara itu Anda bisa melengkapi data akun di Profil Saya.</p>
            </div>
        </div>
    @endif

    {{-- TIER 1: INDIKATOR UTAMA EKSEKUTIF (4 KARTU SEIMBANG) --}}
    @if (! empty($primaryStats))
        <section aria-labelledby="primary-metrics-heading" class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <h2 id="primary-metrics-heading" class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                        Indikator Utama Lembaga
                    </h2>
                </div>
                <span class="text-[11px] text-slate-500 font-mono">Live Telemetry</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
                @foreach ($primaryStats as $stat)
                    <x-dashboard.stat
                        :title="$stat['title']"
                        :value="$stat['value']"
                        :icon="$stat['icon']"
                        :tone="$stat['tone']"
                        :subtitle="$stat['subtitle']"
                        :href="$stat['href']"
                    />
                @endforeach
            </div>
        </section>
    @endif

    {{-- TIER 2: TELEMETRI OPERASIONAL & LAYANAN TERPADU (6 KARTU KOMPAK SEIMBANG) --}}
    @if (! empty($operationalStats))
        <section aria-labelledby="operational-metrics-heading" class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                    <h2 id="operational-metrics-heading" class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                        Operasional &amp; Layanan Terpadu
                    </h2>
                </div>
                <span class="text-[11px] text-slate-500">Kesiswaan, Asrama, Pustaka &amp; POS</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-3.5">
                @foreach ($operationalStats as $stat)
                    <x-dashboard.stat
                        :title="$stat['title']"
                        :value="$stat['value']"
                        :icon="$stat['icon']"
                        :tone="$stat['tone']"
                        :subtitle="$stat['subtitle']"
                        :href="$stat['href']"
                    />
                @endforeach
            </div>
        </section>
    @endif

    {{-- TIER 3: STATISTIK ADMIN & NOTIFIKASI SISTEM (JIKA ADA) --}}
    @if (! empty($systemStats))
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            @foreach ($systemStats as $stat)
                <x-dashboard.stat
                    :title="$stat['title']"
                    :value="$stat['value']"
                    :icon="$stat['icon']"
                    :tone="$stat['tone']"
                    :subtitle="$stat['subtitle']"
                    :href="$stat['href']"
                />
            @endforeach
        </div>
    @endif

    {{-- MENU AKSI CEPAT (SHORTCUTS KERJA HARIAN) --}}
    @if (! empty($shortcuts))
        <section aria-labelledby="shortcuts-heading" class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <h2 id="shortcuts-heading" class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                        Pintasan &amp; Alur Kerja Cepat
                    </h2>
                </div>
                <span class="text-[11px] text-slate-500">Akses langsung fitur harian</span>
            </div>

            <x-dashboard.shortcuts :links="$shortcuts" />
        </section>
    @endif

    {{-- SECTION 2 KOLOM: MUTABA'AH SHALAT, TRANSAKSI KASIR, & AUDIT TRAIL --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-start">
        {{-- KOLOM KIRI (7 Kolom): Mutaba'ah Shalat & Transaksi Kas Masuk Terkini --}}
        <div class="lg:col-span-7 space-y-5">
            {{-- WIDGET 1: Mutaba'ah Shalat Jama'ah Santri Hari Ini --}}
            @if ($worshipOverview !== null)
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <div class="p-2 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                <i data-lucide="calendar-check-2" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Mutaba'ah Shalat Jama'ah Santri Hari Ini</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                    @if ($worshipOverview['has_records'])
                                        Rekap shalat fardhu berjama'ah di masjid dari {{ $worshipOverview['total_recorded'] }} santri terdata.
                                    @else
                                        Pencatatan shalat berjama'ah 5 waktu di masjid pesantren.
                                    @endif
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('islamic.mutabaah') }}" wire:navigate class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:text-emerald-500 transition">
                            Buka Lembar →
                        </a>
                    </div>

                    @if ($worshipOverview['has_records'])
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5 text-center">
                            <div class="bg-slate-50 dark:bg-slate-950/60 p-3 rounded-xl border border-slate-200 dark:border-slate-800/60">
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium block">Subuh</span>
                                <div class="text-lg font-bold font-mono text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $worshipOverview['subuh'] }}</div>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500">santri jama'ah</span>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-950/60 p-3 rounded-xl border border-slate-200 dark:border-slate-800/60">
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium block">Dzuhur</span>
                                <div class="text-lg font-bold font-mono text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $worshipOverview['dzuhur'] }}</div>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500">santri jama'ah</span>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-950/60 p-3 rounded-xl border border-slate-200 dark:border-slate-800/60">
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium block">Ashar</span>
                                <div class="text-lg font-bold font-mono text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $worshipOverview['ashar'] }}</div>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500">santri jama'ah</span>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-950/60 p-3 rounded-xl border border-slate-200 dark:border-slate-800/60">
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium block">Maghrib</span>
                                <div class="text-lg font-bold font-mono text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $worshipOverview['maghrib'] }}</div>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500">santri jama'ah</span>
                            </div>
                            <div class="col-span-2 sm:col-span-1 bg-slate-50 dark:bg-slate-950/60 p-3 rounded-xl border border-slate-200 dark:border-slate-800/60">
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium block">Isya</span>
                                <div class="text-lg font-bold font-mono text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $worshipOverview['isya'] }}</div>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500">santri jama'ah</span>
                            </div>
                        </div>
                    @else
                        {{-- Keadaan ketika belum ada entri shalat hari ini: Tampilan informatif & actionable --}}
                        <div class="rounded-xl border border-dashed border-slate-200 dark:border-slate-800 p-4 bg-slate-50 dark:bg-slate-950/40 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                                    <i data-lucide="clock" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-white">Belum Ada Catatan Shalat Hari Ini</h4>
                                    <p class="text-[11px] text-slate-600 dark:text-slate-400 mt-0.5">Input kehadiran shalat fardhu Subuh, Dzuhur, Ashar, Maghrib, atau Isya untuk hari ini.</p>
                                </div>
                            </div>

                            <a href="{{ route('islamic.mutabaah') }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-xs transition shrink-0 cursor-pointer">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                <span>Input Shalat</span>
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            {{-- WIDGET 2: Penerimaan Kasir & Transaksi Terkini (Jika Berhak Melihat Keuangan) --}}
            @if ($recentTransactions && $recentTransactions->isNotEmpty())
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <div class="p-2 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                <i data-lucide="receipt" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Penerimaan Kasir &amp; Kuitansi Terkini</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Transaksi pembayaran SPP dan tagihan masuk terbaru.</p>
                            </div>
                        </div>
                        <a href="{{ route('finance.transactions') }}" wire:navigate class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:text-emerald-500 transition">
                            Semua Transaksi →
                        </a>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($recentTransactions as $tx)
                            <div class="py-2.5 flex items-center justify-between gap-3 text-xs" wire:key="dashboard-tx-{{ $tx->id }}">
                                <div class="min-w-0 space-y-0.5">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-semibold text-slate-900 dark:text-white">{{ $tx->receipt_number }}</span>
                                        <span class="text-slate-400">&bull;</span>
                                        <span class="font-medium text-slate-700 dark:text-slate-300 truncate">{{ $tx->student?->name ?? 'Santri' }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                        <span>{{ $tx->student?->currentEnrollment?->schoolClass?->name ?? 'Rombel' }}</span>
                                        <span>&bull;</span>
                                        <span class="capitalize">{{ $tx->payment_method_label }}</span>
                                        <span>&bull;</span>
                                        <span>{{ $tx->paid_at->diffForHumans() }}</span>
                                    </div>
                                </div>

                                <div class="text-right shrink-0">
                                    <div class="font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                        Rp {{ number_format((float) $tx->total_amount, 0, ',', '.') }}
                                    </div>
                                    <a href="{{ route('finance.receipts.print', $tx->id) }}" target="_blank" class="text-[10px] text-slate-400 hover:text-emerald-500 inline-flex items-center gap-1 mt-0.5">
                                        <i data-lucide="printer" class="w-3 h-3"></i>
                                        <span>Cetak</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- KOLOM KANAN (5 Kolom): Jejak Audit & Info Pengguna --}}
        <div class="lg:col-span-5 space-y-5">
            {{-- Aktivitas Sistem Terkini (Audit Trail) --}}
            @if ($activities !== null)
                <x-dashboard.activity-feed :activities="$activities" />
            @else
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm space-y-3">
                    <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider">
                        <i data-lucide="info" class="w-4 h-4 text-emerald-500"></i>
                        <span>Informasi Peran Pengguna</span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                        Anda masuk sebagai <strong class="text-emerald-600 dark:text-emerald-400">{{ auth()->user()->roles->pluck('name')->map(fn($r) => \Illuminate\Support\Str::headline($r))->implode(', ') ?: 'Pengguna' }}</strong>. Menu kerja di sidebar disesuaikan secara otomatis berdasarkan izin peran Anda.
                    </p>
                </div>
            @endif

            {{-- Ringkasan Status Sesi Kerja & Bantuan --}}
            <div class="bg-slate-50 dark:bg-slate-900/60 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 sm:p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-500"></i>
                        Status Keamanan &amp; Akses
                    </span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40 font-semibold">
                        Aman
                    </span>
                </div>

                <div class="space-y-2 text-xs text-slate-600 dark:text-slate-400">
                    <div class="flex justify-between">
                        <span>Email Terdaftar:</span>
                        <span class="font-medium text-slate-900 dark:text-slate-200">{{ auth()->user()->email }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Waktu Masuk:</span>
                        <span class="font-mono text-slate-900 dark:text-slate-200">{{ now()->format('H:i') }} WIB</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Pusat Unduhan:</span>
                        @can('view-reports')
                            <a href="{{ route('reports.center') }}" wire:navigate class="text-emerald-600 dark:text-emerald-400 hover:underline font-semibold">
                                Ekspor Rekap Bulanan
                            </a>
                        @else
                            <span class="text-slate-400">-</span>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
