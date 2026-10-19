<div class="space-y-4 sm:space-y-6">
    <x-list-toolbar
        description="Pencatatan materi ajar harian"
        search-placeholder="Cari materi, aktivitas, guru, atau kelas..."
        :can-manage="$this->canManage()"
        :can-export="true"
        add-label="Tambah Jurnal Mengajar"
    >
        <x-slot:filters>
            <div class="w-44">
                <x-select wire:model.live="classFilter" id="classFilter">
                    <option value="">Semua Kelas</option>
                    @foreach ($schoolClasses as $sc)
                        <option value="{{ $sc->id }}">{{ $sc->name }}</option>
                    @endforeach
                </x-select>
            </div>

            <div class="w-48">
                <x-select wire:model.live="subjectFilter" id="subjectFilter">
                    <option value="">Semua Mata Pelajaran</option>
                    @foreach ($subjects as $sb)
                        <option value="{{ $sb->id }}">{{ $sb->name }}</option>
                    @endforeach
                </x-select>
            </div>

            <div class="w-36">
                <x-text-input wire:model.live="dateFilter" id="dateFilter" type="date" class="w-full text-xs" />
            </div>
        </x-slot:filters>
    </x-list-toolbar>

    @if ($journals->isEmpty())
        <div class="bg-slate-900/80 rounded-xl border border-slate-800/80 overflow-hidden">
            <x-empty-state
                icon="book-open"
                title="{{ $search || $classFilter || $subjectFilter || $dateFilter ? 'Tidak ada jurnal yang cocok' : 'Belum ada catatan jurnal mengajar' }}"
                description="{{ $search ? 'Coba ubah kata kunci atau bersihkan filter pencarian.' : 'Catat materi pokok dan aktivitas ajar kelas pertama Anda lewat tombol Tambah Jurnal Mengajar.' }}"
            />
        </div>
    @else
        <x-table :pagination="$journals">
            <thead class="bg-slate-950 text-slate-400 font-semibold uppercase tracking-wider border-b border-slate-800">
                <tr>
                    <x-table.th sortable field="date">Tanggal</x-table.th>
                    <x-table.th>Guru Pengampu</x-table.th>
                    <x-table.th>Rombel & Mapel</x-table.th>
                    <x-table.th>Jam Ke</x-table.th>
                    <x-table.th>Materi & Aktivitas Ajar</x-table.th>
                    <x-table.th align="right">Aksi</x-table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @foreach ($journals as $journal)
                    <x-table.tr wire:key="journal-{{ $journal->id }}">
                        <x-table.td class="text-slate-300 whitespace-nowrap">
                            <div class="font-medium text-slate-200">{{ $journal->date?->translatedFormat('d M Y') }}</div>
                            <div class="text-[10px] text-slate-500">{{ $journal->date?->translatedFormat('l') }}</div>
                        </x-table.td>
                        <x-table.td class="text-slate-200 font-medium whitespace-nowrap">
                            {{ $journal->teacher?->name ?? '-' }}
                        </x-table.td>
                        <x-table.td class="text-slate-300">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-800 text-slate-200 border border-slate-700">
                                {{ $journal->schoolClass?->name ?? '-' }}
                            </span>
                            <div class="text-xs text-slate-400 mt-0.5">
                                {{ $journal->subject?->name ?? 'Mata Pelajaran Umum' }}
                            </div>
                        </x-table.td>
                        <x-table.td class="text-slate-300 whitespace-nowrap">
                            <span class="font-mono text-xs text-emerald-400 font-semibold bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20">
                                Jam {{ $journal->period_start }}-{{ $journal->period_end }}
                            </span>
                        </x-table.td>
                        <x-table.td class="text-slate-300">
                            <div class="font-semibold text-slate-100 text-xs">{{ $journal->topic_taught }}</div>
                            @if ($journal->learning_activities)
                                <p class="text-[11px] text-slate-400 mt-0.5 line-clamp-2 leading-relaxed">{{ $journal->learning_activities }}</p>
                            @endif
                            @if ($journal->notes)
                                <div class="text-[10px] text-amber-400/80 mt-1 italic">Catatan: {{ $journal->notes }}</div>
                            @endif
                        </x-table.td>
                        <x-table.td align="right">
                            <div class="flex items-center justify-end gap-1">
                                @if ($this->canManage())
                                    <x-icon-button icon="pencil" :label="'Edit Jurnal'" tone="edit" @click="$dispatch('open-modal', 'record-form')" wire:click="openEditModal({{ $journal->id }})" />
                                    <x-icon-button icon="trash" :label="'Hapus Jurnal'" tone="danger" @click="$dispatch('open-modal', 'confirm-delete')" wire:click="confirmDelete({{ $journal->id }})" />
                                @endif
                            </div>
                        </x-table.td>
                    </x-table.tr>
                @endforeach
            </tbody>
        </x-table>
    @endif

    {{-- Modal Tambah / Edit Jurnal Mengajar --}}
    <x-record-form-modal
        :title="$editingId ? 'Edit Jurnal Mengajar' : 'Catat Jurnal Mengajar Harian'"
        subtitle="Dokumentasi materi pokok, durasi jam, dan aktivitas santri di kelas"
        icon="book-open"
        max-width="2xl"
    >
        <form wire:submit="save" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="date" value="Tanggal Mengajar *" />
                    <x-text-input wire:model="date" id="date" type="date" class="w-full" />
                    <x-input-error :messages="$errors->get('date')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="teacher_id" value="Guru / Ustadz Pengampu *" />
                    <x-select wire:model="teacher_id" id="teacher_id" class="w-full">
                        <option value="">Pilih Guru Pengampu</option>
                        @foreach ($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('teacher_id')" class="mt-1" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="class_id" value="Rombongan Belajar / Kelas *" />
                    <x-select wire:model="class_id" id="class_id" class="w-full">
                        <option value="">Pilih Kelas</option>
                        @foreach ($schoolClasses as $sc)
                            <option value="{{ $sc->id }}">{{ $sc->name }}</option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('class_id')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="subject_id" value="Mata Pelajaran *" />
                    <x-select wire:model="subject_id" id="subject_id" class="w-full">
                        <option value="">Pilih Mata Pelajaran</option>
                        @foreach ($subjects as $sb)
                            <option value="{{ $sb->id }}">{{ $sb->name }} ({{ $sb->code }})</option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('subject_id')" class="mt-1" />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="period_start" value="Jam Ke (Mulai) *" />
                    <x-text-input wire:model="period_start" id="period_start" type="number" min="1" max="15" class="w-full font-mono" />
                    <x-input-error :messages="$errors->get('period_start')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="period_end" value="Jam Ke (Selesai) *" />
                    <x-text-input wire:model="period_end" id="period_end" type="number" min="1" max="15" class="w-full font-mono" />
                    <x-input-error :messages="$errors->get('period_end')" class="mt-1" />
                </div>
            </div>

            <div>
                <x-input-label for="topic_taught" value="Materi / Topik Bahasan Hari Ini *" />
                <x-text-input wire:model="topic_taught" id="topic_taught" class="w-full" placeholder="Contoh: Bab 3 Hukum Nun Sukun & Tanwin" autofocus />
                <x-input-error :messages="$errors->get('topic_taught')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="learning_activities" value="Aktivitas Pembelajaran di Kelas" />
                <x-textarea wire:model="learning_activities" id="learning_activities" rows="2" class="w-full" placeholder="Rangkuman kegiatan belajar mengajar atau metode yang digunakan" />
                <x-input-error :messages="$errors->get('learning_activities')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="notes" value="Catatan Kelas / Evaluasi Santri" />
                <x-text-input wire:model="notes" id="notes" class="w-full" placeholder="Contoh: 3 santri perlu pengayaan tahsin, dll." />
                <x-input-error :messages="$errors->get('notes')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="photo" value="Foto Bukti Pembelajaran (Opsional)" />
                <x-file-input wire:model="photo" id="photo" accept="image/*" class="w-full" />
                <x-input-error :messages="$errors->get('photo')" class="mt-1" />
                @if ($existingPhotoPath && ! $photo)
                    <p class="text-[11px] text-emerald-400 mt-1">✓ Foto kegiatan saat ini sudah terunggah.</p>
                @endif
            </div>

            <x-modal-actions>
                <x-secondary-button @click="$dispatch('close-modal', 'record-form')">Batal</x-secondary-button>
                <x-primary-button type="submit" wire:loading.attr="disabled">
                    <x-loading-label target="save" :label="$editingId ? 'Simpan Perubahan' : 'Catat Jurnal'" />
                </x-primary-button>
            </x-modal-actions>
        </form>
    </x-record-form-modal>

    <x-confirm-delete-modal
        title="Hapus Jurnal Mengajar"
        description="Apakah Anda yakin ingin menghapus catatan jurnal mengajar ini? Tindakan ini tidak dapat dibatalkan."
    />
</div>
