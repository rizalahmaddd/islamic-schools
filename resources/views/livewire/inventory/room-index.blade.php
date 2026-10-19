<div class="space-y-6">
    {{-- Header & Subtitle --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Ruangan & Lokasi Aset</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Pengelolaan master ruangan sarana prasarana, gedung, dan penanggung jawab aset inventaris.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <x-secondary-button :href="route('inventory.items')" wire:navigate>
                <x-icon name="cube" class="mr-1.5 h-4 w-4" />
                Daftar Inventaris Barang
            </x-secondary-button>
            @can('manage-inventory')
                <x-primary-button wire:click="openCreateModal">
                    <x-icon name="plus" class="mr-1.5 h-4 w-4" />
                    Tambah Ruangan
                </x-primary-button>
            @endcan
        </div>
    </div>

    {{-- Filters & Search --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:w-80">
            <x-search-input wire:model.live.debounce.300ms="search" placeholder="Cari kode, nama ruangan, gedung, PIC..." />
        </div>
    </div>

    {{-- Data Table --}}
    <x-table :pagination="$rooms">
        <thead class="bg-slate-950 text-slate-400 font-semibold uppercase tracking-wider border-b border-slate-800">
            <tr>
                <x-table.th sortable field="code">Kode Ruang</x-table.th>
                <x-table.th sortable field="name">Nama Ruangan</x-table.th>
                <x-table.th sortable field="building">Gedung / Lokasi</x-table.th>
                <x-table.th>Penanggung Jawab (PIC)</x-table.th>
                <x-table.th class="text-center">Total Barang</x-table.th>
                <x-table.th class="text-right">Valuasi Aset</x-table.th>
                <x-table.th class="text-right">Aksi</x-table.th>
            </tr>
        </thead>

        <tbody class="divide-y divide-slate-800/60">
            @forelse ($rooms as $room)
                @php /** @var \App\Models\InventoryRoom $room */ @endphp
                <x-table.tr wire:key="room-{{ $room->id }}">
                    <x-table.td class="font-mono text-xs font-semibold text-slate-900 dark:text-white">
                        {{ $room->code }}
                    </x-table.td>
                    <x-table.td class="font-medium text-slate-900 dark:text-white">
                        <div>{{ $room->name }}</div>
                        @if($room->description)
                            <div class="text-xs text-slate-500 dark:text-slate-400 line-clamp-1">{{ $room->description }}</div>
                        @endif
                    </x-table.td>
                    <x-table.td>
                        <x-badge color="slate">
                            {{ $room->building ?: 'Gedung Utama' }}
                        </x-badge>
                    </x-table.td>
                    <x-table.td class="text-xs">
                        <div class="font-medium text-slate-800 dark:text-slate-200">{{ $room->pic_name ?: '-' }}</div>
                        @if($room->pic_phone)
                            <div class="text-slate-500">{{ $room->pic_phone }}</div>
                        @endif
                    </x-table.td>
                    <x-table.td class="text-center">
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400">
                            {{ $room->items_count }} item
                        </span>
                    </x-table.td>
                    <x-table.td class="text-right font-mono text-xs font-medium text-slate-800 dark:text-slate-200">
                        Rp {{ number_format((float) ($room->items_sum_price ?? 0), 0, ',', '.') }}
                    </x-table.td>
                    <x-table.td class="text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('inventory.print-room-labels', $room->id) }}" target="_blank" title="Cetak Semua Stiker QR Aset Ruangan Ini" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-800/60 hover:text-slate-100 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200">
                                <x-icon name="qr-code" class="h-4 w-4" />
                            </a>
                            @can('manage-inventory')
                                <x-icon-button icon="pencil" label="Ubah Ruangan" wire:click="openEditModal({{ $room->id }})" />
                                <x-icon-button icon="trash-2" label="Hapus Ruangan" class="text-rose-600 hover:text-rose-700 dark:text-rose-400" wire:click="confirmDelete({{ $room->id }})" />
                            @endcan
                        </div>
                    </x-table.td>
                </x-table.tr>
            @empty
                <x-table.tr>
                    <x-table.td colspan="7" class="p-0">
                        <x-empty-state
                            icon="building-2"
                            title="Belum ada data ruangan aset"
                            description="Tambahkan data ruangan sarana prasarana untuk mulai mengelompokkan inventaris aset fisik."
                        >
                            @can('manage-inventory')
                                <x-slot:actions>
                                    <x-primary-button wire:click="openCreateModal">
                                        <x-icon name="plus" class="mr-1.5 h-4 w-4" />
                                        Tambah Ruangan Sekarang
                                    </x-primary-button>
                                </x-slot:actions>
                            @endcan
                        </x-empty-state>
                    </x-table.td>
                </x-table.tr>
            @endforelse
        </tbody>
    </x-table>

    {{-- Form Modal Tambah/Edit Ruangan --}}
    <x-record-form-modal
        name="record-form"
        :title="$editingId ? 'Ubah Data Ruangan Sarpras' : 'Tambah Ruangan Sarpras Baru'"
        subtitle="Kelola identitas ruangan fisik dan penanggung jawab aset."
        icon="building-2"
        max-width="lg"
    >
        <form wire:submit="save" id="record-form" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="code" value="Kode Ruang *" />
                    <x-text-input id="code" type="text" class="mt-1 block w-full uppercase" wire:model="code" placeholder="Mis. R-LAB-01" required />
                    <x-input-error :messages="$errors->get('code')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="name" value="Nama Ruangan *" />
                    <x-text-input id="name" type="text" class="mt-1 block w-full" wire:model="name" placeholder="Mis. Lab Komputer 1" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
            </div>

            <div>
                <x-input-label for="building" value="Gedung / Lokasi" />
                <x-text-input id="building" type="text" class="mt-1 block w-full" wire:model="building" placeholder="Mis. Gedung Abu Bakar Lt. 2" />
                <x-input-error :messages="$errors->get('building')" class="mt-1" />
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="pic_name" value="Nama Penanggung Jawab (PIC)" />
                    <x-text-input id="pic_name" type="text" class="mt-1 block w-full" wire:model="pic_name" placeholder="Mis. Ust. Ahmad Zaid" />
                    <x-input-error :messages="$errors->get('pic_name')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="pic_phone" value="No. HP / WA PIC" />
                    <x-text-input id="pic_phone" type="text" class="mt-1 block w-full" wire:model="pic_phone" placeholder="Mis. 08123456789" />
                    <x-input-error :messages="$errors->get('pic_phone')" class="mt-1" />
                </div>
            </div>

            <div>
                <x-input-label for="description" value="Keterangan / Catatan Fasilitas" />
                <x-textarea id="description" rows="3" class="mt-1 block w-full" wire:model="description" placeholder="Catatan kapasitas ruangan, fungsi spesifik, atau kelengkapan..." />
                <x-input-error :messages="$errors->get('description')" class="mt-1" />
            </div>
        </form>

        <x-slot:actions>
            <x-secondary-button wire:click="closeModal">Batal</x-secondary-button>
            <x-primary-button type="submit" form="record-form">
                <x-loading-label target="save" :label="$editingId ? 'Simpan Perubahan' : 'Tambah Ruangan'" />
            </x-primary-button>
        </x-slot:actions>
    </x-record-form-modal>

    {{-- Confirm Delete Modal --}}
    <x-confirm-delete-modal
        title="Hapus Ruangan Aset?"
        description="Data ruangan akan dihapus dari sistem. Pastikan ruangan sudah tidak memiliki barang aset inventaris."
    />
</div>
