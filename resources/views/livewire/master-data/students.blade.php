<div class="space-y-4 sm:space-y-6">
    <div class="flex flex-col 2xl:flex-row 2xl:items-center justify-between gap-3 sm:gap-4">
        <div>
            <p class="text-xs text-slate-400">Direktori induk santri/siswa</p>
        </div>

        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            <x-search-input class="order-first sm:order-none basis-full sm:basis-auto sm:flex-1 lg:flex-none lg:w-64" wire:model.live.debounce.400ms="search" placeholder="Cari nama, NIS, atau wali..." />

            <div class="w-40">
                <x-select wire:model.live="classFilter" id="classFilter">
                    <option value="">Semua Kelas</option>
                    @foreach ($schoolClasses as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </x-select>
            </div>

            <div class="w-32">
                <x-select wire:model.live="genderFilter" id="genderFilter">
                    <option value="">Semua L/P</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </x-select>
            </div>

            <div class="w-32">
                <x-select wire:model.live="statusFilter" id="statusFilter">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="graduated">Lulus</option>
                    <option value="transferred">Pindah</option>
                    <option value="dropped_out">Keluar</option>
                </x-select>
            </div>

            <x-table.export-button action="export" label="Export" />

            @if ($this->canManage())
                <x-secondary-button size="sm" type="button" wire:click="openImportModal" class="shrink-0">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-400"></i>
                    <span>Impor Excel</span>
                </x-secondary-button>

                <x-primary-button size="sm" type="button" @click="$dispatch('open-modal', 'record-form')" wire:click="openCreateModal" class="shrink-0">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Tambah Siswa</span>
                </x-primary-button>
            @endif
        </div>
    </div>

    @if ($students->isEmpty())
        <div class="bg-slate-900/80 rounded-xl border border-slate-800/80 overflow-hidden">
            <x-empty-state
                icon="graduation-cap"
                title="{{ $search || $classFilter || $genderFilter ? 'Tidak ada santri yang cocok dengan filter' : 'Belum ada data santri' }}"
                description="{{ $search ? 'Coba ubah kata kunci atau bersihkan filter pencarian.' : 'Tambahkan data santri baru secara manual atau impor dari file Excel.' }}"
            />
        </div>
    @else
        <x-table :pagination="$students">
            <thead class="bg-slate-950 text-slate-400 font-semibold uppercase tracking-wider border-b border-slate-800">
                <tr>
                    <x-table.th sortable field="nis">NIS</x-table.th>
                    <x-table.th sortable field="name">Nama Santri / Siswa</x-table.th>
                    <x-table.th sortable field="gender">L/P</x-table.th>
                    <x-table.th>Kelas Aktif</x-table.th>
                    <x-table.th>Wali Murid</x-table.th>
                    <x-table.th sortable field="status">Status</x-table.th>
                    <x-table.th align="right">Aksi</x-table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @foreach ($students as $student)
                    <x-table.tr wire:key="student-{{ $student->id }}">
                        <x-table.td class="font-mono text-xs text-slate-400">
                            <div>{{ $student->nis }}</div>
                            @if ($student->nisn)
                                <div class="text-[10px] text-slate-400">NISN: {{ $student->nisn }}</div>
                            @endif
                        </x-table.td>
                        <x-table.td class="font-medium text-emerald-400">
                            <a href="{{ route('master-data.students.show', $student) }}" wire:navigate class="hover:text-emerald-300 transition">
                                {{ $student->name }}
                            </a>
                            @if ($student->nickname)
                                <span class="text-xs text-slate-400 font-normal">({{ $student->nickname }})</span>
                            @endif
                        </x-table.td>
                        <x-table.td>
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-md text-xs font-semibold {{ $student->gender === 'L' ? 'bg-sky-500/10 text-sky-400' : 'bg-rose-500/10 text-rose-400' }}">
                                {{ $student->gender }}
                            </span>
                        </x-table.td>
                        <x-table.td class="text-slate-300">
                            @if ($student->currentClass())
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-800 text-slate-200 border border-slate-700">
                                    {{ $student->currentClass()->name }}
                                </span>
                            @else
                                <span class="text-xs text-slate-400 italic">Belum ada kelas</span>
                            @endif
                        </x-table.td>
                        <x-table.td class="text-slate-300">
                            @if ($student->guardian)
                                <div>{{ $student->guardian->name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $student->guardian->phone }}</div>
                            @else
                                <span class="text-xs text-slate-400">-</span>
                            @endif
                        </x-table.td>
                        <x-table.td>
                            <x-badge :color="$student->statusBadgeColor()">
                                {{ $student->statusLabel() }}
                            </x-badge>
                        </x-table.td>
                        <x-table.td align="right">
                            <div class="flex items-center justify-end gap-1">
                                @if ($this->canManage())
                                    <x-icon-button icon="pencil" :label="'Edit '.$student->name" tone="edit" @click="$dispatch('open-modal', 'record-form')" wire:click="openEditModal({{ $student->id }})" />
                                    <x-icon-button icon="trash" :label="'Hapus '.$student->name" tone="danger" @click="$dispatch('open-modal', 'confirm-delete')" wire:click="confirmDelete({{ $student->id }})" />
                                @endif
                                <a href="{{ route('master-data.students.show', $student) }}" wire:navigate aria-label="Lihat {{ $student->name }}" class="inline-flex items-center justify-center w-7 h-7 rounded-md text-slate-400 hover:text-slate-200">
                                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </x-table.td>
                    </x-table.tr>
                @endforeach
            </tbody>
        </x-table>
    @endif

    {{-- Modal Tambah / Edit Siswa --}}
    <x-record-form-modal
        :title="$editingId ? 'Edit Data Santri' : 'Tambah Santri / Siswa Baru'"
        subtitle="Data identitas siswa, orang tua/wali murid, dan penempatan kelas"
        icon="graduation-cap"
        max-width="4xl"
    >
        <form wire:submit="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                {{-- Kolom Kiri: Biodata Siswa --}}
                <div class="space-y-4 bg-slate-950/40 p-4 rounded-xl border border-slate-800/60">
                    <div class="flex items-center gap-2 pb-1.5 border-b border-slate-800/80">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-400">1. Biodata Santri / Siswa</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <x-input-label for="nis" value="NIS (Nomor Induk) *" />
                            <x-text-input wire:model="nis" id="nis" class="w-full font-mono uppercase" placeholder="Contoh: 20260001" autofocus />
                            <x-input-error :messages="$errors->get('nis')" class="mt-1.5" />
                        </div>
                        <div>
                            <x-input-label for="nisn" value="NISN" />
                            <x-text-input wire:model="nisn" id="nisn" class="w-full font-mono" placeholder="10 Digit NISN" />
                            <x-input-error :messages="$errors->get('nisn')" class="mt-1.5" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="name" value="Nama Lengkap *" />
                        <x-text-input wire:model="name" id="name" class="w-full" placeholder="Nama lengkap siswa sesuai akta" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <x-input-label for="nickname" value="Nama Panggilan" />
                            <x-text-input wire:model="nickname" id="nickname" class="w-full" placeholder="Nama panggilan" />
                            <x-input-error :messages="$errors->get('nickname')" class="mt-1.5" />
                        </div>
                        <div>
                            <x-input-label for="gender" value="Jenis Kelamin *" />
                            <x-select wire:model="gender" id="gender" class="w-full">
                                <option value="L">Laki-laki (Ikhwan)</option>
                                <option value="P">Perempuan (Akhwat)</option>
                            </x-select>
                            <x-input-error :messages="$errors->get('gender')" class="mt-1.5" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <x-input-label for="pob" value="Tempat Lahir" />
                            <x-text-input wire:model="pob" id="pob" class="w-full" placeholder="Kota lahir" />
                            <x-input-error :messages="$errors->get('pob')" class="mt-1.5" />
                        </div>
                        <div>
                            <x-input-label for="dob" value="Tanggal Lahir" />
                            <x-text-input wire:model="dob" id="dob" type="date" class="w-full" />
                            <x-input-error :messages="$errors->get('dob')" class="mt-1.5" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="status" value="Status Keaktifan *" />
                        <x-select wire:model="status" id="status" class="w-full">
                            <option value="active">Aktif</option>
                            <option value="graduated">Lulus</option>
                            <option value="transferred">Pindah Sekolah</option>
                            <option value="dropped_out">Keluar</option>
                        </x-select>
                        <x-input-error :messages="$errors->get('status')" class="mt-1.5" />
                    </div>
                </div>

                {{-- Kolom Kanan: Data Wali & Kelas --}}
                <div class="space-y-4 bg-slate-950/40 p-4 rounded-xl border border-slate-800/60">
                    <div class="flex items-center gap-2 pb-1.5 border-b border-slate-800/80">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-400">2. Data Orang Tua & Kelas</span>
                    </div>

                    <div>
                        <x-input-label for="guardian_name" value="Nama Orang Tua / Wali *" />
                        <x-text-input wire:model="guardian_name" id="guardian_name" class="w-full" placeholder="Nama lengkap wali murid" />
                        <x-input-error :messages="$errors->get('guardian_name')" class="mt-1.5" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <x-input-label for="guardian_phone" value="No. WhatsApp Wali *" />
                            <x-text-input wire:model="guardian_phone" id="guardian_phone" class="w-full font-mono" placeholder="08xxxxxxxxxx" />
                            <x-input-error :messages="$errors->get('guardian_phone')" class="mt-1.5" />
                        </div>
                        <div>
                            <x-input-label for="guardian_relationship" value="Hubungan Wali *" />
                            <x-select wire:model="guardian_relationship" id="guardian_relationship" class="w-full">
                                <option value="Ayah">Ayah Kandung</option>
                                <option value="Ibu">Ibu Kandung</option>
                                <option value="Wali">Wali / Keluarga</option>
                            </x-select>
                            <x-input-error :messages="$errors->get('guardian_relationship')" class="mt-1.5" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="guardian_occupation" value="Pekerjaan Wali" />
                        <x-text-input wire:model="guardian_occupation" id="guardian_occupation" class="w-full" placeholder="Pekerjaan atau profesi wali" />
                        <x-input-error :messages="$errors->get('guardian_occupation')" class="mt-1.5" />
                    </div>

                    <div>
                        <x-input-label for="target_class_id" value="Penempatan Kelas (Tahun Aktif)" />
                        <x-select wire:model="target_class_id" id="target_class_id" class="w-full">
                            <option value="">Pilih Rombongan Belajar</option>
                            @foreach ($schoolClasses as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} (Tingkat {{ $c->grade_level }})</option>
                            @endforeach
                        </x-select>
                        <x-input-error :messages="$errors->get('target_class_id')" class="mt-1.5" />
                    </div>

                    <div>
                        <x-input-label for="address" value="Alamat Tempat Tinggal" />
                        <x-textarea wire:model="address" id="address" rows="2" class="w-full" placeholder="Alamat lengkap domisili santri" />
                        <x-input-error :messages="$errors->get('address')" class="mt-1.5" />
                    </div>
                </div>
            </div>

            <x-modal-actions>
                <x-secondary-button @click="$dispatch('close-modal', 'record-form')">Batal</x-secondary-button>
                <x-primary-button type="submit" wire:loading.attr="disabled">
                    <x-loading-label target="save" :label="$editingId ? 'Simpan Perubahan' : 'Daftarkan Siswa'" />
                </x-primary-button>
            </x-modal-actions>
        </form>
    </x-record-form-modal>

    {{-- Modal Impor Excel --}}
    <x-modal name="import-excel" max-width="2xl">
        <div class="p-6 space-y-5">
            <x-modal-header
                title="Impor Data Santri / Siswa dari Excel"
                subtitle="Unggah berkas spreadsheet untuk menambahkan puluhan atau ratusan siswa secara massal"
                icon="file-spreadsheet"
            />

            <div class="bg-emerald-950/20 border border-emerald-500/20 rounded-xl p-4 flex items-center justify-between gap-4">
                <div>
                    <h4 class="text-sm font-semibold text-emerald-400">Unduh Format Spreadsheet</h4>
                    <p class="text-xs text-slate-400 mt-0.5">Gunakan template resmi agar susunan kolom cocok dengan sistem.</p>
                </div>
                <x-secondary-button size="sm" type="button" wire:click="downloadTemplate" class="shrink-0">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span>Unduh Template .xlsx</span>
                </x-secondary-button>
            </div>

            <form wire:submit="importExcel" class="space-y-4">
                <div>
                    <x-input-label for="importFile" value="Pilih File Excel (.xlsx / .xls) *" />
                    <x-file-input wire:model="importFile" id="importFile" accept=".xlsx,.xls" class="w-full" />
                    <x-input-error :messages="$errors->get('importFile')" class="mt-1.5" />
                </div>

                @if ($importSummary)
                    <div class="rounded-xl border p-4 space-y-3 {{ $importSummary['imported'] > 0 ? 'bg-emerald-950/30 border-emerald-500/30' : 'bg-rose-950/30 border-rose-500/30' }}">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-200">Hasil Impor:</span>
                            <div class="flex gap-3 text-xs">
                                <span class="text-emerald-400 font-bold">✓ {{ $importSummary['imported'] }} Berhasil</span>
                                @if (($importSummary['updated'] ?? 0) > 0)
                                    <span class="text-slate-400">({{ $importSummary['updated'] }} data lama diperbarui)</span>
                                @endif
                                <span class="text-rose-400 font-bold">✗ {{ $importSummary['skipped'] }} Gagal</span>
                            </div>
                        </div>

                        @if (!empty($importSummary['errors']))
                            <div class="mt-2 max-h-48 overflow-y-auto space-y-1.5 text-xs text-rose-300">
                                @foreach ($importSummary['errors'] as $err)
                                    <div class="bg-slate-900/80 p-2 rounded border border-rose-900/50">
                                        <span class="font-bold">Baris {{ $err['row'] }} ({{ $err['name'] ?: $err['nis'] }}):</span>
                                        <span>{{ $err['error'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <x-modal-actions>
                    <x-secondary-button @click="$dispatch('close-modal', 'import-excel')">Tutup</x-secondary-button>
                    <x-primary-button type="submit" wire:loading.attr="disabled">
                        <x-loading-label target="importExcel" label="Mulai Impor Siswa" />
                    </x-primary-button>
                </x-modal-actions>
            </form>
        </div>
    </x-modal>

    <x-confirm-delete-modal
        title="Hapus Data Santri"
        description="Apakah Anda yakin ingin menghapus data santri ini? Seluruh catatan akademik dan absensi siswa akan dinonaktifkan."
    />
</div>
