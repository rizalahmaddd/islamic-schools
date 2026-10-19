<div class="space-y-6">
    {{-- Top Bar & Segmented Tabs --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <x-segmented>
            <x-tab-button wire:click="setTab('categories')" :active="$activeTab === 'categories'">
                Kategori Biaya
            </x-tab-button>
            <x-tab-button wire:click="setTab('structures')" :active="$activeTab === 'structures'">
                Struktur Tarif
            </x-tab-button>
            <x-tab-button wire:click="setTab('waivers')" :active="$activeTab === 'waivers'">
                Keringanan & Beasiswa
            </x-tab-button>
        </x-segmented>

        <div class="flex items-center gap-2">
            @if ($this->canManage())
                <button
                    type="button"
                    wire:click="openGenerateModal"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-semibold rounded-lg shadow-sm transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Generate Tagihan Massal
                </button>

                @if ($activeTab === 'categories')
                    <x-primary-button wire:click="openCategoryCreate" type="button" size="sm">
                        + Tambah Kategori
                    </x-primary-button>
                @elseif ($activeTab === 'structures')
                    <x-primary-button wire:click="openStructureCreate" type="button" size="sm">
                        + Tambah Tarif
                    </x-primary-button>
                @elseif ($activeTab === 'waivers')
                    <x-primary-button wire:click="openWaiverCreate" type="button" size="sm">
                        + Tambah Keringanan
                    </x-primary-button>
                @endif
            @endif
        </div>
    </div>

    {{-- Toolbar Pencarian --}}
    <x-list-toolbar>
        <x-slot:search>
            <x-search-input wire:model.live.debounce.300ms="search" placeholder="Cari nama pos biaya / santri..." />
        </x-slot:search>
    </x-list-toolbar>

    {{-- TAB 1: Kategori Biaya --}}
    @if ($activeTab === 'categories')
        <x-table :pagination="$categories">
            <x-slot:header>
                <x-table.th class="w-12 text-center">No</x-table.th>
                <x-table.th>Nama Pos Biaya</x-table.th>
                <x-table.th class="text-center">Tipe Penagihan</x-table.th>
                <x-table.th class="text-center">Status</x-table.th>
                <x-table.th class="w-24 text-right">Aksi</x-table.th>
            </x-slot:header>

            <tbody class="divide-y divide-slate-800/60">
                @forelse ($categories as $index => $cat)
                    <x-table.tr wire:key="cat-{{ $cat->id }}">
                        <x-table.td class="text-center text-slate-400 text-xs">
                            {{ $categories->firstItem() + $index }}
                        </x-table.td>
                        <x-table.td class="font-semibold text-slate-900 dark:text-white text-xs">
                            {{ $cat->name }}
                        </x-table.td>
                        <x-table.td class="text-center">
                            <x-badge :color="$cat->type === 'monthly' ? 'emerald' : 'sky'">
                                {{ $cat->type === 'monthly' ? 'Bulanan (SPP)' : 'Non-Bulanan (Insidental)' }}
                            </x-badge>
                        </x-table.td>
                        <x-table.td class="text-center">
                            <x-badge :color="$cat->is_active ? 'emerald' : 'slate'">
                                {{ $cat->is_active ? 'Aktif' : 'Non-Aktif' }}
                            </x-badge>
                        </x-table.td>
                        <x-table.td class="text-right">
                            @if ($this->canManage())
                                <div class="flex items-center justify-end gap-1">
                                    <button
                                        type="button"
                                        wire:click="openCategoryEdit({{ $cat->id }})"
                                        class="p-1.5 text-slate-500 hover:text-emerald-600 transition"
                                        title="Ubah"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="confirmDelete('category', {{ $cat->id }})"
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
                        <x-table.td colspan="5">
                            <x-empty-state
                                icon="wallet"
                                title="Belum Ada Kategori Biaya"
                                description="Tambahkan pos biaya pendidikan seperti SPP Bulanan, Uang Gedung, atau Biaya Ujian."
                            />
                        </x-table.td>
                    </x-table.tr>
                @endforelse
            </tbody>
        </x-table>
    @endif

    {{-- TAB 2: Struktur Tarif --}}
    @if ($activeTab === 'structures')
        <x-table :pagination="$structures">
            <x-slot:header>
                <x-table.th class="w-12 text-center">No</x-table.th>
                <x-table.th>Tahun Ajaran</x-table.th>
                <x-table.th>Kategori Biaya</x-table.th>
                <x-table.th class="text-center">Jenjang Tingkat</x-table.th>
                <x-table.th class="text-right">Nominal Standar</x-table.th>
                <x-table.th class="w-24 text-right">Aksi</x-table.th>
            </x-slot:header>

            <tbody class="divide-y divide-slate-800/60">
                @forelse ($structures as $index => $struct)
                    <x-table.tr wire:key="struct-{{ $struct->id }}">
                        <x-table.td class="text-center text-slate-400 text-xs">
                            {{ $structures->firstItem() + $index }}
                        </x-table.td>
                        <x-table.td class="text-xs text-slate-700 dark:text-slate-300">
                            {{ $struct->academicYear?->name ?? '-' }}
                        </x-table.td>
                        <x-table.td class="font-semibold text-slate-900 dark:text-white text-xs">
                            {{ $struct->feeCategory?->name ?? '-' }}
                        </x-table.td>
                        <x-table.td class="text-center">
                            <x-badge color="sky">
                                {{ $struct->grade_level ? 'Kelas '.$struct->grade_level : 'Semua Tingkat' }}
                            </x-badge>
                        </x-table.td>
                        <x-table.td class="text-right font-mono font-bold text-slate-900 dark:text-white text-xs">
                            Rp {{ number_format((float) $struct->default_amount, 0, ',', '.') }}
                        </x-table.td>
                        <x-table.td class="text-right">
                            @if ($this->canManage())
                                <div class="flex items-center justify-end gap-1">
                                    <button
                                        type="button"
                                        wire:click="openStructureEdit({{ $struct->id }})"
                                        class="p-1.5 text-slate-500 hover:text-emerald-600 transition"
                                        title="Ubah"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="confirmDelete('structure', {{ $struct->id }})"
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
                        <x-table.td colspan="6">
                            <x-empty-state
                                icon="calculator"
                                title="Belum Ada Struktur Tarif"
                                description="Atur besaran tarif biaya pendidikan berdasarkan tahun ajaran dan jenjang kelas."
                            />
                        </x-table.td>
                    </x-table.tr>
                @endforelse
            </tbody>
        </x-table>
    @endif

    {{-- TAB 3: Keringanan & Beasiswa --}}
    @if ($activeTab === 'waivers')
        <x-table :pagination="$waivers">
            <x-slot:header>
                <x-table.th class="w-12 text-center">No</x-table.th>
                <x-table.th>Siswa / Santri</x-table.th>
                <x-table.th>Pos Biaya</x-table.th>
                <x-table.th class="text-center">Bentuk Keringanan</x-table.th>
                <x-table.th class="text-right">Potongan</x-table.th>
                <x-table.th>Keterangan / Alasan</x-table.th>
                <x-table.th class="w-24 text-right">Aksi</x-table.th>
            </x-slot:header>

            <tbody class="divide-y divide-slate-800/60">
                @forelse ($waivers as $index => $w)
                    <x-table.tr wire:key="waiver-{{ $w->id }}">
                        <x-table.td class="text-center text-slate-400 text-xs">
                            {{ $waivers->firstItem() + $index }}
                        </x-table.td>
                        <x-table.td>
                            <div class="font-semibold text-slate-900 dark:text-white text-xs">{{ $w->student?->name ?? '-' }}</div>
                            <div class="text-[11px] text-slate-500">NIS: {{ $w->student?->nis }} • {{ $w->student?->currentEnrollment?->schoolClass?->name ?? '-' }}</div>
                        </x-table.td>
                        <x-table.td class="text-xs text-slate-700 dark:text-slate-300">
                            {{ $w->feeCategory?->name ?? '-' }}
                        </x-table.td>
                        <x-table.td class="text-center">
                            <x-badge :color="$w->discount_type === 'percentage' ? 'emerald' : 'sky'">
                                {{ $w->discount_type === 'percentage' ? 'Persentase' : 'Nominal Tetap' }}
                            </x-badge>
                        </x-table.td>
                        <x-table.td class="text-right font-mono font-bold text-slate-900 dark:text-white text-xs">
                            @if ($w->discount_type === 'percentage')
                                {{ (float) $w->discount_value }}%
                            @else
                                Rp {{ number_format((float) $w->discount_value, 0, ',', '.') }}
                            @endif
                        </x-table.td>
                        <x-table.td class="text-xs text-slate-600 dark:text-slate-400">
                            {{ $w->reason ?? '-' }}
                        </x-table.td>
                        <x-table.td class="text-right">
                            @if ($this->canManage())
                                <div class="flex items-center justify-end gap-1">
                                    <button
                                        type="button"
                                        wire:click="openWaiverEdit({{ $w->id }})"
                                        class="p-1.5 text-slate-500 hover:text-emerald-600 transition"
                                        title="Ubah"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="confirmDelete('waiver', {{ $w->id }})"
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
                        <x-table.td colspan="7">
                            <x-empty-state
                                icon="award"
                                title="Belum Ada Keringanan / Beasiswa"
                                description="Atur potongan biaya SPP khusus untuk santri berprestasi, yatim, atau dhuafa."
                            />
                        </x-table.td>
                    </x-table.tr>
                @endforelse
            </tbody>
        </x-table>
    @endif

    {{-- Modal Form Kategori Biaya --}}
    <x-record-form-modal
        name="category-form"
        :title="$editingCategoryId ? 'Ubah Kategori Biaya' : 'Tambah Kategori Biaya'"
        subtitle="Pos penagihan biaya pendidikan sekolah atau pesantren."
        icon="wallet"
        max-width="md"
    >
        <form wire:submit="saveCategory" class="space-y-4">
            <div>
                <x-input-label for="category_name" value="Nama Pos Biaya" />
                <x-text-input id="category_name" wire:model="category_name" class="w-full mt-1" placeholder="Contoh: SPP Bulanan" />
                <x-input-error :messages="$errors->get('category_name')" />
            </div>

            <div>
                <x-input-label for="category_type" value="Tipe Penagihan" />
                <x-select id="category_type" wire:model="category_type" class="w-full mt-1">
                    <option value="monthly">Bulanan (Ditagih setiap bulan)</option>
                    <option value="non_monthly">Non-Bulanan (Insidental / Sekali bayar)</option>
                </x-select>
                <x-input-error :messages="$errors->get('category_type')" />
            </div>

            <div class="pt-1">
                <x-checkbox
                    id="category_is_active"
                    wire:model="category_is_active"
                    label="Pos Biaya Aktif Digunakan"
                />
            </div>

            <x-modal-actions>
                <x-secondary-button wire:click="$dispatch('close-modal', 'category-form')" type="button">
                    Batal
                </x-secondary-button>
                <x-primary-button type="submit">
                    <x-loading-label target="saveCategory" label="Simpan Kategori" loading="Menyimpan..." />
                </x-primary-button>
            </x-modal-actions>
        </form>
    </x-record-form-modal>

    {{-- Modal Form Struktur Tarif --}}
    <x-record-form-modal
        name="structure-form"
        :title="$editingStructureId ? 'Ubah Struktur Tarif' : 'Tambah Struktur Tarif'"
        subtitle="Besaran nominal standar tagihan per tahun ajaran dan tingkat."
        icon="calculator"
        max-width="md"
    >
        <form wire:submit="saveStructure" class="space-y-4">
            <div>
                <x-input-label for="structure_academic_year_id" value="Tahun Ajaran" />
                <x-select id="structure_academic_year_id" wire:model="structure_academic_year_id" class="w-full mt-1">
                    @foreach ($academicYears as $ay)
                        <option value="{{ $ay->id }}">{{ $ay->name }} ({{ ucfirst((string) $ay->semester) }})</option>
                    @endforeach
                </x-select>
                <x-input-error :messages="$errors->get('structure_academic_year_id')" />
            </div>

            <div>
                <x-input-label for="structure_fee_category_id" value="Kategori Biaya" />
                <x-select id="structure_fee_category_id" wire:model="structure_fee_category_id" class="w-full mt-1">
                    <option value="">Pilih Kategori</option>
                    @foreach ($feeCategories as $fc)
                        <option value="{{ $fc->id }}">{{ $fc->name }} ({{ $fc->type === 'monthly' ? 'Bulanan' : 'Non-Bulanan' }})</option>
                    @endforeach
                </x-select>
                <x-input-error :messages="$errors->get('structure_fee_category_id')" />
            </div>

            <div>
                <x-input-label for="structure_grade_level" value="Jenjang Tingkat Kelas (Opsional)" />
                <x-select id="structure_grade_level" wire:model="structure_grade_level" class="w-full mt-1">
                    <option value="">Semua Tingkat (Umum)</option>
                    <option value="7">Tingkat 7 (SMP / MTs)</option>
                    <option value="8">Tingkat 8 (SMP / MTs)</option>
                    <option value="9">Tingkat 9 (SMP / MTs)</option>
                    <option value="10">Tingkat 10 (SMA / MA)</option>
                    <option value="11">Tingkat 11 (SMA / MA)</option>
                    <option value="12">Tingkat 12 (SMA / MA)</option>
                </x-select>
                <x-input-error :messages="$errors->get('structure_grade_level')" />
            </div>

            <div>
                <x-input-label for="structure_default_amount" value="Nominal Standar (Rp)" />
                <x-text-input
                    id="structure_default_amount"
                    type="number"
                    wire:model="structure_default_amount"
                    placeholder="Contoh: 350000"
                    class="w-full mt-1 font-mono"
                />
                <x-input-error :messages="$errors->get('structure_default_amount')" />
            </div>

            <x-modal-actions>
                <x-secondary-button wire:click="$dispatch('close-modal', 'structure-form')" type="button">
                    Batal
                </x-secondary-button>
                <x-primary-button type="submit">
                    <x-loading-label target="saveStructure" label="Simpan Tarif" loading="Menyimpan..." />
                </x-primary-button>
            </x-modal-actions>
        </form>
    </x-record-form-modal>

    {{-- Modal Form Keringanan & Beasiswa --}}
    <x-record-form-modal
        name="waiver-form"
        :title="$editingWaiverId ? 'Ubah Keringanan Santri' : 'Tambah Keringanan Santri'"
        subtitle="Alokasi diskon atau beasiswa biaya SPP khusus santri tertentu."
        icon="award"
        max-width="md"
    >
        <form wire:submit="saveWaiver" class="space-y-4">
            <div>
                <x-input-label for="waiver_student_id" value="Pilih Santri / Siswa" />
                <x-select id="waiver_student_id" wire:model="waiver_student_id" class="w-full mt-1">
                    <option value="">Pilih Siswa</option>
                    @foreach ($students as $st)
                        <option value="{{ $st->id }}">{{ $st->name }} (NIS: {{ $st->nis }})</option>
                    @endforeach
                </x-select>
                <x-input-error :messages="$errors->get('waiver_student_id')" />
            </div>

            <div>
                <x-input-label for="waiver_fee_category_id" value="Pos Biaya" />
                <x-select id="waiver_fee_category_id" wire:model="waiver_fee_category_id" class="w-full mt-1">
                    <option value="">Pilih Pos Biaya</option>
                    @foreach ($feeCategories as $fc)
                        <option value="{{ $fc->id }}">{{ $fc->name }}</option>
                    @endforeach
                </x-select>
                <x-input-error :messages="$errors->get('waiver_fee_category_id')" />
            </div>

            <div>
                <x-input-label for="waiver_academic_year_id" value="Tahun Ajaran" />
                <x-select id="waiver_academic_year_id" wire:model="waiver_academic_year_id" class="w-full mt-1">
                    @foreach ($academicYears as $ay)
                        <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                    @endforeach
                </x-select>
                <x-input-error :messages="$errors->get('waiver_academic_year_id')" />
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <x-input-label for="waiver_discount_type" value="Bentuk Potongan" />
                    <x-select id="waiver_discount_type" wire:model="waiver_discount_type" class="w-full mt-1">
                        <option value="percentage">Persentase (%)</option>
                        <option value="fixed">Nominal Tetap (Rp)</option>
                    </x-select>
                </div>
                <div>
                    <x-input-label for="waiver_discount_value" value="Nilai Potongan" />
                    <x-text-input
                        id="waiver_discount_value"
                        type="number"
                        wire:model="waiver_discount_value"
                        placeholder="Mis: 50 atau 150000"
                        class="w-full mt-1 font-mono"
                    />
                </div>
            </div>
            <x-input-error :messages="$errors->get('waiver_discount_value')" />

            <div>
                <x-input-label for="waiver_reason" value="Alasan / Jenis Beasiswa (Opsional)" />
                <x-text-input
                    id="waiver_reason"
                    type="text"
                    wire:model="waiver_reason"
                    placeholder="Contoh: Beasiswa Tahfidz 30 Juz / Anak Yatim"
                    class="w-full mt-1"
                />
            </div>

            <x-modal-actions>
                <x-secondary-button wire:click="$dispatch('close-modal', 'waiver-form')" type="button">
                    Batal
                </x-secondary-button>
                <x-primary-button type="submit">
                    <x-loading-label target="saveWaiver" label="Simpan Keringanan" loading="Menyimpan..." />
                </x-primary-button>
            </x-modal-actions>
        </form>
    </x-record-form-modal>

    {{-- Modal Generate Tagihan Massal --}}
    <x-record-form-modal
        name="generate-bills-modal"
        title="Generate Tagihan Bulanan Siswa"
        subtitle="Membuat tagihan otomatis untuk seluruh santri aktif sesuai tarif dan keringanan."
        icon="refresh-cw"
        max-width="md"
    >
        <form wire:submit="runGenerateBills" class="space-y-4">
            <div>
                <x-input-label for="gen_academic_year_id" value="Tahun Ajaran" />
                <x-select id="gen_academic_year_id" wire:model="gen_academic_year_id" class="w-full mt-1">
                    @foreach ($academicYears as $ay)
                        <option value="{{ $ay->id }}">{{ $ay->name }} ({{ ucfirst((string) $ay->semester) }})</option>
                    @endforeach
                </x-select>
                <x-input-error :messages="$errors->get('gen_academic_year_id')" />
            </div>

            <div>
                <x-input-label for="gen_fee_category_id" value="Pos Biaya Bulanan" />
                <x-select id="gen_fee_category_id" wire:model="gen_fee_category_id" class="w-full mt-1">
                    @foreach ($feeCategories->where('type', 'monthly') as $fc)
                        <option value="{{ $fc->id }}">{{ $fc->name }}</option>
                    @endforeach
                </x-select>
                <x-input-error :messages="$errors->get('gen_fee_category_id')" />
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <x-input-label for="gen_month" value="Bulan Tagihan" />
                    <x-select id="gen_month" wire:model="gen_month" class="w-full mt-1">
                        @foreach (\App\Models\StudentBill::MONTH_NAMES as $num => $name)
                            <option value="{{ $num }}">{{ $name }}</option>
                        @endforeach
                    </x-select>
                </div>
                <div>
                    <x-input-label for="gen_year" value="Tahun" />
                    <x-text-input id="gen_year" type="number" wire:model="gen_year" class="w-full mt-1 font-mono" />
                </div>
            </div>

            <div class="p-3 bg-amber-50 dark:bg-amber-950/30 rounded-lg text-xs text-amber-800 dark:text-amber-300">
                Sistem akan membuat tagihan secara otomatis untuk seluruh santri aktif. Santri yang sudah memiliki tagihan untuk bulan ini tidak akan diduplikasi (aman dari duplikasi).
            </div>

            <x-modal-actions>
                <x-secondary-button wire:click="closeGenerateModal" type="button">
                    Batal
                </x-secondary-button>
                <x-primary-button type="submit">
                    <x-loading-label target="runGenerateBills" label="Jalankan Generator" loading="Membuat Tagihan..." />
                </x-primary-button>
            </x-modal-actions>
        </form>
    </x-record-form-modal>

    {{-- Modal Konfirmasi Hapus --}}
    <x-confirm-delete-modal
        title="Konfirmasi Hapus Data"
        description="Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan."
    />
</div>
