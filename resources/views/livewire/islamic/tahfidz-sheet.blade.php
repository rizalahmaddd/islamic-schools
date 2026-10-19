<div class="space-y-6">
    {{-- Filter Rombel & Pemilihan Santri --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/60 p-5 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="selectedClassId" value="Pilih Kelas / Halaqah" />
                <x-select id="selectedClassId" wire:model.live="selectedClassId" class="w-full mt-1">
                    @foreach ($schoolClasses as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} (Tingkat {{ $c->grade_level }})</option>
                    @endforeach
                </x-select>
            </div>

            <div>
                <x-input-label for="selectedStudentId" value="Pilih Santri yang Menyetor" />
                <x-select id="selectedStudentId" wire:model.live="selectedStudentId" class="w-full mt-1">
                    @forelse ($studentsInClass as $st)
                        <option value="{{ $st->id }}">{{ $st->name }} (NIS: {{ $st->nis }})</option>
                    @empty
                        <option value="">Tidak ada santri aktif di kelas ini</option>
                    @endforelse
                </x-select>
            </div>
        </div>
    </div>

    @if ($selectedStudent && $progressData)
        {{-- Visual Progress Hafalan 30 Juz --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/60 p-5 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        Capaian Hafalan 30 Juz: {{ $selectedStudent->name }}
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Total Sesi Setoran: {{ $progressData['total_sessions_count'] }} kali
                        • Terakhir: {{ $progressData['latest_session']?->recorded_at?->diffForHumans() ?? 'Belum ada' }}
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        {{ $progressData['completed_juz_count'] }} / 30 Juz Mutqin
                    </span>
                </div>
            </div>

            {{-- Grid 30 Blok Juz --}}
            <div class="grid grid-cols-5 sm:grid-cols-6 md:grid-cols-10 gap-2 pt-2">
                @foreach ($progressData['juz_progress'] as $juzNum => $info)
                    @php
                        $bgColor = match ($info['status']) {
                            'completed' => 'bg-emerald-600 text-white border-emerald-500 shadow-sm shadow-emerald-600/30 font-bold',
                            'in_progress' => 'bg-amber-500/20 text-amber-700 dark:text-amber-300 border-amber-500/40 font-medium',
                            default => 'bg-slate-100 dark:bg-slate-900 text-slate-400 dark:text-slate-500 border-slate-200 dark:border-slate-700/60',
                        };
                    @endphp
                    <div
                        class="h-11 rounded-xl border flex flex-col items-center justify-center text-xs transition select-none {{ $bgColor }}"
                        title="Juz {{ $juzNum }}: {{ $info['status'] === 'completed' ? 'Mutqin' : ($info['status'] === 'in_progress' ? 'Sedang Berjalan ('.$info['total_sessions'].' sesi)' : 'Belum disetor') }}"
                    >
                        <span class="text-[10px] leading-none opacity-80">Juz</span>
                        <span class="text-xs leading-none font-bold mt-0.5">{{ $juzNum }}</span>
                    </div>
                @endforeach
            </div>

            <div class="flex items-center gap-4 pt-2 text-[11px] text-slate-500 dark:text-slate-400">
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded bg-emerald-600 inline-block"></span>
                    <span>Sudah Mutqin (A/B)</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded bg-amber-500/30 border border-amber-500/40 inline-block"></span>
                    <span>Sedang Ditempuh</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded bg-slate-200 dark:bg-slate-700 inline-block"></span>
                    <span>Belum Ada Setoran</span>
                </div>
            </div>
        </div>

        {{-- Form Input Setoran Baru & Riwayat --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- Form Input Setoran (5 Kolom) --}}
            <div class="lg:col-span-5 space-y-4">
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/60 p-5 shadow-sm space-y-4">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        Catat Setoran Santri
                    </h3>

                    <form wire:submit="saveSession" class="space-y-4">
                        {{-- Jenis Setoran --}}
                        <div>
                            <x-input-label value="Jenis Setoran" />
                            <div class="grid grid-cols-2 gap-2 mt-1">
                                <button
                                    type="button"
                                    wire:click="$set('session_type', 'ziyadah')"
                                    class="py-2 text-xs font-semibold rounded-lg border transition {{ $session_type === 'ziyadah' ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' : 'bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-800/60' }}"
                                >
                                    Ziyadah (Hafalan Baru)
                                </button>
                                <button
                                    type="button"
                                    wire:click="$set('session_type', 'murajaah')"
                                    class="py-2 text-xs font-semibold rounded-lg border transition {{ $session_type === 'murajaah' ? 'bg-sky-600 text-white border-sky-600 shadow-sm' : 'bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-800/60' }}"
                                >
                                    Muraja'ah (Mengulang)
                                </button>
                            </div>
                        </div>

                        {{-- Surat Al-Qur'an --}}
                        <div>
                            <x-input-label for="surah_id" value="Surat Al-Qur'an" />
                            <x-select id="surah_id" wire:model.live="surah_id" class="w-full mt-1">
                                @foreach ($surahs as $s)
                                    <option value="{{ $s->id }}">
                                        {{ $s->number }}. {{ $s->name_latin }} ({{ $s->name_arabic }}) - {{ $s->total_verses }} ayat
                                    </option>
                                @endforeach
                            </x-select>
                            <x-input-error :messages="$errors->get('surah_id')" />
                        </div>

                        {{-- Ayat Mulai & Selesai & Juz --}}
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <x-input-label for="verse_start" value="Ayat Mulai" />
                                <x-text-input id="verse_start" type="number" wire:model="verse_start" class="w-full mt-1 font-mono text-center" min="1" />
                            </div>
                            <div>
                                <x-input-label for="verse_end" value="Ayat Selesai" />
                                <x-text-input id="verse_end" type="number" wire:model="verse_end" class="w-full mt-1 font-mono text-center" min="1" />
                            </div>
                            <div>
                                <x-input-label for="juz" value="Juz" />
                                <x-text-input id="juz" type="number" wire:model="juz" class="w-full mt-1 font-mono text-center" min="1" max="30" />
                            </div>
                        </div>

                        {{-- Nilai Kelancaran (Grade) --}}
                        <div>
                            <x-input-label value="Predikat Kelancaran" />
                            <div class="grid grid-cols-4 gap-1.5 mt-1">
                                @foreach ([
                                    'A' => 'A (Mumtaz)',
                                    'B' => 'B (Jayyid J.)',
                                    'C' => 'C (Jayyid)',
                                    'D' => 'D (Maqbul)',
                                ] as $gVal => $gLabel)
                                    <button
                                        type="button"
                                        wire:click="$set('grade', '{{ $gVal }}')"
                                        class="py-2 text-[11px] font-bold rounded-lg border transition {{ $grade === $gVal ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' : 'bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-800/60' }}"
                                    >
                                        {{ $gLabel }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Catatan Tajwid / Makhraj --}}
                        <div>
                            <x-input-label for="tajwid_notes" value="Catatan Tajwid / Makhraj (Opsional)" />
                            <x-textarea
                                id="tajwid_notes"
                                wire:model="tajwid_notes"
                                rows="2"
                                placeholder="Misal: Perhatikan ghunnah pada ayat 4 & makhraj huruf 'ain"
                                class="w-full mt-1 text-xs"
                            />
                        </div>

                        {{-- Checkbox Notifikasi WA --}}
                        <div class="pt-1">
                            <x-checkbox
                                id="send_wa"
                                wire:model="send_wa"
                                label="Kirim Laporan Setoran ke WhatsApp Wali Murid"
                            />
                        </div>

                        @if ($this->canManage())
                            <x-primary-button type="submit" class="w-full justify-center py-2.5">
                                <x-loading-label target="saveSession" label="Simpan Setoran Santri" loading="Menyimpan..." />
                            </x-primary-button>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Tabel Riwayat Setoran Terakhir (7 Kolom) --}}
            <div class="lg:col-span-7 space-y-4">
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/60 p-5 shadow-sm space-y-4">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center justify-between">
                        <span>Riwayat Setoran Terbaru</span>
                        <span class="text-xs text-slate-400 font-normal">10 Sesi Terakhir</span>
                    </h3>

                    <x-table>
                        <x-slot:header>
                            <x-table.th class="w-24">Waktu</x-table.th>
                            <x-table.th>Surat & Ayat</x-table.th>
                            <x-table.th class="text-center">Juz</x-table.th>
                            <x-table.th class="text-center">Nilai</x-table.th>
                            <x-table.th>Catatan Tajwid</x-table.th>
                            <x-table.th class="w-12 text-right"></x-table.th>
                        </x-slot:header>

                        <tbody class="divide-y divide-slate-800/60">
                            @forelse ($recentSessions as $session)
                                <x-table.tr wire:key="session-{{ $session->id }}">
                                    <x-table.td class="text-xs text-slate-600 dark:text-slate-400">
                                        {{ $session->recorded_at->translatedFormat('d/m/Y') }}
                                        <span class="block text-[10px] text-slate-500">{{ $session->recorded_at->format('H:i') }}</span>
                                    </x-table.td>
                                    <x-table.td>
                                        <div class="font-semibold text-slate-900 dark:text-white text-xs">
                                            QS. {{ $session->surah?->name_latin }} ({{ $session->verse_start }}-{{ $session->verse_end }})
                                        </div>
                                        <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium">
                                            {{ $session->session_type_label }}
                                        </span>
                                    </x-table.td>
                                    <x-table.td class="text-center font-mono font-bold text-xs text-slate-700 dark:text-slate-300">
                                        {{ $session->juz }}
                                    </x-table.td>
                                    <x-table.td class="text-center">
                                        <x-badge :color="$session->grade === 'A' ? 'emerald' : ($session->grade === 'B' ? 'sky' : ($session->grade === 'C' ? 'amber' : 'rose'))">
                                            {{ $session->grade }}
                                        </x-badge>
                                    </x-table.td>
                                    <x-table.td class="text-xs text-slate-600 dark:text-slate-400">
                                        {{ $session->tajwid_notes ?? '-' }}
                                    </x-table.td>
                                    <x-table.td class="text-right">
                                        @if ($this->canManage())
                                            <button
                                                type="button"
                                                wire:click="deleteSession({{ $session->id }})"
                                                wire:confirm="Hapus catatan setoran ini?"
                                                class="text-slate-400 hover:text-rose-500 transition p-1"
                                                title="Hapus"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        @endif
                                    </x-table.td>
                                </x-table.tr>
                            @empty
                                <x-table.tr>
                                    <x-table.td colspan="6">
                                        <x-empty-state
                                            icon="book-open"
                                            title="Belum Ada Riwayat Setoran"
                                            description="Setoran hafalan baru atau murajaah santri akan tercatat rapi di sini."
                                        />
                                    </x-table.td>
                                </x-table.tr>
                            @endforelse
                        </tbody>
                    </x-table>
                </div>
            </div>
        </div>
    @endif
</div>
