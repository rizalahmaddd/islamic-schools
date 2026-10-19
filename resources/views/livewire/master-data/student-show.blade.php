<div class="space-y-6">
    {{-- Header & Navigasi Kembali --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div class="flex items-center gap-3">
            <a aria-label="Kembali" href="{{ route('master-data.students') }}" wire:navigate class="inline-flex items-center justify-center w-11 h-11 sm:w-9 sm:h-9 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-slate-100 hover:bg-slate-800 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-100 tracking-tight">{{ $student->name }}</h1>
                    <x-badge :color="$student->statusBadgeColor()">
                        {{ $student->statusLabel() }}
                    </x-badge>
                    @if ($student->currentClass())
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/15 text-emerald-400 border border-emerald-500/25">
                            <i data-lucide="layout-grid" class="w-3 h-3"></i>
                            <span>{{ $student->currentClass()->name }}</span>
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-400 mt-1 font-mono">NIS: {{ $student->nis }} {{ $student->nisn ? '• NISN: '.$student->nisn : '' }}</p>
            </div>
        </div>
    </div>

    {{-- Grid 3 Kolom Info --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Kolom 1: Profil & Identitas Santri --}}
        <div class="space-y-6">
            {{-- Biodata Pokok --}}
            <div class="bg-slate-900/80 rounded-2xl border border-slate-800/80 p-5 space-y-4 shadow-xs">
                <div class="flex items-center gap-2 text-emerald-400 font-semibold text-xs uppercase tracking-wider pb-2 border-b border-slate-800">
                    <i data-lucide="user" class="w-4 h-4"></i>
                    <span>Biodata Santri</span>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-slate-400 block">Nama Lengkap</span>
                        <span class="text-slate-200 font-semibold text-sm">{{ $student->name }}</span>
                    </div>

                    @if ($student->nickname)
                        <div>
                            <span class="text-slate-400 block">Nama Panggilan</span>
                            <span class="text-slate-200">{{ $student->nickname }}</span>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <span class="text-slate-400 block">Jenis Kelamin</span>
                            <span class="text-slate-200">{{ $student->gender === 'L' ? 'Laki-laki (Ikhwan)' : 'Perempuan (Akhwat)' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">NIK</span>
                            <span class="font-mono text-slate-200">{{ $student->nik ?: '-' }}</span>
                        </div>
                    </div>

                    <div>
                        <span class="text-slate-400 block">Tempat, Tanggal Lahir</span>
                        <span class="text-slate-200">
                            {{ $student->pob ?: '-' }}{{ $student->dob ? ', ' . $student->dob->translatedFormat('d F Y') : '' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-slate-400 block">No. Telepon Santri</span>
                        <span class="font-mono text-slate-200">{{ $student->phone ?: '-' }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block">Alamat Domisili</span>
                        <p class="text-slate-300 leading-relaxed mt-0.5">{{ $student->address ?: 'Belum diisi' }}</p>
                    </div>
                </div>
            </div>

            {{-- Orang Tua / Wali Murid --}}
            <div class="bg-slate-900/80 rounded-2xl border border-slate-800/80 p-5 space-y-4 shadow-xs">
                <div class="flex items-center gap-2 text-emerald-400 font-semibold text-xs uppercase tracking-wider pb-2 border-b border-slate-800">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    <span>Orang Tua / Wali Murid</span>
                </div>

                @if ($student->guardian)
                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-slate-400 block">Nama Wali</span>
                            <span class="text-slate-200 font-semibold text-sm">{{ $student->guardian->name }}</span>
                            <span class="text-[11px] text-slate-400">({{ $student->guardian->relationship }})</span>
                        </div>

                        <div>
                            <span class="text-slate-400 block">Nomor WhatsApp Resmi</span>
                            <a href="https://wa.me/{{ $student->guardian->phone }}" target="_blank" class="font-mono text-emerald-400 hover:underline inline-flex items-center gap-1.5 mt-0.5">
                                <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
                                <span>{{ $student->guardian->phone }}</span>
                            </a>
                        </div>

                        <div>
                            <span class="text-slate-400 block">Pekerjaan</span>
                            <span class="text-slate-200">{{ $student->guardian->occupation ?: '-' }}</span>
                        </div>

                        <div>
                            <span class="text-slate-400 block">Alamat Wali</span>
                            <p class="text-slate-300 leading-relaxed mt-0.5">{{ $student->guardian->address ?: '-' }}</p>
                        </div>
                    </div>
                @else
                    <p class="text-xs text-slate-400 italic">Data orang tua/wali belum ditautkan.</p>
                @endif
            </div>

            {{-- Penempatan Asrama & Kamar (Fase 7) --}}
            <div class="bg-slate-900/80 rounded-2xl border border-slate-800/80 p-5 space-y-4 shadow-xs">
                <div class="flex items-center gap-2 text-amber-400 font-semibold text-xs uppercase tracking-wider pb-2 border-b border-slate-800">
                    <i data-lucide="home" class="w-4 h-4"></i>
                    <span>Asrama &amp; Kamar</span>
                </div>

                @php $dormPlacement = $student->currentDormPlacement(); @endphp
                @if ($dormPlacement && $dormPlacement->room)
                    <div class="space-y-2.5 text-xs">
                        <div>
                            <span class="text-slate-400 block">Gedung Asrama</span>
                            <span class="text-slate-200 font-semibold">{{ $dormPlacement->room->building?->name ?? 'Gedung Asrama' }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <span class="text-slate-400 block">Nomor Kamar</span>
                                <span class="font-mono text-slate-100 font-bold">{{ $dormPlacement->room->room_number }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Nama Kamar</span>
                                <span class="text-slate-200">{{ $dormPlacement->room->name }}</span>
                            </div>
                        </div>
                        @if ($dormPlacement->bed_number)
                            <div>
                                <span class="text-slate-400 block">Nomor Ranjang</span>
                                <span class="font-mono text-slate-200">{{ $dormPlacement->bed_number }}</span>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-xs text-slate-400 italic">Santri belum dialokasikan ke kamar asrama.</p>
                @endif
            </div>

            {{-- Smart Card RFID & Dompet Digital (Fase 12) --}}
            <div class="bg-slate-900/80 rounded-2xl border border-slate-800/80 p-5 space-y-4 shadow-xs">
                <div class="flex items-center gap-2 text-sky-400 font-semibold text-xs uppercase tracking-wider pb-2 border-b border-slate-800">
                    <i data-lucide="credit-card" class="w-4 h-4"></i>
                    <span>Kantin &amp; Saldo RFID</span>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-slate-400 block">Saldo Dompet Digital</span>
                        <div class="text-lg font-bold font-mono text-emerald-400 mt-0.5">
                            Rp {{ number_format((float) ($student->wallet?->balance ?? 0), 0, ',', '.') }}
                        </div>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Batas Limit Harian Orang Tua</span>
                        <span class="text-slate-200 font-mono">
                            {{ ($student->wallet && $student->wallet->daily_limit > 0) ? 'Rp '.number_format((float) $student->wallet->daily_limit, 0, ',', '.') : 'Tanpa Batas' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Kartu RFID Terdaftar</span>
                        @if ($student->rfidCards->isNotEmpty())
                            <div class="space-y-1 mt-1">
                                @foreach ($student->rfidCards as $card)
                                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-950/60 border border-slate-800/60">
                                        <span class="font-mono text-slate-300">{{ $card->card_uid }}</span>
                                        <x-badge :color="$card->is_active ? 'emerald' : 'rose'">
                                            {{ $card->is_active ? 'Aktif' : 'Terblokir' }}
                                        </x-badge>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <span class="text-slate-500 italic">Belum ada kartu RFID yang dipasang.</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom 2 & 3: Riwayat Akademik, Tahfidz & Keuangan --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Riwayat Rombongan Belajar --}}
            <div class="bg-slate-900/80 rounded-2xl border border-slate-800/80 p-5 space-y-4 shadow-xs">
                <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                    <div class="flex items-center gap-2 text-emerald-400 font-semibold text-xs uppercase tracking-wider">
                        <i data-lucide="layout-grid" class="w-4 h-4"></i>
                        <span>Riwayat Penempatan Rombongan Belajar</span>
                    </div>
                </div>

                @if ($student->enrollments->isEmpty())
                    <x-empty-state
                        icon="layout-grid"
                        title="Belum ditempatkan di kelas"
                        description="Santri ini belum memiliki riwayat penempatan kelas pada tahun ajaran mana pun."
                    />
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="text-slate-400 uppercase font-semibold border-b border-slate-800">
                                <tr>
                                    <th class="pb-2.5">Tahun Ajaran</th>
                                    <th class="pb-2.5">Kelas / Rombel</th>
                                    <th class="pb-2.5">Wali Kelas</th>
                                    <th class="pb-2.5">Tanggal Masuk</th>
                                    <th class="pb-2.5">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60">
                                @foreach ($student->enrollments as $enr)
                                    <tr>
                                        <td class="py-2.5 text-slate-200">
                                            {{ $enr->academicYear?->name }} ({{ ucfirst((string) $enr->academicYear?->semester) }})
                                            @if ($enr->academicYear?->is_active)
                                                <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Aktif</span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 font-semibold text-slate-100">
                                            {{ $enr->schoolClass?->name }}
                                        </td>
                                        <td class="py-2.5 text-slate-300">
                                            {{ $enr->schoolClass?->homeroomTeacher?->name ?? '-' }}
                                        </td>
                                        <td class="py-2.5 text-slate-400 font-mono">
                                            {{ $enr->enrolled_at?->format('d/m/Y') ?? '-' }}
                                        </td>
                                        <td class="py-2.5">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium {{ $enr->status === 'active' ? 'text-emerald-400 bg-emerald-500/10' : 'text-slate-400 bg-slate-800' }}">
                                                {{ ucfirst((string) $enr->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- 2 Kolom: Tahfidz & Poin Adab Santri --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Setoran Tahfidz Terakhir --}}
                <div class="bg-slate-900/80 rounded-2xl border border-slate-800/80 p-5 space-y-4 shadow-xs">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                        <div class="flex items-center gap-2 text-emerald-400 font-semibold text-xs uppercase tracking-wider">
                            <i data-lucide="book-marked" class="w-4 h-4"></i>
                            <span>Tahfidz Terakhir</span>
                        </div>
                        <a href="{{ route('islamic.tahfidz') }}" wire:navigate class="text-xs text-emerald-400 hover:underline">
                            Buku Tahfidz
                        </a>
                    </div>

                    @if ($student->tahfidzSessions->isEmpty())
                        <p class="text-xs text-slate-400 italic">Belum ada catatan setoran tahfidz.</p>
                    @else
                        <div class="space-y-2.5">
                            @foreach ($student->tahfidzSessions as $session)
                                <div class="p-2.5 rounded-xl bg-slate-950/60 border border-slate-800/60 text-xs space-y-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-slate-100">{{ $session->surah?->name_latin ?? 'Al-Qur\'an' }}</span>
                                        <x-badge color="emerald">Nilai: {{ $session->grade }}</x-badge>
                                    </div>
                                    <div class="flex items-center justify-between text-slate-400 text-[11px]">
                                        <span>Juz {{ $session->juz }} (Ayat {{ $session->verse_start }}-{{ $session->verse_end }})</span>
                                        <span class="font-mono">{{ $session->recorded_at?->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Rekam Poin Adab & Disiplin --}}
                <div class="bg-slate-900/80 rounded-2xl border border-slate-800/80 p-5 space-y-4 shadow-xs">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                        <div class="flex items-center gap-2 text-amber-400 font-semibold text-xs uppercase tracking-wider">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                            <span>Poin Adab Santri</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs font-mono">
                            <span class="text-emerald-400 font-bold">+{{ $student->totalMeritPoints() }}</span>
                            <span class="text-slate-600">/</span>
                            <span class="text-rose-400 font-bold">-{{ $student->totalViolationPoints() }}</span>
                        </div>
                    </div>

                    @if ($student->conductRecords->isEmpty())
                        <p class="text-xs text-slate-400 italic">Belum ada catatan prestasi atau pelanggaran santri.</p>
                    @else
                        <div class="space-y-2.5">
                            @foreach ($student->conductRecords as $record)
                                <div class="p-2.5 rounded-xl bg-slate-950/60 border border-slate-800/60 text-xs space-y-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-semibold text-slate-200">{{ $record->description }}</span>
                                        <span class="font-mono font-bold text-[11px] {{ $record->type === 'merit' ? 'text-emerald-400' : 'text-rose-400' }}">
                                            {{ $record->type === 'merit' ? '+' : '-' }}{{ $record->points }} Poin
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        {{ $record->category }} • <span class="font-mono">{{ $record->date?->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Audit Trail Riwayat Perubahan --}}
            @if ($this->canViewActivityLog())
                <div class="bg-slate-900/80 rounded-2xl border border-slate-800/80 p-5 space-y-4 shadow-xs">
                    <div class="flex items-center gap-2 text-slate-400 font-semibold text-xs uppercase tracking-wider pb-2 border-b border-slate-800">
                        <i data-lucide="scroll-text" class="w-4 h-4"></i>
                        <span>Riwayat Perubahan Data (Audit Trail)</span>
                    </div>

                    @if ($this->history->isEmpty())
                        <p class="text-xs text-slate-400 italic">Belum ada riwayat perubahan yang tercatat.</p>
                    @else
                        <div class="space-y-2.5">
                            @foreach ($this->history as $activity)
                                <div class="text-xs bg-slate-950/60 p-3 rounded-xl border border-slate-800/60 space-y-1">
                                    <div class="flex items-center justify-between text-slate-400">
                                        <span class="font-semibold text-slate-200">{{ $activity->causer?->name ?? 'Sistem' }}</span>
                                        <span class="font-mono text-[11px]">{{ $activity->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-slate-300">{{ $activity->description }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
