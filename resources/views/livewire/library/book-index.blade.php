<div class="space-y-6">
    {{-- Header & Subtitle --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Katalog Buku & Kitab Perpustakaan</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Pencatatan kitab kuning, buku teks pelajaran, referensi umum, dan ketersediaan stok pinjam.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <x-secondary-button :href="route('library.circulation')" wire:navigate>
                <x-icon name="arrow-left-right" class="mr-1.5 h-4 w-4" />
                Meja Sirkulasi Pinjam
            </x-secondary-button>
            @can('manage-library')
                <x-primary-button wire:click="openCreateModal">
                    <x-icon name="plus" class="mr-1.5 h-4 w-4" />
                    Tambah Buku/Kitab
                </x-primary-button>
            @endcan
        </div>
    </div>

    {{-- Filters & Search --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div>
            <x-search-input wire:model.live.debounce.300ms="search" placeholder="Cari judul, pengarang, ISBN, rak..." />
        </div>
        <div>
            <x-select wire:model.live="categoryFilter" class="w-full">
                <option value="">Semua Kategori</option>
                <option value="kitab_kuning">Kitab Kuning / Pesantren</option>
                <option value="pelajaran">Buku Teks Pelajaran</option>
                <option value="tahfidz">Al-Qur'an & Tahfidz</option>
                <option value="kamus">Kamus & Ensiklopedia</option>
                <option value="umum">Referensi Umum</option>
                <option value="fiksi">Sastra & Fiksi</option>
                <option value="lainnya">Lainnya</option>
            </x-select>
        </div>
        <div>
            <x-select wire:model.live="availabilityFilter" class="w-full">
                <option value="">Semua Status Ketersediaan</option>
                <option value="available">Tersedia untuk Dipinjam (>0)</option>
                <option value="borrowed">Sedang Habis Terpinjam (=0)</option>
            </x-select>
        </div>
    </div>

    {{-- Data Table --}}
    <x-table :pagination="$books">
        <thead class="bg-slate-950 text-slate-400 font-semibold uppercase tracking-wider border-b border-slate-800">
            <tr>
                <x-table.th sortable field="code">Kode & Barcode</x-table.th>
                <x-table.th sortable field="title">Judul Buku / Kitab</x-table.th>
                <x-table.th>Pengarang / Penerbit</x-table.th>
                <x-table.th>Kategori</x-table.th>
                <x-table.th>Lokasi Rak / No Panggil</x-table.th>
                <x-table.th class="text-center">Ketersediaan Stok</x-table.th>
                <x-table.th class="text-right">Aksi</x-table.th>
            </tr>
        </thead>

        <tbody class="divide-y divide-slate-800/60">
            @forelse ($books as $book)
                @php /** @var \App\Models\LibraryBook $book */ @endphp
                <x-table.tr wire:key="book-{{ $book->id }}">
                    <x-table.td>
                        <div class="font-mono text-xs font-bold text-slate-900 dark:text-white">{{ $book->code }}</div>
                        <div class="font-mono text-[10px] text-slate-400">{{ $book->barcode_token }}</div>
                    </x-table.td>
                    <x-table.td class="font-medium text-slate-900 dark:text-white">
                        <div>{{ $book->title }}</div>
                        @if ($book->isbn)
                            <div class="text-[11px] font-mono text-slate-500">ISBN: {{ $book->isbn }}</div>
                        @endif
                    </x-table.td>
                    <x-table.td class="text-xs">
                        <div class="font-medium text-slate-800 dark:text-slate-200">{{ $book->author ?: '-' }}</div>
                        <div class="text-slate-500">{{ $book->publisher ?: '-' }} {{ $book->publish_year ? "({$book->publish_year})" : '' }}</div>
                    </x-table.td>
                    <x-table.td>
                        <x-badge color="{{ $book->category === 'kitab_kuning' ? 'emerald' : ($book->category === 'pelajaran' ? 'sky' : 'slate') }}">
                            {{ str_replace('_', ' ', $book->category) }}
                        </x-badge>
                    </x-table.td>
                    <x-table.td class="text-xs">
                        <div class="font-medium text-slate-800 dark:text-slate-200">{{ $book->rack_location ?: '-' }}</div>
                        @if ($book->call_number)
                            <div class="font-mono text-[11px] text-slate-500">{{ $book->call_number }}</div>
                        @endif
                    </x-table.td>
                    <x-table.td class="text-center">
                        @if ($book->available_copies > 0)
                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400">
                                {{ $book->available_copies }} / {{ $book->total_copies }} eks
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-700 dark:bg-rose-950/50 dark:text-rose-400">
                                Habis Terpinjam (0/{{ $book->total_copies }})
                            </span>
                        @endif
                    </x-table.td>
                    <x-table.td class="text-right">
                        <div class="flex items-center justify-end gap-1">
                            @can('manage-library')
                                <x-icon-button icon="pencil" label="Ubah Buku" wire:click="openEditModal({{ $book->id }})" />
                                <x-icon-button icon="trash-2" label="Hapus Buku" class="text-rose-600 hover:text-rose-700 dark:text-rose-400" wire:click="confirmDelete({{ $book->id }})" />
                            @endcan
                        </div>
                    </x-table.td>
                </x-table.tr>
            @empty
                <x-table.tr>
                    <x-table.td colspan="7" class="p-0">
                        <x-empty-state
                            icon="book-open"
                            title="Belum ada data buku perpustakaan"
                            description="Tambahkan koleksi kitab kuning, buku teks, atau buku bacaan untuk sirkulasi peminjaman siswa."
                        >
                            @can('manage-library')
                                <x-slot:actions>
                                    <x-primary-button wire:click="openCreateModal">
                                        <x-icon name="plus" class="mr-1.5 h-4 w-4" />
                                        Tambah Buku/Kitab Pertama
                                    </x-primary-button>
                                </x-slot:actions>
                            @endcan
                        </x-empty-state>
                    </x-table.td>
                </x-table.tr>
            @endforelse
        </tbody>
    </x-table>

    {{-- Form Modal Tambah/Edit Buku --}}
    <x-record-form-modal
        name="record-form"
        :title="$editingId ? 'Ubah Data Buku / Kitab' : 'Tambah Buku / Kitab Baru'"
        subtitle="Kelola metadata pustaka dan kuota stok eksemplar fisik."
        icon="book-open"
        max-width="2xl"
    >
        <form wire:submit="save" id="record-form" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="title" value="Judul Buku / Kitab *" />
                    <x-text-input id="title" type="text" class="mt-1 block w-full" wire:model="title" placeholder="Mis. Riyadhus Shalihin" required />
                    <x-input-error :messages="$errors->get('title')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="category" value="Kategori *" />
                    <x-select id="category" class="mt-1 block w-full" wire:model="category" required>
                        <option value="kitab_kuning">Kitab Kuning / Pesantren</option>
                        <option value="pelajaran">Buku Teks Pelajaran</option>
                        <option value="tahfidz">Al-Qur'an & Tahfidz</option>
                        <option value="kamus">Kamus & Ensiklopedia</option>
                        <option value="umum">Referensi Umum</option>
                        <option value="fiksi">Sastra & Fiksi</option>
                        <option value="lainnya">Lainnya</option>
                    </x-select>
                    <x-input-error :messages="$errors->get('category')" class="mt-1" />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <x-input-label for="author" value="Pengarang / Muallif" />
                    <x-text-input id="author" type="text" class="mt-1 block w-full" wire:model="author" placeholder="Mis. Imam An-Nawawi" />
                    <x-input-error :messages="$errors->get('author')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="publisher" value="Penerbit" />
                    <x-text-input id="publisher" type="text" class="mt-1 block w-full" wire:model="publisher" placeholder="Mis. Darul Haq" />
                    <x-input-error :messages="$errors->get('publisher')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="publish_year" value="Tahun Terbit" />
                    <x-text-input id="publish_year" type="number" min="1900" max="{{ now()->year + 1 }}" class="mt-1 block w-full" wire:model="publish_year" placeholder="{{ now()->year }}" />
                    <x-input-error :messages="$errors->get('publish_year')" class="mt-1" />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <x-input-label for="isbn" value="ISBN / Nomor Barcode" />
                    <x-text-input id="isbn" type="text" class="mt-1 block w-full" wire:model="isbn" placeholder="978-602-..." />
                    <x-input-error :messages="$errors->get('isbn')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="call_number" value="Nomor Panggil (Call No)" />
                    <x-text-input id="call_number" type="text" class="mt-1 block w-full font-mono text-xs" wire:model="call_number" placeholder="297.1 NAW r" />
                    <x-input-error :messages="$errors->get('call_number')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="rack_location" value="Lokasi Rak" />
                    <x-text-input id="rack_location" type="text" class="mt-1 block w-full" wire:model="rack_location" placeholder="Mis. Rak A-02" />
                    <x-input-error :messages="$errors->get('rack_location')" class="mt-1" />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="total_copies" value="Total Eksemplar Fisik *" />
                    <x-text-input id="total_copies" type="number" min="1" class="mt-1 block w-full" wire:model="total_copies" required />
                    <x-input-error :messages="$errors->get('total_copies')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="code" value="Kode Buku (Kosongkan = Otomatis BK-...)" />
                    <x-text-input id="code" type="text" class="mt-1 block w-full uppercase" wire:model="code" placeholder="BK-2026-XXXX" />
                    <x-input-error :messages="$errors->get('code')" class="mt-1" />
                </div>
            </div>
        </form>

        <x-slot:actions>
            <x-secondary-button wire:click="closeModal">Batal</x-secondary-button>
            <x-primary-button type="submit" form="record-form">
                <x-loading-label target="save" :label="$editingId ? 'Simpan Perubahan' : 'Tambah ke Katalog'" />
            </x-primary-button>
        </x-slot:actions>
    </x-record-form-modal>

    {{-- Confirm Delete Modal --}}
    <x-confirm-delete-modal
        title="Hapus Buku dari Katalog?"
        description="Buku akan dihapus dari perpustakaan. Pastikan buku tidak sedang dalam sirkulasi peminjaman aktif."
    />
</div>
