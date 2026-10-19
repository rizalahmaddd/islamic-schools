<div class="space-y-4 sm:space-y-6">
    <x-list-toolbar
        description="Direktori ustadz, guru pengampu, dan staf kependidikan."
        search-placeholder="Cari nama, NIP, atau mata pelajaran..."
        :can-manage="$this->canManage()"
        :can-export="true"
        add-label="Tambah Guru / Staf"
    >
        <x-slot:filters>
            <div class="w-36">
                <x-select wire:model.live="genderFilter" id="genderFilter">
                    <option value="">Semua Gender</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </x-select>
            </div>
            <div class="w-36">
                <x-select wire:model.live="statusFilter" id="statusFilter">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </x-select>
            </div>
        </x-slot:filters>
    </x-list-toolbar>

    @if ($teachers->isEmpty())
        <div class="bg-slate-900/80 rounded-xl border border-slate-800/80 overflow-hidden">
            <x-empty-state
                icon="briefcase"
                title="{{ $search || $genderFilter || $statusFilter ? 'Tidak ada guru yang cocok dengan filter' : 'Belum ada data guru' }}"
                description="{{ $search ? 'Coba kata kunci lain atau bersihkan filter pencarian.' : 'Tambahkan data guru dan ustadz pengajar pertama lewat tombol Tambah Guru.' }}"
            />
        </div>
    @else
        <x-table :pagination="$teachers">
            <thead class="bg-slate-950 text-slate-400 font-semibold uppercase tracking-wider border-b border-slate-800">
                <tr>
                    <x-table.th sortable field="nip">NIP</x-table.th>
                    <x-table.th sortable field="name">Nama Pendidik</x-table.th>
                    <x-table.th sortable field="gender">L/P</x-table.th>
                    <x-table.th sortable field="specialization">Bidang Ajar</x-table.th>
                    <x-table.th sortable field="phone">Kontak</x-table.th>
                    <x-table.th sortable field="is_active">Status</x-table.th>
                    <x-table.th align="right">Aksi</x-table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @foreach ($teachers as $teacher)
                    <x-table.tr wire:key="teacher-{{ $teacher->id }}">
                        <x-table.td class="font-mono text-slate-400 text-xs">
                            {{ $teacher->nip ?: '-' }}
                        </x-table.td>
                        <x-table.td class="font-medium text-slate-200">
                            <div>{{ $teacher->name }}</div>
                            @if ($teacher->email)
                                <div class="text-[11px] text-slate-400 font-normal">{{ $teacher->email }}</div>
                            @endif
                        </x-table.td>
                        <x-table.td>
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-md text-xs font-semibold {{ $teacher->gender === 'L' ? 'bg-sky-500/10 text-sky-400' : 'bg-rose-500/10 text-rose-400' }}">
                                {{ $teacher->gender }}
                            </span>
                        </x-table.td>
                        <x-table.td class="text-slate-300">
                            {{ $teacher->specialization ?: '-' }}
                        </x-table.td>
                        <x-table.td class="font-mono text-xs text-slate-300">
                            {{ $teacher->phone }}
                        </x-table.td>
                        <x-table.td>
                            <x-status-badge :active="$teacher->is_active" />
                        </x-table.td>
                        <x-table.td align="right">
                            <div class="flex items-center justify-end gap-1">
                                @if ($this->canManage())
                                    <x-icon-button icon="pencil" :label="'Edit '.$teacher->name" tone="edit" @click="$dispatch('open-modal', 'record-form')" wire:click="openEditModal({{ $teacher->id }})" />
                                    <x-icon-button icon="trash" :label="'Hapus '.$teacher->name" tone="danger" @click="$dispatch('open-modal', 'confirm-delete')" wire:click="confirmDelete({{ $teacher->id }})" />
                                @endif
                            </div>
                        </x-table.td>
                    </x-table.tr>
                @endforeach
            </tbody>
        </x-table>
    @endif

    <x-record-form-modal
        :title="$editingId ? 'Edit Data Pendidik' : 'Tambah Guru / Ustadz Baru'"
        subtitle="Data identitas pendidik, kontak WhatsApp, dan bidang keahlian"
        icon="briefcase"
        max-width="2xl"
    >
        <form wire:submit="save" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="name" value="Nama Lengkap & Gelar *" />
                    <x-text-input wire:model="name" id="name" class="w-full" placeholder="Contoh: Ustadz Abdullah, S.Pd.I" autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
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

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="nip" value="NIP / Nomor Induk Guru" />
                    <x-text-input wire:model="nip" id="nip" class="w-full font-mono" placeholder="Nomor unik kepegawaian" />
                    <x-input-error :messages="$errors->get('nip')" class="mt-1.5" />
                </div>
                <div>
                    <x-input-label for="nuptk" value="NUPTK (Jika Ada)" />
                    <x-text-input wire:model="nuptk" id="nuptk" class="w-full font-mono" placeholder="Nomor registrasi kementerian" />
                    <x-input-error :messages="$errors->get('nuptk')" class="mt-1.5" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="phone" value="No. WhatsApp *" />
                    <x-text-input wire:model="phone" id="phone" class="w-full font-mono" placeholder="Contoh: 081234567890" />
                    <x-input-error :messages="$errors->get('phone')" class="mt-1.5" />
                </div>
                <div>
                    <x-input-label for="email" value="Alamat Email" />
                    <x-text-input wire:model="email" id="email" type="email" class="w-full" placeholder="email@sekolah.id" />
                    <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                </div>
            </div>

            <div>
                <x-input-label for="specialization" value="Bidang Keahlian / Mata Pelajaran" />
                <x-text-input wire:model="specialization" id="specialization" class="w-full" placeholder="Contoh: Tahfidz Al-Qur'an & Bahasa Arab" />
                <x-input-error :messages="$errors->get('specialization')" class="mt-1.5" />
            </div>

            <div>
                <x-input-label for="address" value="Alamat Domisili" />
                <x-textarea wire:model="address" id="address" rows="2" class="w-full" placeholder="Alamat tempat tinggal saat ini" />
                <x-input-error :messages="$errors->get('address')" class="mt-1.5" />
            </div>

            <div class="pt-2">
                <x-checkbox wire:model="is_active" id="is_active" label="Status Pendidik Aktif Mengajar" />
            </div>

            <x-modal-actions>
                <x-secondary-button @click="$dispatch('close-modal', 'record-form')">Batal</x-secondary-button>
                <x-primary-button type="submit" wire:loading.attr="disabled">
                    <x-loading-label target="save" :label="$editingId ? 'Simpan Perubahan' : 'Tambah Guru'" />
                </x-primary-button>
            </x-modal-actions>
        </form>
    </x-record-form-modal>

    <x-confirm-delete-modal
        title="Hapus Data Guru"
        description="Apakah Anda yakin ingin menghapus data pendidik ini? Data penugasan dan riwayat mengajar akan dinonaktifkan."
    />
</div>
