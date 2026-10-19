<div class="space-y-6">
    {{-- Header & Subtitle --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Kamar & Asrama Santri</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Pengelolaan gedung asrama, kapasitas kamar, penempatan santri, dan pembina musyrif.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @can('manage-boarding')
                <x-secondary-button wire:click="openCreateBuildingModal">
                    <x-icon name="building" class="mr-1.5 h-4 w-4" />
                    + Gedung Asrama
                </x-secondary-button>
                @if($activeTab === 'rooms')
                    <x-primary-button wire:click="openCreateModal">
                        <x-icon name="plus" class="mr-1.5 h-4 w-4" />
                        Tambah Kamar
                    </x-primary-button>
                @else
                    <x-primary-button wire:click="openPlacementModal">
                        <x-icon name="user-plus" class="mr-1.5 h-4 w-4" />
                        Tempatkan Santri
                    </x-primary-button>
                @endif
            @endcan
        </div>
    </div>

    {{-- Tab Segmented & Filters --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <x-segmented>
            <x-tab-button :active="$activeTab === 'rooms'" wire:click="setTab('rooms')">
                <x-icon name="bed" class="mr-1.5 h-4 w-4" />
                Daftar Kamar Asrama
            </x-tab-button>
            <x-tab-button :active="$activeTab === 'placements'" wire:click="setTab('placements')">
                <x-icon name="users" class="mr-1.5 h-4 w-4" />
                Penempatan Santri
            </x-tab-button>
        </x-segmented>

        <div class="flex flex-wrap items-center gap-3">
            <div class="w-full sm:w-64">
                <x-search-input wire:model.live.debounce.300ms="search" placeholder="Cari nomor kamar / santri..." />
            </div>

            <div class="w-44">
                <x-select wire:model.live="buildingFilter">
                    <option value="">Semua Gedung</option>
                    @foreach($buildings as $b)
                        <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->gender === 'male' ? 'Putra' : 'Putri' }})</option>
                    @endforeach
                </x-select>
            </div>

            <div class="w-44">
                <x-select wire:model.live="academicYearFilter">
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->name }} ({{ ucfirst($year->semester) }})</option>
                    @endforeach
                </x-select>
            </div>
        </div>
    </div>

    {{-- Content: TAB 1 ROOMS --}}
    @if($activeTab === 'rooms')
        <x-table :pagination="$rooms">
            <x-slot:header>
                <x-table.th>Gedung Asrama</x-table.th>
                <x-table.th>Nomor / Nama Kamar</x-table.th>
                <x-table.th>Kapasitas & Keterisian</x-table.th>
                <x-table.th>Musyrif Pembina</x-table.th>
                <x-table.th>Keterangan</x-table.th>
                <x-table.th class="text-right">Aksi</x-table.th>
            </x-slot:header>

            @forelse($rooms as $room)
                <x-table.tr wire:key="room-{{ $room->id }}">
                    <x-table.td>
                        <div class="font-medium text-slate-900 dark:text-white">{{ $room->building->name }}</div>
                        <div class="text-xs text-slate-500">{{ $room->building->genderLabel() }}</div>
                    </x-table.td>
                    <x-table.td class="font-semibold text-slate-800 dark:text-slate-100">
                        {{ $room->room_number }}
                    </x-table.td>
                    <x-table.td>
                        @php
                            $occupied = $room->placements_count;
                            $pct = $room->capacity > 0 ? round(($occupied / $room->capacity) * 100) : 0;
                            $isFull = $occupied >= $room->capacity;
                        @endphp
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold {{ $isFull ? 'text-rose-600 dark:text-rose-400' : 'text-slate-700 dark:text-slate-200' }}">
                                {{ $occupied }} / {{ $room->capacity }} Santri
                            </span>
                            @if($isFull)
                                <x-badge color="rose">Penuh</x-badge>
                            @else
                                <x-badge color="emerald">Sisa {{ $room->capacity - $occupied }}</x-badge>
                            @endif
                        </div>
                        <div class="mt-1 h-1.5 w-32 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                            <div class="h-full {{ $isFull ? 'bg-rose-500' : 'bg-emerald-500' }}" style="width: {{ min(100, $pct) }}%"></div>
                        </div>
                    </x-table.td>
                    <x-table.td>
                        @if($room->musyrif)
                            <div class="flex items-center gap-1.5 text-sm text-slate-700 dark:text-slate-200">
                                <x-icon name="user" class="h-4 w-4 text-slate-400" />
                                <span>{{ $room->musyrif->name }}</span>
                            </div>
                        @else
                            <span class="text-xs italic text-slate-400">Belum ditugaskan</span>
                        @endif
                    </x-table.td>
                    <x-table.td class="text-xs text-slate-500">
                        {{ $room->description ?: '-' }}
                    </x-table.td>
                    <x-table.td class="text-right">
                        @can('manage-boarding')
                            <div class="flex items-center justify-end gap-1">
                                <x-icon-button wire:click="openPlacementModal({{ $room->id }})" icon="user-plus" label="Tempatkan Santri" />
                                <x-icon-button wire:click="openEditModal({{ $room->id }})" icon="pencil" label="Edit Kamar" />
                                <x-icon-button wire:click="confirmDelete({{ $room->id }})" icon="trash" label="Hapus Kamar" class="text-rose-600 hover:text-rose-700" />
                            </div>
                        @endcan
                    </x-table.td>
                </x-table.tr>
            @empty
                <x-table.tr>
                    <x-table.td colspan="6">
                        <x-empty-state icon="bed" title="Belum Ada Kamar Asrama" description="Gunakan tombol Tambah Kamar untuk mendaftarkan kamar santri." />
                    </x-table.td>
                </x-table.tr>
            @endforelse
        </x-table>
    @else
        {{-- Content: TAB 2 PLACEMENTS --}}
        <x-table :pagination="$placements">
            <x-slot:header>
                <x-table.th>Santri / Siswa</x-table.th>
                <x-table.th>Gedung & Kamar</x-table.th>
                <x-table.th>Nomor Kasur / Ranjang</x-table.th>
                <x-table.th>Wali Murid & Kontak</x-table.th>
                <x-table.th>Tanggal Penempatan</x-table.th>
                <x-table.th class="text-right">Aksi</x-table.th>
            </x-slot:header>

            @forelse($placements as $placement)
                <x-table.tr wire:key="placement-{{ $placement->id }}">
                    <x-table.td>
                        <div class="font-semibold text-slate-900 dark:text-white">{{ $placement->student->name }}</div>
                        <div class="text-xs text-slate-500">NIS: {{ $placement->student->nis ?: '-' }} · {{ $placement->student->gender === 'male' ? 'Putra' : 'Putri' }}</div>
                    </x-table.td>
                    <x-table.td>
                        <div class="font-medium text-slate-800 dark:text-slate-200">{{ $placement->room->room_number }}</div>
                        <div class="text-xs text-slate-500">{{ $placement->room->building->name }}</div>
                    </x-table.td>
                    <x-table.td>
                        <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                            {{ $placement->bed_number ?: 'Kasur Tidak Dispesifikasi' }}
                        </span>
                    </x-table.td>
                    <x-table.td class="text-sm">
                        <div class="text-slate-800 dark:text-slate-200">{{ $placement->student->guardian?->name ?: '-' }}</div>
                        <div class="text-xs text-slate-500">{{ $placement->student->guardian?->phone ?: '-' }}</div>
                    </x-table.td>
                    <x-table.td class="text-xs text-slate-500">
                        {{ $placement->placed_at?->format('d/m/Y') ?: $placement->created_at->format('d/m/Y') }}
                    </x-table.td>
                    <x-table.td class="text-right">
                        @can('manage-boarding')
                            <x-text-button wire:click="removePlacement({{ $placement->id }})" wire:confirm="Lepas penempatan santri ini dari kamar asrama?" class="text-rose-600 hover:text-rose-700">
                                Lepas dari Kamar
                            </x-text-button>
                        @endcan
                    </x-table.td>
                </x-table.tr>
            @empty
                <x-table.tr>
                    <x-table.td colspan="6">
                        <x-empty-state icon="users" title="Belum Ada Penempatan Santri" description="Gunakan tombol Tempatkan Santri untuk menempatkan santri ke dalam kamar asrama." />
                    </x-table.td>
                </x-table.tr>
            @endforelse
        </x-table>
    @endif

    {{-- MODAL: Form Kamar Asrama --}}
    <x-record-form-modal name="record-form" :title="$editingId ? 'Edit Kamar Asrama' : 'Tambah Kamar Asrama'" subtitle="Kelola gedung, kapasitas ranjang, dan ustadz musyrif pembina kamar." icon="bed" max-width="md">
        <form wire:submit="save" class="space-y-4">
            <div>
                <x-input-label for="dorm_building_id" value="Gedung Asrama" />
                <x-select id="dorm_building_id" wire:model="dorm_building_id" class="mt-1 block w-full">
                    <option value="">Pilih Gedung Asrama</option>
                    @foreach($buildings as $b)
                        <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->gender === 'male' ? 'Santri Putra' : 'Santri Putri' }})</option>
                    @endforeach
                </x-select>
                <x-input-error :messages="$errors->get('dorm_building_id')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="room_number" value="Nomor / Nama Kamar" />
                <x-text-input id="room_number" wire:model="room_number" type="text" class="mt-1 block w-full" placeholder="Contoh: Kamar 101, Kamar Ali bin Abi Thalib" />
                <x-input-error :messages="$errors->get('room_number')" class="mt-1" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="capacity" value="Kapasitas Santri" />
                    <x-text-input id="capacity" wire:model="capacity" type="number" min="1" max="50" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('capacity')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="musyrif_id" value="Musyrif Pembina" />
                    <x-select id="musyrif_id" wire:model="musyrif_id" class="mt-1 block w-full">
                        <option value="">Pilih Musyrif</option>
                        @foreach($musyrikin as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('musyrif_id')" class="mt-1" />
                </div>
            </div>

            <div>
                <x-input-label for="description" value="Keterangan / Fasilitas Kamar" />
                <x-textarea id="description" wire:model="description" rows="2" class="mt-1 block w-full" placeholder="Contoh: Lantai 1 sayap barat, ranjang tingkat, kamar mandi dalam" />
                <x-input-error :messages="$errors->get('description')" class="mt-1" />
            </div>

            <x-modal-actions>
                <x-secondary-button wire:click="closeModal">Batal</x-secondary-button>
                <x-primary-button type="submit">
                    <x-loading-label target="save">Simpan Kamar</x-loading-label>
                </x-primary-button>
            </x-modal-actions>
        </form>
    </x-record-form-modal>

    {{-- MODAL: Form Gedung Asrama --}}
    <x-modal name="building-form" :show="$showBuildingModal" max-width="md">
        <form wire:submit="saveBuilding" class="p-6 space-y-4">
            <x-modal-header title="Tambah Gedung Asrama" subtitle="Daftarkan blok gedung asrama pondok pesantren." icon="building" />

            <div>
                <x-input-label for="building_name" value="Nama Gedung Asrama" />
                <x-text-input id="building_name" wire:model="building_name" type="text" class="mt-1 block w-full" placeholder="Contoh: Gedung Abu Bakar Ash-Shiddiq" />
                <x-input-error :messages="$errors->get('building_name')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="building_gender" value="Peruntukan Santri" />
                <x-select id="building_gender" wire:model="building_gender" class="mt-1 block w-full">
                    <option value="male">Santri Putra (Banin)</option>
                    <option value="female">Santri Putri (Banat)</option>
                </x-select>
                <x-input-error :messages="$errors->get('building_gender')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="building_description" value="Keterangan Lokasi" />
                <x-textarea id="building_description" wire:model="building_description" rows="2" class="mt-1 block w-full" placeholder="Lokasi gedung di lingkungan kampus pesantren..." />
                <x-input-error :messages="$errors->get('building_description')" class="mt-1" />
            </div>

            <x-modal-actions>
                <x-secondary-button x-on:click="$dispatch('close-modal', 'building-form')">Batal</x-secondary-button>
                <x-primary-button type="submit">
                    <x-loading-label target="saveBuilding">Simpan Gedung</x-loading-label>
                </x-primary-button>
            </x-modal-actions>
        </form>
    </x-modal>

    {{-- MODAL: Form Penempatan Santri --}}
    <x-modal name="placement-form" :show="$showPlacementModal" max-width="md">
        <form wire:submit="savePlacement" class="p-6 space-y-4">
            <x-modal-header title="Penempatan Santri di Kamar" subtitle="Alokasikan santri ke kamar asrama dan nomor ranjang." icon="user-plus" />

            <div>
                <x-input-label for="placement_room_id" value="Kamar Asrama" />
                <x-select id="placement_room_id" wire:model="placement_room_id" class="mt-1 block w-full">
                    <option value="">Pilih Kamar Asrama</option>
                    @foreach($allRooms as $r)
                        <option value="{{ $r->id }}">{{ $r->building->name }} - {{ $r->room_number }} (Kapasitas: {{ $r->capacity }} santri)</option>
                    @endforeach
                </x-select>
                <x-input-error :messages="$errors->get('placement_room_id')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="placement_student_id" value="Pilih Santri" />
                <x-select id="placement_student_id" wire:model="placement_student_id" class="mt-1 block w-full">
                    <option value="">Pilih Santri (Belum Ditempatkan)</option>
                    @if(isset($availableStudents))
                        @foreach($availableStudents as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} (NIS: {{ $s->nis ?: '-' }})</option>
                        @endforeach
                    @endif
                </x-select>
                <x-input-error :messages="$errors->get('placement_student_id')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="placement_bed_number" value="Nomor Kasur / Ranjang (Opsional)" />
                <x-text-input id="placement_bed_number" wire:model="placement_bed_number" type="text" class="mt-1 block w-full" placeholder="Contoh: Bed A1, Ranjang Atas No. 3" />
                <x-input-error :messages="$errors->get('placement_bed_number')" class="mt-1" />
            </div>

            <x-modal-actions>
                <x-secondary-button x-on:click="$dispatch('close-modal', 'placement-form')">Batal</x-secondary-button>
                <x-primary-button type="submit">
                    <x-loading-label target="savePlacement">Tempatkan Santri</x-loading-label>
                </x-primary-button>
            </x-modal-actions>
        </form>
    </x-modal>

    {{-- MODAL: Konfirmasi Hapus Kamar --}}
    <x-confirm-delete-modal title="Hapus Kamar Asrama" description="Apakah Anda yakin ingin menghapus kamar ini? Seluruh data penempatan santri di kamar ini akan ikut terhapus." />
</div>
