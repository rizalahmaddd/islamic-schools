<div class="space-y-4 sm:space-y-6">
    <x-list-toolbar
        description="Pengaturan kalender tahun ajaran dan semester aktif sekolah."
        search-placeholder="Cari tahun ajaran atau semester..."
        :can-manage="$this->canManage()"
        :can-export="true"
        add-label="Tambah Tahun Ajaran"
    />

    @if ($academicYears->isEmpty())
        <div class="bg-slate-900/80 rounded-xl border border-slate-800/80 overflow-hidden">
            <x-empty-state
                icon="calendar"
                title="{{ $search ? 'Tidak ada tahun ajaran yang cocok' : 'Belum ada tahun ajaran' }}"
                description="{{ $search ? 'Coba kata kunci lain atau hapus pencarian.' : 'Tambahkan tahun ajaran pertama untuk memulai operasional sekolah.' }}"
            />
        </div>
    @else
        <x-table :pagination="$academicYears">
            <thead class="bg-slate-950 text-slate-400 font-semibold uppercase tracking-wider border-b border-slate-800">
                <tr>
                    <x-table.th sortable field="name">Tahun Ajaran</x-table.th>
                    <x-table.th sortable field="semester">Semester</x-table.th>
                    <x-table.th sortable field="start_date">Periode Mulai</x-table.th>
                    <x-table.th sortable field="end_date">Periode Selesai</x-table.th>
                    <x-table.th sortable field="is_active">Status Aktif</x-table.th>
                    <x-table.th align="right">Aksi</x-table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @foreach ($academicYears as $year)
                    <x-table.tr wire:key="year-{{ $year->id }}">
                        <x-table.td class="font-medium text-slate-200">
                            {{ $year->name }}
                        </x-table.td>
                        <x-table.td class="capitalize text-slate-300">
                            {{ $year->semester }}
                        </x-table.td>
                        <x-table.td class="text-slate-400">
                            {{ $year->start_date?->translatedFormat('d F Y') ?? '-' }}
                        </x-table.td>
                        <x-table.td class="text-slate-400">
                            {{ $year->end_date?->translatedFormat('d F Y') ?? '-' }}
                        </x-table.td>
                        <x-table.td>
                            @if ($year->is_active)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    Aktif Berjalan
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-normal text-slate-400 bg-slate-800/50">
                                    Nonaktif
                                </span>
                            @endif
                        </x-table.td>
                        <x-table.td align="right">
                            <div class="flex items-center justify-end gap-1.5">
                                @if ($this->canManage())
                                    @if (!$year->is_active)
                                        <button
                                            type="button"
                                            wire:click="activateYear({{ $year->id }})"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-emerald-400 hover:text-emerald-300 hover:bg-emerald-500/10 rounded-md transition"
                                            title="Jadikan Tahun Ajaran Aktif"
                                        >
                                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                                            <span>Aktifkan</span>
                                        </button>
                                    @endif
                                    <x-icon-button icon="pencil" :label="'Edit '.$year->name" tone="edit" @click="$dispatch('open-modal', 'record-form')" wire:click="openEditModal({{ $year->id }})" />
                                    <x-icon-button icon="trash" :label="'Hapus '.$year->name" tone="danger" @click="$dispatch('open-modal', 'confirm-delete')" wire:click="confirmDelete({{ $year->id }})" />
                                @endif
                            </div>
                        </x-table.td>
                    </x-table.tr>
                @endforeach
            </tbody>
        </x-table>
    @endif

    <x-record-form-modal
        :title="$editingId ? 'Edit Tahun Ajaran' : 'Tambah Tahun Ajaran Baru'"
        subtitle="Konfigurasi tahun kalender dan periode semester sekolah"
        icon="calendar"
        max-width="lg"
    >
        <form wire:submit="save" class="space-y-4">
            <div>
                <x-input-label for="name" value="Nama Tahun Ajaran *" />
                <x-text-input wire:model="name" id="name" class="w-full" placeholder="Contoh: 2026/2027" autofocus />
                <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
            </div>

            <div>
                <x-input-label for="semester" value="Semester *" />
                <x-select wire:model="semester" id="semester" class="w-full">
                    <option value="ganjil">Semester Ganjil</option>
                    <option value="genap">Semester Genap</option>
                </x-select>
                <x-input-error :messages="$errors->get('semester')" class="mt-1.5" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <x-input-label for="start_date" value="Tanggal Mulai *" />
                    <x-text-input wire:model="start_date" id="start_date" type="date" class="w-full" />
                    <x-input-error :messages="$errors->get('start_date')" class="mt-1.5" />
                </div>
                <div>
                    <x-input-label for="end_date" value="Tanggal Selesai *" />
                    <x-text-input wire:model="end_date" id="end_date" type="date" class="w-full" />
                    <x-input-error :messages="$errors->get('end_date')" class="mt-1.5" />
                </div>
            </div>

            <div class="pt-2">
                <x-checkbox wire:model="is_active" id="is_active" label="Jadikan sebagai Tahun Ajaran Aktif saat ini" />
                <p class="text-[11px] text-slate-400 mt-1 pl-6">Mengaktifkan tahun ini akan otomatis menonaktifkan tahun ajaran lainnya.</p>
            </div>

            <x-modal-actions>
                <x-secondary-button @click="$dispatch('close-modal', 'record-form')">Batal</x-secondary-button>
                <x-primary-button type="submit" wire:loading.attr="disabled">
                    <x-loading-label target="save" :label="$editingId ? 'Simpan Perubahan' : 'Tambah Tahun Ajaran'" />
                </x-primary-button>
            </x-modal-actions>
        </form>
    </x-record-form-modal>

    <x-confirm-delete-modal
        title="Hapus Tahun Ajaran"
        description="Apakah Anda yakin ingin menghapus data tahun ajaran ini? Data rombel dan siswa yang terhubung tidak akan hilang."
    />
</div>
