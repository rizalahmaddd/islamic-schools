<div class="space-y-4 sm:space-y-6">
    <x-list-toolbar
        description="Daftar rombongan belajar (rombel), tingkat kelas, dan penugasan wali kelas."
        search-placeholder="Cari nama kelas..."
        :can-manage="$this->canManage()"
        :can-export="true"
        add-label="Tambah Kelas"
    >
        <x-slot:filters>
            <div class="w-48">
                <x-select wire:model.live="academicYearFilter" id="academicYearFilter">
                    <option value="">Semua Tahun Ajaran</option>
                    @foreach ($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->name }} ({{ ucfirst($year->semester) }})</option>
                    @endforeach
                </x-select>
            </div>
            <div class="w-36">
                <x-select wire:model.live="gradeFilter" id="gradeFilter">
                    <option value="">Semua Tingkat</option>
                    @foreach (range(7, 12) as $grade)
                        <option value="{{ $grade }}">Kelas {{ $grade }}</option>
                    @endforeach
                </x-select>
            </div>
        </x-slot:filters>
    </x-list-toolbar>

    @if ($schoolClasses->isEmpty())
        <div class="bg-slate-900/80 rounded-xl border border-slate-800/80 overflow-hidden">
            <x-empty-state
                icon="layout-grid"
                title="{{ $search || $academicYearFilter || $gradeFilter ? 'Tidak ada kelas yang cocok dengan filter' : 'Belum ada data kelas' }}"
                description="{{ $search ? 'Coba ubah kata kunci atau filter tahun ajaran.' : 'Tambahkan kelas rombel pertama Anda lewat tombol Tambah Kelas.' }}"
            />
        </div>
    @else
        <x-table :pagination="$schoolClasses">
            <thead class="bg-slate-950 text-slate-400 font-semibold uppercase tracking-wider border-b border-slate-800">
                <tr>
                    <x-table.th sortable field="name">Nama Kelas</x-table.th>
                    <x-table.th sortable field="grade_level">Tingkat</x-table.th>
                    <x-table.th>Tahun Ajaran</x-table.th>
                    <x-table.th>Wali Kelas</x-table.th>
                    <x-table.th>Kapasitas Terisi</x-table.th>
                    <x-table.th align="right">Aksi</x-table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @foreach ($schoolClasses as $class)
                    <x-table.tr wire:key="class-{{ $class->id }}">
                        <x-table.td class="font-medium text-slate-200">
                            {{ $class->name }}
                        </x-table.td>
                        <x-table.td class="text-slate-300">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-800 text-slate-300 border border-slate-700">
                                Tingkat {{ $class->grade_level }}
                            </span>
                        </x-table.td>
                        <x-table.td class="text-slate-300">
                            {{ $class->academicYear ? $class->academicYear->name . ' (' . ucfirst($class->academicYear->semester) . ')' : '-' }}
                        </x-table.td>
                        <x-table.td class="text-slate-200">
                            @if ($class->homeroomTeacher)
                                <div>{{ $class->homeroomTeacher->name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $class->homeroomTeacher->phone }}</div>
                            @else
                                <span class="text-xs text-amber-400/80 italic">Belum ditentukan</span>
                            @endif
                        </x-table.td>
                        <x-table.td class="text-slate-300">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-sm {{ $class->enrollments_count >= $class->capacity ? 'text-rose-400 font-bold' : 'text-slate-200' }}">
                                    {{ $class->enrollments_count }} / {{ $class->capacity }}
                                </span>
                                <span class="text-xs text-slate-400">santri</span>
                            </div>
                        </x-table.td>
                        <x-table.td align="right">
                            <div class="flex items-center justify-end gap-1">
                                @if ($this->canManage())
                                    <x-icon-button icon="pencil" :label="'Edit '.$class->name" tone="edit" @click="$dispatch('open-modal', 'record-form')" wire:click="openEditModal({{ $class->id }})" />
                                    <x-icon-button icon="trash" :label="'Hapus '.$class->name" tone="danger" @click="$dispatch('open-modal', 'confirm-delete')" wire:click="confirmDelete({{ $class->id }})" />
                                @endif
                            </div>
                        </x-table.td>
                    </x-table.tr>
                @endforeach
            </tbody>
        </x-table>
    @endif

    <x-record-form-modal
        :title="$editingId ? 'Edit Data Kelas' : 'Tambah Kelas / Rombel Baru'"
        subtitle="Identitas rombel, tingkat kelas, dan penunjukan wali kelas"
        icon="layout-grid"
        max-width="lg"
    >
        <form wire:submit="save" class="space-y-4">
            <div>
                <x-input-label for="academic_year_id" value="Tahun Ajaran *" />
                <x-select wire:model="academic_year_id" id="academic_year_id" class="w-full">
                    <option value="">Pilih Tahun Ajaran</option>
                    @foreach ($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->name }} ({{ ucfirst($year->semester) }}) {{ $year->is_active ? '— Aktif' : '' }}</option>
                    @endforeach
                </x-select>
                <x-input-error :messages="$errors->get('academic_year_id')" class="mt-1.5" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="name" value="Nama Kelas / Rombel *" />
                    <x-text-input wire:model="name" id="name" class="w-full" placeholder="Contoh: 7A atau 7 Tahfidz" autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
                </div>
                <div>
                    <x-input-label for="grade_level" value="Tingkat / Jenjang *" />
                    <x-select wire:model="grade_level" id="grade_level" class="w-full">
                        @foreach (range(1, 12) as $grade)
                            <option value="{{ $grade }}">Tingkat {{ $grade }}</option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('grade_level')" class="mt-1.5" />
                </div>
            </div>

            <div>
                <x-input-label for="homeroom_teacher_id" value="Wali Kelas Pengampu" />
                <x-select wire:model="homeroom_teacher_id" id="homeroom_teacher_id" class="w-full">
                    <option value="">Pilih Guru Pengampu (Opsional)</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}">{{ $teacher->name }} {{ $teacher->specialization ? '('.$teacher->specialization.')' : '' }}</option>
                    @endforeach
                </x-select>
                <x-input-error :messages="$errors->get('homeroom_teacher_id')" class="mt-1.5" />
            </div>

            <div>
                <x-input-label for="capacity" value="Kapasitas Maksimal Siswa *" />
                <x-text-input wire:model="capacity" id="capacity" type="number" min="1" max="100" class="w-full" />
                <x-input-error :messages="$errors->get('capacity')" class="mt-1.5" />
            </div>

            <x-modal-actions>
                <x-secondary-button @click="$dispatch('close-modal', 'record-form')">Batal</x-secondary-button>
                <x-primary-button type="submit" wire:loading.attr="disabled">
                    <x-loading-label target="save" :label="$editingId ? 'Simpan Perubahan' : 'Tambah Kelas'" />
                </x-primary-button>
            </x-modal-actions>
        </form>
    </x-record-form-modal>

    <x-confirm-delete-modal
        title="Hapus Kelas"
        description="Apakah Anda yakin ingin menghapus rombongan belajar ini? Siswa di dalamnya akan kehilangan penempatan kelas."
    />
</div>
