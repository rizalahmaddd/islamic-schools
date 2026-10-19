<div class="space-y-6">
    {{-- Header & Selector Bar --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900/60 p-4 rounded-2xl border border-slate-800">
        <div class="flex flex-wrap items-center gap-4">
            <div class="w-72 md:w-96">
                <x-input-label for="selectedAssignmentId" value="Pilih Rombel & Mata Pelajaran" class="sr-only" />
                <x-select id="selectedAssignmentId" wire:model.live="selectedAssignmentId" class="w-full">
                    <option value="">Pilih Mapel & Kelas...</option>
                    @foreach ($assignments as $a)
                        <option value="{{ $a->id }}">
                            {{ $a->schoolClass->name }} — {{ $a->subject->name }} ({{ $a->teacher->name }})
                        </option>
                    @endforeach
                </x-select>
            </div>

            @if ($currentAssignment)
                <div class="flex items-center gap-2 text-xs text-slate-300 bg-slate-800/80 px-3 py-1.5 rounded-lg border border-slate-700/60">
                    <span class="text-slate-400">KKM:</span>
                    <strong class="text-sky-400 font-mono">{{ number_format((float) $currentAssignment->passing_grade, 0) }}</strong>
                    <span class="text-slate-600">|</span>
                    <span class="text-slate-400">Guru:</span>
                    <span class="font-medium text-slate-200">{{ $currentAssignment->teacher->name }}</span>
                </div>
            @endif
        </div>

        @if ($currentAssignment && count($rows) > 0)
            <div class="flex items-center gap-2">
                <x-secondary-button wire:click="generateAllDescriptions" size="sm" type="button">
                    <i data-lucide="file-text" class="w-4 h-4 mr-1.5 text-amber-400"></i>
                    Auto Teks Capaian
                </x-secondary-button>

                @can('academic.grading.manage')
                    <x-primary-button wire:click="saveAll" size="sm" type="button">
                        <x-loading-label target="saveAll">
                            <i data-lucide="check" class="w-4 h-4 mr-1.5"></i>
                            Simpan Semua Nilai
                        </x-loading-label>
                    </x-primary-button>
                @endcan
            </div>
        @endif
    </div>

    @if ($errors->any())
        <div role="alert" class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-3 text-xs text-rose-300 space-y-1">
            <p class="font-semibold">Nilai belum tersimpan. Perbaiki isian berikut:</p>
            <ul class="list-disc pl-4">
                @foreach (collect($errors->all())->unique()->take(5) as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Grid Ledger / Spreadsheet Table --}}
    @if ($currentAssignment && count($rows) > 0)
        <div class="overflow-x-auto bg-slate-900/60 rounded-2xl border border-slate-800">
            <table class="w-full text-xs text-left border-collapse">
                <thead class="bg-slate-800/90 text-slate-200 uppercase font-semibold text-[11px] border-b border-slate-700">
                    <tr>
                        <th class="p-3 w-12 text-center">No</th>
                        <th class="p-3 w-48">Nama Santri</th>
                        <th class="p-3 w-28 text-center">Formatif (50%)</th>
                        <th class="p-3 w-28 text-center">UTS (25%)</th>
                        <th class="p-3 w-28 text-center">UAS (25%)</th>
                        <th class="p-3 w-24 text-center">Nilai Akhir</th>
                        <th class="p-3 w-16 text-center">Predikat</th>
                        <th class="p-3 min-w-[280px]">Deskripsi Capaian Kompetensi (Rapor)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-200">
                    @php $no = 1; @endphp
                    @foreach ($rows as $studentId => $row)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="p-3 text-center text-slate-500 font-mono">{{ $no++ }}</td>
                            <td class="p-3">
                                <div class="font-medium text-slate-100 text-sm">{{ $row['name'] }}</div>
                                <div class="font-mono text-[11px] text-slate-400">NIS: {{ $row['nis'] }}</div>
                            </td>

                            {{-- Formatif --}}
                            <td class="p-2 text-center">
                                <x-text-input
                                    type="number"
                                    step="0.1"
                                    min="0"
                                    max="100"
                                    wire:model.live.debounce.400ms="rows.{{ $studentId }}.formative_average"
                                    class="w-20 text-center font-mono text-sm py-1 px-2"
                                    placeholder="0"
                                />
                            </td>

                            {{-- UTS --}}
                            <td class="p-2 text-center">
                                <x-text-input
                                    type="number"
                                    step="0.1"
                                    min="0"
                                    max="100"
                                    wire:model.live.debounce.400ms="rows.{{ $studentId }}.mid_term_score"
                                    class="w-20 text-center font-mono text-sm py-1 px-2"
                                    placeholder="0"
                                />
                            </td>

                            {{-- UAS --}}
                            <td class="p-2 text-center">
                                <x-text-input
                                    type="number"
                                    step="0.1"
                                    min="0"
                                    max="100"
                                    wire:model.live.debounce.400ms="rows.{{ $studentId }}.final_term_score"
                                    class="w-20 text-center font-mono text-sm py-1 px-2"
                                    placeholder="0"
                                />
                            </td>

                            {{-- Nilai Akhir --}}
                            <td class="p-3 text-center">
                                <span class="font-mono text-sm font-bold {{ (float) $row['final_score'] >= (float) $currentAssignment->passing_grade ? 'text-emerald-400' : 'text-rose-400' }}">
                                    {{ $row['final_score'] ?: '0.00' }}
                                </span>
                            </td>

                            {{-- Predikat --}}
                            <td class="p-3 text-center">
                                <x-badge :color="match ($row['predicate']) {
                                    'A' => 'emerald',
                                    'B' => 'sky',
                                    'C' => 'amber',
                                    default => 'rose'
                                }">
                                    {{ $row['predicate'] }}
                                </x-badge>
                            </td>

                            {{-- Deskripsi Capaian --}}
                            <td class="p-2">
                                <div class="space-y-1">
                                    <x-text-input
                                        type="text"
                                        wire:model="rows.{{ $studentId }}.achievement_desc"
                                        class="w-full text-xs py-1 px-2 text-slate-300"
                                        placeholder="Capaian pembelajaran..."
                                    />
                                    <x-text-input
                                        type="text"
                                        wire:model="rows.{{ $studentId }}.improvement_desc"
                                        class="w-full text-xs py-1 px-2 text-slate-400 bg-slate-950/40"
                                        placeholder="Saran peningkatan..."
                                    />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @elseif (! $currentAssignment)
        <div class="bg-slate-900/60 rounded-2xl border border-slate-800 p-12 text-center">
            <x-empty-state
                icon="document-chart-bar"
                title="Pilih Mata Pelajaran Terlebih Dahulu"
                description="Gunakan dropdown di atas untuk memilih rombel dan mata pelajaran yang akan diisi nilainya."
            />
        </div>
    @else
        <div class="bg-slate-900/60 rounded-2xl border border-slate-800 p-12 text-center">
            <x-empty-state
                icon="user-group"
                title="Belum Ada Santri Terdaftar di Rombel Ini"
                description="Silakan periksa data pendaftaran siswa aktif di menu Master Data Rombel / Kelas."
            />
        </div>
    @endif
</div>
