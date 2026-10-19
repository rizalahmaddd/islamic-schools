<div class="space-y-6">
    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/60 p-5 shadow-sm">
            <span class="text-xs text-slate-500 dark:text-slate-400 block font-medium">Total Catatan Pelanggaran</span>
            <div class="text-2xl font-bold font-mono text-rose-600 dark:text-rose-400 mt-1">
                {{ $totalViolations }} Kasus
            </div>
            <span class="text-[11px] text-slate-400 block mt-1">Akumulasi pembinaan santri</span>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/60 p-5 shadow-sm">
            <span class="text-xs text-slate-500 dark:text-slate-400 block font-medium">Total Catatan Prestasi & Kebaikan</span>
            <div class="text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400 mt-1">
                {{ $totalMerits }} Catatan
            </div>
            <span class="text-[11px] text-slate-400 block mt-1">Poin penghargaan adab & lomba</span>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/60 p-5 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 block font-medium">Total Rekaman Ditampilkan</span>
                <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">
                    {{ $records->total() }}
                </div>
                <span class="text-[11px] text-slate-400 block mt-1">Sesuai filter pencarian</span>
            </div>
            @if ($this->canManage())
                <x-primary-button wire:click="openCreateModal" type="button" size="sm">
                    + Catat Baru
                </x-primary-button>
            @endif
        </div>
    </div>

    {{-- Toolbar Filter --}}
    <x-list-toolbar>
        <x-slot:search>
            <x-search-input wire:model.live.debounce.300ms="search" placeholder="Cari santri, NIS, atau deskripsi..." />
        </x-slot:search>

        <x-slot:filters>
            <div class="flex flex-wrap items-center gap-2">
                <x-select wire:model.live="typeFilter" class="text-xs py-1.5">
                    <option value="all">Semua Jenis</option>
                    <option value="violation">Pelanggaran Disiplin</option>
                    <option value="merit">Prestasi / Kebaikan</option>
                </x-select>

                <x-select wire:model.live="classFilter" class="text-xs py-1.5">
                    <option value="">Semua Rombel</option>
                    @foreach ($schoolClasses as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </x-select>
            </div>
        </x-slot:filters>

        <x-slot:actions>
            @if ($this->canManage())
                <x-primary-button wire:click="openCreateModal" type="button">
                    + Catat Pelanggaran / Prestasi
                </x-primary-button>
            @endif
        </x-slot:actions>
    </x-list-toolbar>

    {{-- Tabel Catatan Disiplin & Prestasi --}}
    <x-table :pagination="$records">
        <x-slot:header>
            <x-table.th class="w-12 text-center">No</x-table.th>
            <x-table.th>Tanggal</x-table.th>
            <x-table.th>Santri / Rombel</x-table.th>
            <x-table.th class="text-center">Jenis</x-table.th>
            <x-table.th class="text-center">Poin</x-table.th>
            <x-table.th>Kategori & Uraian Kejadian</x-table.th>
            <x-table.th>Tindakan Pembinaan</x-table.th>
            <x-table.th>Pencatat</x-table.th>
            <x-table.th class="w-20 text-right">Aksi</x-table.th>
        </x-slot:header>

        <tbody class="divide-y divide-slate-800/60">
            @forelse ($records as $index => $r)
                <x-table.tr wire:key="conduct-{{ $r->id }}">
                    <x-table.td class="text-center text-slate-400 text-xs">
                        {{ $records->firstItem() + $index }}
                    </x-table.td>
                    <x-table.td class="text-xs text-slate-600 dark:text-slate-400">
                        {{ $r->date->format('d/m/Y') }}
                    </x-table.td>
                    <x-table.td>
                        <div class="font-semibold text-slate-900 dark:text-white text-xs">{{ $r->student?->name ?? '-' }}</div>
                        <div class="text-[11px] text-slate-500">NIS: {{ $r->student?->nis }} • {{ $r->student?->currentEnrollment?->schoolClass?->name ?? '-' }}</div>
                    </x-table.td>
                    <x-table.td class="text-center">
                        <x-badge :color="$r->type === 'merit' ? 'emerald' : 'rose'">
                            {{ $r->type === 'merit' ? 'Prestasi' : 'Pelanggaran' }}
                        </x-badge>
                    </x-table.td>
                    <x-table.td class="text-center font-mono font-bold text-xs {{ $r->type === 'merit' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                        {{ $r->type === 'merit' ? '+'.$r->points : '-'.$r->points }}
                    </x-table.td>
                    <x-table.td class="text-xs">
                        <span class="font-medium text-slate-800 dark:text-slate-200 block">{{ $r->category }}</span>
                        <span class="text-slate-600 dark:text-slate-400 text-[11px]">{{ $r->description }}</span>
                    </x-table.td>
                    <x-table.td class="text-xs text-slate-600 dark:text-slate-400">
                        {{ $r->action_taken ?? '—' }}
                    </x-table.td>
                    <x-table.td class="text-xs text-slate-500">
                        {{ $r->recordedBy?->name ?? '-' }}
                    </x-table.td>
                    <x-table.td class="text-right">
                        @if ($this->canManage())
                            <div class="flex items-center justify-end gap-1">
                                <button
                                    type="button"
                                    wire:click="openEditModal({{ $r->id }})"
                                    class="p-1.5 text-slate-500 hover:text-emerald-600 transition"
                                    title="Ubah"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    wire:click="confirmDelete({{ $r->id }})"
                                    class="p-1.5 text-slate-500 hover:text-rose-600 transition"
                                    title="Hapus"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        @endif
                    </x-table.td>
                </x-table.tr>
            @empty
                <x-table.tr>
                    <x-table.td colspan="9">
                        <x-empty-state
                            icon="shield-check"
                            title="Belum Ada Catatan Disiplin / Prestasi"
                            description="Catatan pelanggaran atau prestasi santri akan tercatat secara terpusat di sini."
                        />
                    </x-table.td>
                </x-table.tr>
            @endforelse
        </tbody>
    </x-table>

    {{-- Modal Form Catat Disiplin / Prestasi --}}
    <x-record-form-modal
        name="conduct-form"
        :title="$editingId ? 'Ubah Catatan Adab & Disiplin' : 'Catat Pelanggaran / Prestasi Santri'"
        subtitle="Mencatat poin kedisiplinan dan pembinaan santri di pesantren."
        icon="alert-circle"
        max-width="md"
    >
        <form wire:submit="save" class="space-y-4">
            <div>
                <x-input-label for="student_id" value="Pilih Santri" />
                <x-select id="student_id" wire:model="student_id" class="w-full mt-1">
                    <option value="">Pilih Santri</option>
                    @foreach ($allStudents as $st)
                        <option value="{{ $st->id }}">{{ $st->name }} (NIS: {{ $st->nis }})</option>
                    @endforeach
                </x-select>
                <x-input-error :messages="$errors->get('student_id')" />
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <x-input-label for="date" value="Tanggal Kejadian" />
                    <x-text-input id="date" type="date" wire:model="date" class="w-full mt-1 text-xs" />
                    <x-input-error :messages="$errors->get('date')" />
                </div>
                <div>
                    <x-input-label for="type" value="Jenis Catatan" />
                    <x-select id="type" wire:model.live="type" class="w-full mt-1 text-xs">
                        <option value="violation">Pelanggaran Disiplin</option>
                        <option value="merit">Prestasi / Kebaikan</option>
                    </x-select>
                    <x-input-error :messages="$errors->get('type')" />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <x-input-label for="category" value="Kategori" />
                    <x-select id="category" wire:model="category" class="w-full mt-1 text-xs">
                        @if ($type === 'violation')
                            <option value="Kedisiplinan & Waktu">Kedisiplinan & Waktu</option>
                            <option value="Kerapian & Seragam">Kerapian & Seragam</option>
                            <option value="Bahasa Resmi (Arab/Inggris)">Bahasa Resmi (Arab/Inggris)</option>
                            <option value="Akhlak & Adab Asrama">Akhlak & Adab Asrama</option>
                            <option value="Shalat Berjamaah & Ibadah">Shalat Berjamaah & Ibadah</option>
                            <option value="Pelanggaran Berat">Pelanggaran Berat</option>
                        @else
                            <option value="Prestasi Akademik / Lomba">Prestasi Akademik / Lomba</option>
                            <option value="Tahfidz & Tilawah">Tahfidz & Tilawah</option>
                            <option value="Adab & Keteladanan">Adab & Keteladanan</option>
                            <option value="Bahasa Resmi Aktif">Bahasa Resmi Aktif</option>
                            <option value="Kebersihan & Kerapian">Kebersihan & Kerapian</option>
                        @endif
                    </x-select>
                    <x-input-error :messages="$errors->get('category')" />
                </div>

                <div>
                    <x-input-label for="points" value="Poin {{ $type === 'merit' ? 'Prestasi' : 'Pelanggaran' }}" />
                    <x-text-input id="points" type="number" wire:model="points" min="1" max="100" class="w-full mt-1 font-mono text-center" />
                    <x-input-error :messages="$errors->get('points')" />
                </div>
            </div>

            <div>
                <x-input-label for="description" value="Uraian Kejadian / Keterangan" />
                <x-textarea id="description" wire:model="description" rows="2" placeholder="Jelaskan detail kejadian..." class="w-full mt-1 text-xs" />
                <x-input-error :messages="$errors->get('description')" />
            </div>

            <div>
                <x-input-label for="action_taken" value="Tindakan Pembinaan / Sanksi (Opsional)" />
                <x-text-input id="action_taken" wire:model="action_taken" placeholder="Misal: Teguran lisan, menulis istighfar 100x..." class="w-full mt-1 text-xs" />
                <x-input-error :messages="$errors->get('action_taken')" />
            </div>

            <div class="pt-1">
                <x-checkbox
                    id="send_wa_conduct"
                    wire:model="send_wa"
                    label="Kirim Notifikasi Otomatis ke WhatsApp Wali Murid"
                />
            </div>

            <x-modal-actions>
                <x-secondary-button wire:click="$dispatch('close-modal', 'conduct-form')" type="button">
                    Batal
                </x-secondary-button>
                <x-primary-button type="submit">
                    <x-loading-label target="save" label="Simpan Catatan" loading="Menyimpan..." />
                </x-primary-button>
            </x-modal-actions>
        </form>
    </x-record-form-modal>

    {{-- Confirm Delete Modal --}}
    <x-confirm-delete-modal
        title="Konfirmasi Hapus Catatan"
        description="Apakah Anda yakin ingin menghapus catatan adab & disiplin ini?"
    />
</div>
