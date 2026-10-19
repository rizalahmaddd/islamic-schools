<div class="space-y-6">
    {{-- Header & Filter Bar --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900/60 p-4 rounded-2xl border border-slate-800">
        <div class="flex flex-wrap items-center gap-3">
            {{-- Filter Tahun Ajaran --}}
            <div class="w-48">
                <x-select id="academicYearFilter" wire:model.live="academicYearFilter">
                    <option value="">Semua Tahun Ajaran</option>
                    @foreach ($academicYears as $year)
                        <option value="{{ $year->id }}">
                            {{ $year->name }} ({{ ucfirst((string) $year->semester) }})
                            {{ $year->is_active ? '★ Aktif' : '' }}
                        </option>
                    @endforeach
                </x-select>
            </div>

            {{-- Filter Kelas --}}
            <div class="w-48">
                <x-select id="classFilter" wire:model.live="classFilter">
                    <option value="">Semua Rombel</option>
                    @foreach ($schoolClasses as $itemClass)
                        <option value="{{ $itemClass->id }}">{{ $itemClass->name }} (Tingkat {{ $itemClass->grade_level }})</option>
                    @endforeach
                </x-select>
            </div>

            {{-- Filter Guru --}}
            <div class="w-48">
                <x-select id="teacherFilter" wire:model.live="teacherFilter">
                    <option value="">Semua Guru Pengampu</option>
                    @foreach ($teachers as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </x-select>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <x-search-input wire:model.live.debounce.300ms="search" placeholder="Cari mapel atau guru..." />
            @can('academic.assignments.manage')
                <x-primary-button wire:click="openCreateModal" icon="plus">
                    Tambah Penugasan
                </x-primary-button>
            @endcan
        </div>
    </div>

    {{-- Tabel Data Penugasan Mapel --}}
    <x-table :pagination="$assignments">
        <x-slot:header>
            <x-table.th>Rombel / Kelas</x-table.th>
            <x-table.th>Mata Pelajaran</x-table.th>
            <x-table.th>Guru Pengampu</x-table.th>
            <x-table.th class="text-center">KKM / TP</x-table.th>
            <x-table.th class="text-center">Tahun Ajaran</x-table.th>
            <x-table.th class="text-right">Aksi</x-table.th>
        </x-slot:header>

        <tbody class="divide-y divide-slate-800/60">
            @forelse ($assignments as $assignment)
                <x-table.tr>
                    <x-table.td>
                        <div class="font-medium text-slate-100">{{ $assignment->schoolClass->name }}</div>
                        <div class="text-xs text-slate-400">Tingkat {{ $assignment->schoolClass->grade_level }}</div>
                    </x-table.td>

                    <x-table.td>
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-slate-200">{{ $assignment->subject->name }}</span>
                            <span class="font-mono text-xs text-slate-400 bg-slate-800 px-1.5 py-0.5 rounded">
                                {{ $assignment->subject->code }}
                            </span>
                        </div>
                        <div class="text-xs text-slate-400 mt-0.5 capitalize">
                            Kategori: {{ $assignment->subject->category }}
                        </div>
                    </x-table.td>

                    <x-table.td>
                        <div class="font-medium text-slate-200">{{ $assignment->teacher->name }}</div>
                        <div class="text-xs text-slate-400">{{ $assignment->teacher->nip ?: 'NIP: -' }}</div>
                    </x-table.td>

                    <x-table.td class="text-center">
                        <x-badge color="sky">
                            {{ number_format((float) $assignment->passing_grade, 0) }}
                        </x-badge>
                    </x-table.td>

                    <x-table.td class="text-center text-xs text-slate-300">
                        {{ $assignment->academicYear->name }}
                        <span class="text-slate-400">({{ ucfirst((string) $assignment->academicYear->semester) }})</span>
                    </x-table.td>

                    <x-table.td class="text-right">
                        <div class="flex items-center justify-end gap-1">
                            @can('academic.grading.manage')
                                <a
                                    href="{{ route('academic.grade-sheet', ['assignment_id' => $assignment->id]) }}"
                                    class="inline-flex items-center justify-center shrink-0 w-8 h-8 rounded-lg text-emerald-400 hover:text-emerald-300 hover:bg-emerald-500/10 transition"
                                    title="Input Nilai Kelas"
                                >
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </a>
                            @endcan

                            @can('academic.assignments.manage')
                                <x-icon-button
                                    icon="settings"
                                    :label="'Edit Penugasan'"
                                    tone="edit"
                                    wire:click="openEditModal({{ $assignment->id }})"
                                />
                                <x-icon-button
                                    icon="trash-2"
                                    :label="'Hapus Penugasan'"
                                    tone="danger"
                                    wire:click="confirmDelete({{ $assignment->id }})"
                                />
                            @endcan
                        </div>
                    </x-table.td>
                </x-table.tr>
            @empty
                <x-table.tr>
                    <x-table.td colspan="6" class="text-center py-12">
                        <x-empty-state
                            icon="graduation-cap"
                            title="Belum Ada Pembagian Tugas Mengajar"
                            description="Pilih tahun ajaran atau klik tombol 'Tambah Penugasan' untuk mengatur guru pengampu mata pelajaran."
                        />
                    </x-table.td>
                </x-table.tr>
            @endforelse
        </tbody>
    </x-table>

    {{-- Modal Tambah/Edit Penugasan --}}
    <x-record-form-modal
        name="record-form"
        :title="$editingId ? 'Edit Tugas Mengajar' : 'Tambah Tugas Mengajar'"
        :subtitle="'Tetapkan guru pengampu dan nilai KKM mata pelajaran per rombel.'"
        icon="graduation-cap"
        max-width="lg"
    >
        <form wire:submit="save" class="space-y-4">
            <div>
                <x-input-label for="academic_year_id" value="Tahun Ajaran" />
                <x-select id="academic_year_id" wire:model="academic_year_id" class="w-full mt-1">
                    <option value="">Pilih Tahun Ajaran...</option>
                    @foreach ($academicYears as $year)
                        <option value="{{ $year->id }}">
                            {{ $year->name }} ({{ ucfirst((string) $year->semester) }})
                            {{ $year->is_active ? '★ Aktif' : '' }}
                        </option>
                    @endforeach
                </x-select>
                <x-input-error :messages="$errors->get('academic_year_id')" class="mt-1" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="class_id" value="Rombel / Kelas" />
                    <x-select id="class_id" wire:model="class_id" class="w-full mt-1">
                        <option value="">Pilih Kelas...</option>
                        @foreach ($schoolClasses as $itemClass)
                            <option value="{{ $itemClass->id }}">{{ $itemClass->name }} (Tingkat {{ $itemClass->grade_level }})</option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('class_id')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="subject_id" value="Mata Pelajaran" />
                    <x-select id="subject_id" wire:model="subject_id" class="w-full mt-1">
                        <option value="">Pilih Mapel...</option>
                        @foreach ($subjects as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('subject_id')" class="mt-1" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="teacher_id" value="Guru Pengampu" />
                    <x-select id="teacher_id" wire:model="teacher_id" class="w-full mt-1">
                        <option value="">Pilih Guru...</option>
                        @foreach ($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('teacher_id')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="passing_grade" value="Nilai KKM / Kriteria Kelulusan" />
                    <x-text-input
                        id="passing_grade"
                        type="number"
                        step="0.01"
                        wire:model="passing_grade"
                        class="w-full mt-1 font-mono"
                        placeholder="75.00"
                    />
                    <x-input-error :messages="$errors->get('passing_grade')" class="mt-1" />
                </div>
            </div>

            <x-modal-actions>
                <x-secondary-button wire:click="closeModal" type="button">
                    Batal
                </x-secondary-button>
                <x-primary-button type="submit">
                    <x-loading-label target="save">
                        {{ $editingId ? 'Perbarui Penugasan' : 'Simpan Penugasan' }}
                    </x-loading-label>
                </x-primary-button>
            </x-modal-actions>
        </form>
    </x-record-form-modal>

    {{-- Confirm Delete Modal --}}
    <x-confirm-delete-modal
        title="Hapus Penugasan Guru"
        description="Apakah Anda yakin ingin menghapus penugasan mata pelajaran ini? Seluruh data nilai siswa terkait akan ikut terhapus."
    />
</div>
