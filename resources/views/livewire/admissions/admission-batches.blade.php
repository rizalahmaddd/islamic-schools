<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Gelombang PPDB</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Jadwal buka-tutup pendaftaran santri baru, kuota penerimaan, dan pos biaya pendaftaran.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <x-secondary-button :href="route('admissions.candidates')" wire:navigate>
                <x-icon name="arrow-left" class="mr-1.5 h-4 w-4" />
                Data Calon Santri
            </x-secondary-button>
            @can('manage-admissions')
                <x-primary-button wire:click="openCreateModal">
                    <x-icon name="plus" class="mr-1.5 h-4 w-4" />
                    Tambah Gelombang
                </x-primary-button>
            @endcan
        </div>
    </div>

    {{-- Search Toolbar --}}
    <div class="flex items-center justify-between">
        <div class="w-full sm:w-80">
            <x-search-input wire:model.live.debounce.300ms="search" placeholder="Cari nama gelombang..." />
        </div>
    </div>

    {{-- Table of Admission Batches --}}
    <x-table :pagination="$batches">
        <x-slot:header>
            <x-table.th>Gelombang & Tahun Ajaran</x-table.th>
            <x-table.th>Periode Pendaftaran</x-table.th>
            <x-table.th>Kuota & Pendaftar</x-table.th>
            <x-table.th>Biaya Formulir</x-table.th>
            <x-table.th>Status</x-table.th>
            <x-table.th class="text-right">Aksi</x-table.th>
        </x-slot:header>

        @forelse($batches as $batch)
            <x-table.tr wire:key="batch-{{ $batch->id }}">
                <x-table.td>
                    <div class="font-bold text-slate-900 dark:text-white">{{ $batch->name }}</div>
                    <div class="text-xs text-slate-500">Tahun Ajaran: {{ $batch->academicYear->name }} (Gelombang {{ $batch->wave_number }})</div>
                </x-table.td>
                <x-table.td class="text-xs">
                    <div class="text-slate-800 dark:text-slate-200">
                        {{ $batch->start_date->format('d/m/Y') }} s/d {{ $batch->end_date->format('d/m/Y') }}
                    </div>
                    @if($batch->isOpen())
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            Sedang Dibuka
                        </span>
                    @else
                        <span class="text-[11px] text-slate-400">Ditutup</span>
                    @endif
                </x-table.td>
                <x-table.td class="text-xs">
                    <div class="font-semibold text-slate-800 dark:text-slate-100">
                        {{ $batch->candidates_count }} / {{ $batch->quota }} Calon Santri
                    </div>
                    <div class="text-[11px] text-slate-500">
                        Sisa kuota: {{ $batch->remainingQuota() }} kursi
                    </div>
                </x-table.td>
                <x-table.td class="font-mono text-xs font-semibold text-slate-900 dark:text-white">
                    Rp {{ number_format((float) $batch->registration_fee, 0, ',', '.') }}
                </x-table.td>
                <x-table.td>
                    @can('manage-admissions')
                        <button wire:click="toggleActive({{ $batch->id }})" title="Klik untuk ubah status aktif">
                            <x-badge :color="$batch->is_active ? 'emerald' : 'slate'">
                                {{ $batch->is_active ? 'Aktif' : 'Nonaktif' }}
                            </x-badge>
                        </button>
                    @else
                        <x-badge :color="$batch->is_active ? 'emerald' : 'slate'">
                            {{ $batch->is_active ? 'Aktif' : 'Nonaktif' }}
                        </x-badge>
                    @endcan
                </x-table.td>
                <x-table.td class="text-right">
                    @can('manage-admissions')
                        <div class="flex items-center justify-end gap-1">
                            <x-icon-button wire:click="openEditModal({{ $batch->id }})" icon="pencil" label="Edit Gelombang" />
                            <x-icon-button wire:click="confirmDelete({{ $batch->id }})" icon="trash" label="Hapus Gelombang" class="text-rose-600 hover:text-rose-700" />
                        </div>
                    @endcan
                </x-table.td>
            </x-table.tr>
        @empty
            <x-table.tr>
                <x-table.td colspan="6">
                    <x-empty-state icon="calendar" title="Belum Ada Gelombang PPDB" description="Gunakan tombol Tambah Gelombang untuk membuat gelombang pendaftaran baru." />
                </x-table.td>
            </x-table.tr>
        @endforelse
    </x-table>

    {{-- MODAL: Form Gelombang PPDB --}}
    <x-record-form-modal name="record-form" :title="$editingId ? 'Edit Gelombang PPDB' : 'Tambah Gelombang PPDB'" subtitle="Atur periode tanggal pendaftaran santri baru dan kuota penerimaan." icon="calendar" max-width="md">
        <form wire:submit="save" class="space-y-4">
            <div>
                <x-input-label for="academic_year_id" value="Tahun Ajaran Target" />
                <x-select id="academic_year_id" wire:model="academic_year_id" class="mt-1 block w-full">
                    <option value="">Pilih Tahun Ajaran</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->name }} ({{ ucfirst($year->semester) }})</option>
                    @endforeach
                </x-select>
                <x-input-error :messages="$errors->get('academic_year_id')" class="mt-1" />
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div class="col-span-2">
                    <x-input-label for="name" value="Nama Gelombang" />
                    <x-text-input id="name" wire:model="name" type="text" class="mt-1 block w-full" placeholder="Contoh: Gelombang 1 - Jalur Prestasi" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="wave_number" value="Gel. Ke" />
                    <x-text-input id="wave_number" wire:model="wave_number" type="number" min="1" max="10" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('wave_number')" class="mt-1" />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <x-input-label for="start_date" value="Tanggal Buka" />
                    <x-text-input id="start_date" wire:model="start_date" type="date" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('start_date')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="end_date" value="Tanggal Tutup" />
                    <x-text-input id="end_date" wire:model="end_date" type="date" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('end_date')" class="mt-1" />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <x-input-label for="quota" value="Target Kuota (Santri)" />
                    <x-text-input id="quota" wire:model="quota" type="number" min="1" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('quota')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="registration_fee" value="Biaya Formulir (Rp)" />
                    <x-text-input id="registration_fee" wire:model="registration_fee" type="number" min="0" step="1000" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('registration_fee')" class="mt-1" />
                </div>
            </div>

            <div>
                <x-input-label for="description" value="Keterangan / Ketentuan Gelombang" />
                <x-textarea id="description" wire:model="description" rows="2" class="mt-1 block w-full" placeholder="Syarat khusus, jadwal tes seleksi, berkas yang perlu dibawa..." />
                <x-input-error :messages="$errors->get('description')" class="mt-1" />
            </div>

            <div class="pt-1">
                <x-checkbox
                    wire:model="is_active"
                    label="Aktifkan gelombang ini di formulir pendaftaran publik"
                />
            </div>

            <x-modal-actions>
                <x-secondary-button wire:click="closeModal">Batal</x-secondary-button>
                <x-primary-button type="submit">
                    <x-loading-label target="save">Simpan Gelombang</x-loading-label>
                </x-primary-button>
            </x-modal-actions>
        </form>
    </x-record-form-modal>

    {{-- MODAL: Konfirmasi Hapus --}}
    <x-confirm-delete-modal title="Hapus Gelombang PPDB" description="Apakah Anda yakin ingin menghapus gelombang ini? Semua data calon santri yang mendaftar di gelombang ini akan ikut terhapus." />
</div>
