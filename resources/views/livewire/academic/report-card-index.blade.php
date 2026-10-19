<div class="space-y-6">
    {{-- Header & Selector Bar --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900/60 p-4 rounded-2xl border border-slate-800">
        <div class="flex flex-wrap items-center gap-3">
            {{-- Filter Tahun Ajaran --}}
            <div class="w-56">
                <x-select id="academicYearFilter" wire:model.live="academicYearFilter">
                    <option value="">Pilih Tahun Ajaran...</option>
                    @foreach ($academicYears as $year)
                        <option value="{{ $year->id }}">
                            {{ $year->name }} ({{ ucfirst((string) $year->semester) }})
                            {{ $year->is_active ? '★ Aktif' : '' }}
                        </option>
                    @endforeach
                </x-select>
            </div>

            {{-- Filter Kelas --}}
            <div class="w-56">
                <x-select id="classFilter" wire:model.live="classFilter">
                    <option value="">Pilih Rombel / Kelas...</option>
                    @foreach ($schoolClasses as $itemClass)
                        <option value="{{ $itemClass->id }}">{{ $itemClass->name }} (Tingkat {{ $itemClass->grade_level }})</option>
                    @endforeach
                </x-select>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <x-secondary-button wire:click="syncAllAttendance" size="sm" type="button">
                <i data-lucide="refresh-cw" class="w-4 h-4 mr-1.5"></i>
                Sinkron Presensi Harian
            </x-secondary-button>
        </div>
    </div>

    {{-- Tabel Status Rapor Siswa --}}
    <x-table>
        <x-slot:header>
            <x-table.th class="w-12 text-center">No</x-table.th>
            <x-table.th>Santri / Siswa</x-table.th>
            <x-table.th class="text-center">Rata-rata Nilai</x-table.th>
            <x-table.th class="text-center">Presensi (S / I / A)</x-table.th>
            <x-table.th class="text-center">Capaian Tahfidz</x-table.th>
            <x-table.th class="text-center">Status Rapor</x-table.th>
            <x-table.th class="text-right">Aksi</x-table.th>
        </x-slot:header>

        <tbody class="divide-y divide-slate-800/60 text-slate-200">
            @forelse ($studentsData as $index => $item)
                @php
                    $student = $item['student'];
                    $summary = $item['summary'];
                    $avg = $item['averageScore'];
                    $tahfidz = $item['tahfidzProgress'];
                @endphp
                <x-table.tr>
                    <x-table.td class="text-center text-slate-500 font-mono">{{ $index + 1 }}</x-table.td>

                    <x-table.td>
                        <div class="font-medium text-slate-100">{{ $student->name }}</div>
                        <div class="text-xs text-slate-400 font-mono">NIS: {{ $student->nis }}</div>
                    </x-table.td>

                    <x-table.td class="text-center">
                        <span class="font-mono font-bold text-sm {{ $avg >= 75 ? 'text-emerald-400' : ($avg > 0 ? 'text-amber-400' : 'text-slate-500') }}">
                            {{ $avg > 0 ? number_format($avg, 1) : '-' }}
                        </span>
                        <div class="text-[11px] text-slate-400">
                            {{ $item['grades']->count() }} Mapel
                        </div>
                    </x-table.td>

                    <x-table.td class="text-center font-mono text-xs">
                        <span class="text-amber-400" title="Sakit">{{ $summary?->sick_count ?? 0 }} S</span>
                        <span class="text-slate-600 mx-1">/</span>
                        <span class="text-sky-400" title="Izin">{{ $summary?->permit_count ?? 0 }} I</span>
                        <span class="text-slate-600 mx-1">/</span>
                        <span class="text-rose-400 font-bold" title="Alpa">{{ $summary?->absent_count ?? 0 }} A</span>
                    </x-table.td>

                    <x-table.td class="text-center text-xs">
                        <div class="font-semibold text-emerald-400">
                            {{ $tahfidz['completed_juz_count'] }} Juz Mutqin
                        </div>
                        <div class="text-[11px] text-slate-400">
                            {{ $tahfidz['total_sessions'] }} kali setoran
                        </div>
                    </x-table.td>

                    <x-table.td class="text-center">
                        @if ($summary?->is_locked)
                            <x-badge color="rose">Terkunci</x-badge>
                        @else
                            <x-badge color="slate">Draft</x-badge>
                        @endif
                        @if ($summary?->promotion_status)
                            <div class="text-[10px] text-slate-400 mt-1 capitalize">
                                {{ match($summary->promotion_status) {
                                    'promoted' => 'Naik Kelas',
                                    'graduated' => 'Lulus',
                                    'retained' => 'Tinggal Kelas',
                                    default => $summary->promotion_status
                                } }}
                            </div>
                        @endif
                    </x-table.td>

                    <x-table.td class="text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            @can('academic.grading.manage')
                                <x-icon-button
                                    icon="message-square"
                                    :label="'Catatan Wali Kelas'"
                                    tone="edit"
                                    wire:click="editNotes({{ $student->id }})"
                                />
                                <x-icon-button
                                    :icon="$summary?->is_locked ? 'lock' : 'unlock'"
                                    :label="$summary?->is_locked ? 'Buka Kunci Rapor' : 'Kunci Rapor Resmi'"
                                    :tone="$summary?->is_locked ? 'danger' : 'default'"
                                    wire:click="toggleLock({{ $student->id }})"
                                />
                            @endcan

                            @can('academic.report-card.print')
                                <a
                                    href="{{ route('academic.report-cards.print', ['student' => $student->id, 'academic_year_id' => $academicYearFilter]) }}"
                                    target="_blank"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-emerald-400 hover:text-emerald-300 hover:bg-emerald-500/10 rounded-lg border border-emerald-500/20 transition"
                                    title="Cetak E-Rapor PDF"
                                >
                                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                    <span>Cetak</span>
                                </a>
                            @endcan
                        </div>
                    </x-table.td>
                </x-table.tr>
            @empty
                <x-table.tr>
                    <x-table.td colspan="7" class="text-center py-12">
                        <x-empty-state
                            icon="document-text"
                            title="Pilih Kelas & Tahun Ajaran"
                            description="Pilih rombel dan tahun ajaran untuk melihat progres pengisian rapor santri."
                        />
                    </x-table.td>
                </x-table.tr>
            @endforelse
        </tbody>
    </x-table>

    {{-- Modal Catatan Wali Kelas & Keputusan Kenaikan --}}
    <x-record-form-modal
        name="notes-modal"
        :title="'Catatan Rapor: ' . $selectedStudentName"
        :subtitle="'Kelola catatan wali kelas, kegiatan ekstrakurikuler, dan status kenaikan kelas santri.'"
        icon="document-text"
        max-width="lg"
        closeAction="closeNotesModal"
    >
        <form wire:submit="saveNotes" class="space-y-4">
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <x-input-label for="sick_count" value="Sakit (Hari)" />
                    <x-text-input
                        id="sick_count"
                        type="number"
                        min="0"
                        wire:model="sick_count"
                        class="w-full mt-1 text-center font-mono"
                    />
                </div>
                <div>
                    <x-input-label for="permit_count" value="Izin (Hari)" />
                    <x-text-input
                        id="permit_count"
                        type="number"
                        min="0"
                        wire:model="permit_count"
                        class="w-full mt-1 text-center font-mono"
                    />
                </div>
                <div>
                    <x-input-label for="absent_count" value="Alpa (Hari)" />
                    <x-text-input
                        id="absent_count"
                        type="number"
                        min="0"
                        wire:model="absent_count"
                        class="w-full mt-1 text-center font-mono"
                    />
                </div>
            </div>

            <div>
                <x-input-label for="extracurricular_notes" value="Kegiatan Ekstrakurikuler & Organisasi" />
                <x-textarea
                    id="extracurricular_notes"
                    wire:model="extracurricular_notes"
                    rows="2"
                    class="w-full mt-1 text-sm"
                    placeholder="Contoh: Aktif sebagai anggota Pramuka Penggalang dan Muhadhoroh 3 Bahasa."
                />
            </div>

            <div>
                <x-input-label for="homeroom_notes" value="Catatan Wali Kelas" />
                <x-textarea
                    id="homeroom_notes"
                    wire:model="homeroom_notes"
                    rows="3"
                    class="w-full mt-1 text-sm"
                    placeholder="Contoh: Ananda memiliki akhlak terpuji dan kedisiplinan yang tinggi. Tingkatkan terus mutaba'ah dan hafalan Al-Qur'an."
                />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="promotion_status" value="Keputusan Kenaikan / Kelulusan" />
                    <x-select id="promotion_status" wire:model="promotion_status" class="w-full mt-1">
                        <option value="promoted">Naik ke Kelas Berikutnya</option>
                        <option value="graduated">Lulus dari Satuan Pendidikan</option>
                        <option value="retained">Tinggal di Kelas Ini</option>
                    </x-select>
                </div>

                <div class="flex items-center pt-6">
                    <x-checkbox
                        id="is_locked"
                        wire:model="is_locked"
                        label="Kunci Rapor (Cegah Perubahan Nilai)"
                    />
                </div>
            </div>

            <x-modal-actions>
                <x-secondary-button wire:click="closeNotesModal" type="button">
                    Batal
                </x-secondary-button>
                <x-primary-button type="submit">
                    <x-loading-label target="saveNotes">
                        Simpan Catatan
                    </x-loading-label>
                </x-primary-button>
            </x-modal-actions>
        </form>
    </x-record-form-modal>
</div>
