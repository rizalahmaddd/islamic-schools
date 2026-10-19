<div class="space-y-6">
    {{-- Toolbar Filter Tanggal & Kelas --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/60 p-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3">
            <div>
                <x-input-label for="date" value="Tanggal Mutaba'ah" />
                <x-text-input
                    type="date"
                    id="date"
                    wire:model.live="date"
                    class="text-xs py-1.5 mt-1"
                />
            </div>

            <div>
                <x-input-label for="selectedClassId" value="Rombel / Kelas" />
                <x-select id="selectedClassId" wire:model.live="selectedClassId" class="text-xs py-1.5 mt-1">
                    @foreach ($schoolClasses as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} (Tingkat {{ $c->grade_level }})</option>
                    @endforeach
                </x-select>
            </div>
        </div>

        @if ($this->canManage() && $students->isNotEmpty())
            <div class="self-end sm:self-center">
                <x-primary-button
                    type="button"
                    wire:click="markAllJamaah"
                    size="sm"
                >
                    <i data-lucide="check-check" class="w-4 h-4 mr-1.5 text-emerald-200"></i>
                    <span>Tandai Semua Shalat Berjama'ah</span>
                </x-primary-button>
            </div>
        @endif
    </div>

    {{-- Keterangan Singkat Status Shalat --}}
    <div class="flex flex-wrap items-center gap-3 text-[11px] text-slate-500 dark:text-slate-400">
        <span class="font-medium text-slate-700 dark:text-slate-300">Status Shalat:</span>
        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-emerald-600 inline-block"></span> J = Jama'ah</span>
        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-sky-600 inline-block"></span> M = Munfarid</span>
        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-amber-500 inline-block"></span> U = Udzur (Haid/Sakit)</span>
        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-rose-600 inline-block"></span> A = Alpa / Masbuk</span>
    </div>

    {{-- Tabel Grid Mutabaah Ibadah --}}
    <x-table>
        <x-slot:header>
            <x-table.th class="w-10 text-center">No</x-table.th>
            <x-table.th>Santri / Siswa</x-table.th>
            <x-table.th class="text-center w-16">Subuh</x-table.th>
            <x-table.th class="text-center w-16">Dzuhur</x-table.th>
            <x-table.th class="text-center w-16">Ashar</x-table.th>
            <x-table.th class="text-center w-16">Maghrib</x-table.th>
            <x-table.th class="text-center w-16">Isya</x-table.th>
            <x-table.th class="text-center w-16">Dhuha</x-table.th>
            <x-table.th class="text-center w-16">Tahajjud</x-table.th>
            <x-table.th class="text-center w-24">Tilawah (Hal)</x-table.th>
        </x-slot:header>

        <tbody class="divide-y divide-slate-800/60">
            @forelse ($students as $index => $student)
                @php
                    $stState = $worshipState[$student->id] ?? [
                        'fajr' => 'jamaah', 'dhuhr' => 'jamaah', 'asr' => 'jamaah',
                        'maghrib' => 'jamaah', 'isha' => 'jamaah', 'dhuha' => false,
                        'tahajjud' => false, 'tilawah_pages' => 0, 'fasting_type' => 'none',
                    ];
                @endphp
                <x-table.tr wire:key="student-worship-{{ $student->id }}">
                    <x-table.td class="text-center text-slate-400 text-xs">
                        {{ $index + 1 }}
                    </x-table.td>
                    <x-table.td>
                        <div class="font-semibold text-slate-900 dark:text-white text-xs">{{ $student->name }}</div>
                        <div class="text-[11px] text-slate-500 font-mono">NIS: {{ $student->nis }}</div>
                    </x-table.td>

                    {{-- 5 Waktu Shalat --}}
                    @foreach (['fajr' => 'Subuh', 'dhuhr' => 'Dzuhur', 'asr' => 'Ashar', 'maghrib' => 'Maghrib', 'isha' => 'Isya'] as $prayerKey => $prayerLabel)
                        @php
                            $cur = $stState[$prayerKey];
                            $badgeColor = match ($cur) {
                                'jamaah' => 'emerald',
                                'munfarid' => 'sky',
                                'excused' => 'amber',
                                default => 'rose',
                            };
                            $shortCode = match ($cur) {
                                'jamaah' => 'J',
                                'munfarid' => 'M',
                                'excused' => 'U',
                                default => 'A',
                            };
                            $nextStatus = match ($cur) {
                                'jamaah' => 'munfarid',
                                'munfarid' => 'excused',
                                'excused' => 'missed',
                                default => 'jamaah',
                            };
                        @endphp
                        <x-table.td class="text-center">
                            @if ($this->canManage())
                                <button
                                    type="button"
                                    wire:click="updatePrayerStatus({{ $student->id }}, '{{ $prayerKey }}', '{{ $nextStatus }}')"
                                    class="w-8 h-8 rounded-lg font-bold text-xs flex items-center justify-center transition border {{ $badgeColor === 'emerald' ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : ($badgeColor === 'sky' ? 'bg-sky-500/20 text-sky-400 border-sky-500/30' : ($badgeColor === 'amber' ? 'bg-amber-500/20 text-amber-400 border-amber-500/30' : 'bg-rose-500/20 text-rose-400 border-rose-500/30')) }}"
                                    title="{{ $prayerLabel }}: {{ ucfirst($cur) }} (Klik untuk ubah)"
                                >
                                    {{ $shortCode }}
                                </button>
                            @else
                                <span class="font-bold text-xs">{{ $shortCode }}</span>
                            @endif
                        </x-table.td>
                    @endforeach

                    {{-- Dhuha --}}
                    <x-table.td class="text-center">
                        <button
                            type="button"
                            wire:click="toggleSunnah({{ $student->id }}, 'dhuha')"
                            @disabled(! $this->canManage())
                            class="w-8 h-8 rounded-lg font-bold text-xs flex items-center justify-center transition border {{ $stState['dhuha'] ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-slate-800 text-slate-500 border-slate-700' }}"
                            title="Shalat Dhuha"
                        >
                            {{ $stState['dhuha'] ? '✓' : '—' }}
                        </button>
                    </x-table.td>

                    {{-- Tahajjud --}}
                    <x-table.td class="text-center">
                        <button
                            type="button"
                            wire:click="toggleSunnah({{ $student->id }}, 'tahajjud')"
                            @disabled(! $this->canManage())
                            class="w-8 h-8 rounded-lg font-bold text-xs flex items-center justify-center transition border {{ $stState['tahajjud'] ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-slate-800 text-slate-500 border-slate-700' }}"
                            title="Qiyamul Lail / Tahajjud"
                        >
                            {{ $stState['tahajjud'] ? '✓' : '—' }}
                        </button>
                    </x-table.td>

                    {{-- Tilawah Quran (Halaman) --}}
                    <x-table.td class="text-center">
                        <x-text-input
                            type="number"
                            value="{{ $stState['tilawah_pages'] }}"
                            wire:change="updateTilawah({{ $student->id }}, $event.target.value)"
                            @disabled(! $this->canManage())
                            min="0"
                            class="w-16 text-center font-mono text-xs py-1"
                        />
                    </x-table.td>
                </x-table.tr>
            @empty
                <x-table.tr>
                    <x-table.td colspan="10">
                        <x-empty-state
                            icon="calendar-check"
                            title="Pilih Rombel / Kelas"
                            description="Pilih rombongan belajar di atas untuk mulai mencatat mutaba'ah ibadah santri."
                        />
                    </x-table.td>
                </x-table.tr>
            @endforelse
        </tbody>
    </x-table>
</div>
