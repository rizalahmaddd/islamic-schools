<div class="space-y-6">
    {{-- Header & Subtitle --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Inventaris Barang & Aset</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Pencatatan aset fisik sekolah/pesantren, kondisi barang, dan pencetakan stiker label QR.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <x-secondary-button :href="route('inventory.rooms')" wire:navigate>
                <x-icon name="building-2" class="mr-1.5 h-4 w-4" />
                Master Ruangan
            </x-secondary-button>
            @can('manage-inventory')
                <x-primary-button wire:click="openCreateModal">
                    <x-icon name="plus" class="mr-1.5 h-4 w-4" />
                    Tambah Barang Aset
                </x-primary-button>
            @endcan
        </div>
    </div>

    {{-- Filters & Search --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <x-search-input wire:model.live.debounce.300ms="search" placeholder="Cari aset, kode, merk, token..." />
        </div>
        <div>
            <x-select wire:model.live="roomFilter" class="w-full">
                <option value="">Semua Ruangan</option>
                @foreach ($rooms as $r)
                    <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->code }})</option>
                @endforeach
            </x-select>
        </div>
        <div>
            <x-select wire:model.live="categoryFilter" class="w-full">
                <option value="">Semua Kategori</option>
                <option value="mebel">Mebel / Perabot</option>
                <option value="elektronik">Elektronik & IT</option>
                <option value="alat_tulis">Alat Tulis & Kantor</option>
                <option value="buku_referensi">Buku & Referensi</option>
                <option value="peralatan_lab">Peralatan Laboratorium</option>
                <option value="kendaraan">Kendaraan Dinas</option>
                <option value="lainnya">Lainnya</option>
            </x-select>
        </div>
        <div>
            <x-select wire:model.live="conditionFilter" class="w-full">
                <option value="">Semua Kondisi</option>
                <option value="baik">Baik</option>
                <option value="rusak_ringan">Rusak Ringan</option>
                <option value="rusak_berat">Rusak Berat</option>
                <option value="hilang">Hilang</option>
            </x-select>
        </div>
    </div>

    {{-- Data Table --}}
    <x-table :pagination="$items">
        <thead class="bg-slate-950 text-slate-400 font-semibold uppercase tracking-wider border-b border-slate-800">
            <tr>
                <x-table.th sortable field="code">Kode & Label</x-table.th>
                <x-table.th sortable field="name">Nama Barang</x-table.th>
                <x-table.th>Ruangan / Lokasi</x-table.th>
                <x-table.th>Kategori & Merk</x-table.th>
                <x-table.th class="text-center">Kondisi</x-table.th>
                <x-table.th class="text-center">Jumlah</x-table.th>
                <x-table.th class="text-right">Harga Beli</x-table.th>
                <x-table.th class="text-right">Aksi</x-table.th>
            </tr>
        </thead>

        <tbody class="divide-y divide-slate-800/60">
            @forelse ($items as $item)
                @php /** @var \App\Models\InventoryItem $item */ @endphp
                <x-table.tr wire:key="item-{{ $item->id }}">
                    <x-table.td>
                        <div class="font-mono text-xs font-bold text-slate-900 dark:text-white">{{ $item->code }}</div>
                        <div class="font-mono text-[10px] text-slate-400">{{ $item->qr_code_token }}</div>
                    </x-table.td>
                    <x-table.td>
                        <div class="font-medium text-slate-900 dark:text-white">{{ $item->name }}</div>
                        @if($item->source_of_fund)
                            <div class="text-[11px] text-slate-500">Sumber: <span class="uppercase font-semibold">{{ $item->source_of_fund }}</span></div>
                        @endif
                    </x-table.td>
                    <x-table.td>
                        <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                            {{ $item->room?->name ?? 'Gudang' }}
                        </span>
                    </x-table.td>
                    <x-table.td class="text-xs">
                        <div class="font-medium capitalize text-slate-800 dark:text-slate-200">{{ str_replace('_', ' ', $item->category) }}</div>
                        <div class="text-slate-500">{{ $item->brand ?: '-' }}</div>
                    </x-table.td>
                    <x-table.td class="text-center">
                        @if ($item->condition === 'baik')
                            <x-badge color="emerald">Baik</x-badge>
                        @elseif ($item->condition === 'rusak_ringan')
                            <x-badge color="amber">Rusak Ringan</x-badge>
                        @elseif ($item->condition === 'rusak_berat')
                            <x-badge color="rose">Rusak Berat</x-badge>
                        @else
                            <x-badge color="slate">Hilang</x-badge>
                        @endif
                    </x-table.td>
                    <x-table.td class="text-center font-medium text-xs text-slate-900 dark:text-white">
                        {{ $item->quantity }} {{ $item->unit }}
                    </x-table.td>
                    <x-table.td class="text-right font-mono text-xs text-slate-800 dark:text-slate-200">
                        Rp {{ number_format((float) $item->price, 0, ',', '.') }}
                    </x-table.td>
                    <x-table.td class="text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('inventory.print-item-label', $item->id) }}" target="_blank" title="Cetak Stiker Label QR" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-emerald-600 hover:bg-emerald-50 hover:text-emerald-700 dark:text-emerald-400 dark:hover:bg-emerald-950/50">
                                <x-icon name="qr-code" class="h-4 w-4" />
                            </a>
                            @can('manage-inventory')
                                <x-icon-button icon="pencil" label="Ubah Barang" wire:click="openEditModal({{ $item->id }})" />
                                <x-icon-button icon="trash-2" label="Hapus Barang" class="text-rose-600 hover:text-rose-700 dark:text-rose-400" wire:click="confirmDelete({{ $item->id }})" />
                            @endcan
                        </div>
                    </x-table.td>
                </x-table.tr>
            @empty
                <x-table.tr>
                    <x-table.td colspan="8" class="p-0">
                        <x-empty-state
                            icon="cube"
                            title="Belum ada data barang aset"
                            description="Catat aset fisik dan cetak label stiker QR untuk penomoran inventaris sekolah."
                        >
                            @can('manage-inventory')
                                <x-slot:actions>
                                    <x-primary-button wire:click="openCreateModal">
                                        <x-icon name="plus" class="mr-1.5 h-4 w-4" />
                                        Tambah Barang Aset
                                    </x-primary-button>
                                </x-slot:actions>
                            @endcan
                        </x-empty-state>
                    </x-table.td>
                </x-table.tr>
            @endforelse
        </tbody>
    </x-table>

    {{-- Form Modal Tambah/Edit Barang Aset --}}
    <x-record-form-modal
        name="record-form"
        :title="$editingId ? 'Ubah Data Barang Inventaris' : 'Tambah Barang Inventaris Baru'"
        subtitle="Nomor aset & token QR digenerate otomatis oleh sistem."
        icon="cube"
        max-width="2xl"
    >
        <form wire:submit="save" id="record-form" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="name" value="Nama Barang Aset *" />
                    <x-text-input id="name" type="text" class="mt-1 block w-full" wire:model="name" placeholder="Mis. Laptop Lenovo Core i5" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="inventory_room_id" value="Ruangan / Lokasi Simpan *" />
                    <x-select id="inventory_room_id" class="mt-1 block w-full" wire:model="inventory_room_id" required>
                        <option value="">Pilih Ruangan</option>
                        @foreach ($rooms as $r)
                            <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->code }})</option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('inventory_room_id')" class="mt-1" />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <x-input-label for="category" value="Kategori *" />
                    <x-select id="category" class="mt-1 block w-full" wire:model="category" required>
                        <option value="mebel">Mebel / Perabot</option>
                        <option value="elektronik">Elektronik & IT</option>
                        <option value="alat_tulis">Alat Tulis Kantor</option>
                        <option value="buku_referensi">Buku & Referensi</option>
                        <option value="peralatan_lab">Peralatan Laboratorium</option>
                        <option value="kendaraan">Kendaraan Dinas</option>
                        <option value="lainnya">Lainnya</option>
                    </x-select>
                    <x-input-error :messages="$errors->get('category')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="brand" value="Merk / Tipe" />
                    <x-text-input id="brand" type="text" class="mt-1 block w-full" wire:model="brand" placeholder="Mis. Asus / Epson" />
                    <x-input-error :messages="$errors->get('brand')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="condition" value="Kondisi Fisik *" />
                    <x-select id="condition" class="mt-1 block w-full" wire:model="condition" required>
                        <option value="baik">Baik</option>
                        <option value="rusak_ringan">Rusak Ringan</option>
                        <option value="rusak_berat">Rusak Berat</option>
                        <option value="hilang">Hilang</option>
                    </x-select>
                    <x-input-error :messages="$errors->get('condition')" class="mt-1" />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                <div>
                    <x-input-label for="quantity" value="Jumlah *" />
                    <x-text-input id="quantity" type="number" min="1" class="mt-1 block w-full" wire:model="quantity" required />
                    <x-input-error :messages="$errors->get('quantity')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="unit" value="Satuan *" />
                    <x-text-input id="unit" type="text" class="mt-1 block w-full" wire:model="unit" placeholder="unit / set / buah" required />
                    <x-input-error :messages="$errors->get('unit')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="price" value="Harga Satuan (Rp) *" />
                    <x-text-input id="price" type="number" min="0" step="1000" class="mt-1 block w-full" wire:model="price" required />
                    <x-input-error :messages="$errors->get('price')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="source_of_fund" value="Sumber Dana" />
                    <x-select id="source_of_fund" class="mt-1 block w-full" wire:model="source_of_fund">
                        <option value="yayasan">Yayasan / Pesantren</option>
                        <option value="bos">Dana BOS</option>
                        <option value="hibah">Hibah / Wakaf</option>
                        <option value="komite">Komite Sekolah</option>
                        <option value="lainnya">Lainnya</option>
                    </x-select>
                    <x-input-error :messages="$errors->get('source_of_fund')" class="mt-1" />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="purchase_date" value="Tanggal Pembelian" />
                    <x-text-input id="purchase_date" type="date" class="mt-1 block w-full" wire:model="purchase_date" />
                    <x-input-error :messages="$errors->get('purchase_date')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="code" value="Kode Aset (Kosongkan = Otomatis AST-...)" />
                    <x-text-input id="code" type="text" class="mt-1 block w-full uppercase" wire:model="code" placeholder="AST-2026-XXXX" />
                    <x-input-error :messages="$errors->get('code')" class="mt-1" />
                </div>
            </div>

            <div>
                <x-input-label for="notes" value="Catatan / Spesifikasi" />
                <x-textarea id="notes" rows="2" class="mt-1 block w-full" wire:model="notes" placeholder="Catatan nomor seri, garansi, atau spesifikasi barang..." />
                <x-input-error :messages="$errors->get('notes')" class="mt-1" />
            </div>
        </form>

        <x-slot:actions>
            <x-secondary-button wire:click="closeModal">Batal</x-secondary-button>
            <x-primary-button type="submit" form="record-form">
                <x-loading-label target="save" :label="$editingId ? 'Simpan Perubahan' : 'Tambah & Buat QR Label'" />
            </x-primary-button>
        </x-slot:actions>
    </x-record-form-modal>

    {{-- Confirm Delete Modal --}}
    <x-confirm-delete-modal
        title="Hapus Barang Inventaris?"
        description="Data barang aset fisik dan stiker QR terkait akan dihapus secara permanen dari sistem."
    />
</div>
