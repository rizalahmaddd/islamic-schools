<div class="space-y-6">
    {{-- Header & Subtitle --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Penerimaan Santri Baru (PPDB)</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Verifikasi berkas, penilaian seleksi tes masuk, dan 1-klik konversi pendaftar menjadi santri aktif.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <x-secondary-button :href="route('ppdb.register')" target="_blank">
                <x-icon name="external-link" class="mr-1.5 h-4 w-4 text-emerald-600" />
                Formulir Pendaftaran Publik
            </x-secondary-button>
            @can('manage-admissions')
                <x-secondary-button :href="route('admissions.batches')" wire:navigate>
                    <x-icon name="calendar" class="mr-1.5 h-4 w-4" />
                    Kelola Gelombang
                </x-secondary-button>
            @endcan
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="w-full sm:w-80">
            <x-search-input wire:model.live.debounce.300ms="search" placeholder="Cari santri, no registrasi, wali, asal sekolah..." />
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="w-48">
                <x-select wire:model.live="batchFilter">
                    <option value="">Semua Gelombang</option>
                    @foreach($batches as $batch)
                        <option value="{{ $batch->id }}">{{ $batch->name }}</option>
                    @endforeach
                </x-select>
            </div>

            <div class="w-44">
                <x-select wire:model.live="statusFilter">
                    <option value="">Semua Status</option>
                    <option value="registered">Terdaftar (Baru)</option>
                    <option value="verified">Berkas Terverifikasi</option>
                    <option value="tested">Selesai Tes</option>
                    <option value="passed">Lulus Seleksi</option>
                    <option value="enrolled">Santri Aktif (Dikonversi)</option>
                    <option value="rejected">Tidak Lulus</option>
                </x-select>
            </div>
        </div>
    </div>

    {{-- Table Candidates --}}
    <x-table :pagination="$candidates">
        <x-slot:header>
            <x-table.th>No. Registrasi</x-table.th>
            <x-table.th>Calon Santri</x-table.th>
            <x-table.th>Gelombang PPDB</x-table.th>
            <x-table.th>Wali Murid & Kontak</x-table.th>
            <x-table.th>Nilai Tes Seleksi</x-table.th>
            <x-table.th>Status</x-table.th>
            <x-table.th class="text-right">Aksi</x-table.th>
        </x-slot:header>

        @forelse($candidates as $candidate)
            <x-table.tr wire:key="candidate-{{ $candidate->id }}">
                <x-table.td>
                    <div class="font-mono text-xs font-bold text-slate-900 dark:text-white">{{ $candidate->registration_number }}</div>
                    <div class="text-[11px] text-slate-400">{{ $candidate->created_at->format('d/m/Y H:i') }}</div>
                </x-table.td>
                <x-table.td>
                    <div class="font-semibold text-slate-900 dark:text-white">{{ $candidate->student_name }}</div>
                    <div class="text-xs text-slate-500">
                        {{ $candidate->gender === 'male' ? 'Putra' : 'Putri' }}
                        @if($candidate->previous_school)
                            · Asal: {{ $candidate->previous_school }}
                        @endif
                    </div>
                </x-table.td>
                <x-table.td class="text-xs font-medium text-slate-700 dark:text-slate-300">
                    {{ $candidate->batch->name }}
                </x-table.td>
                <x-table.td class="text-xs">
                    <div class="font-medium text-slate-900 dark:text-white">{{ $candidate->guardian_name }} ({{ $candidate->guardian_relationship }})</div>
                    <div class="text-slate-500 font-mono">{{ $candidate->guardian_phone }}</div>
                </x-table.td>
                <x-table.td class="text-xs">
                    @if($candidate->test_score_academic !== null || $candidate->test_score_quran !== null || $candidate->test_score_interview !== null)
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-slate-800 dark:text-slate-100">
                                Rata-rata: {{ $candidate->averageScore() }}
                            </span>
                        </div>
                        <div class="text-[11px] text-slate-500 mt-0.5 space-x-1.5">
                            <span>Akd: {{ $candidate->test_score_academic ?? '-' }}</span>
                            <span>Qrn: {{ $candidate->test_score_quran ?? '-' }}</span>
                            <span>Wwc: {{ $candidate->test_score_interview ?? '-' }}</span>
                        </div>
                    @else
                        <span class="text-slate-400 italic">Belum diinput</span>
                    @endif
                </x-table.td>
                <x-table.td>
                    <x-badge :color="$candidate->statusBadgeColor()">
                        {{ $candidate->statusLabel() }}
                    </x-badge>
                    @if($candidate->isEnrolled() && $candidate->convertedStudent)
                        <div class="mt-1 text-[11px] font-mono font-semibold text-emerald-600 dark:text-emerald-400">
                            NIS: {{ $candidate->convertedStudent->nis }}
                        </div>
                    @endif
                </x-table.td>
                <x-table.td class="text-right">
                    <div class="flex items-center justify-end gap-1">
                        {{-- Cetak Bukti Registrasi --}}
                        <a href="{{ \Illuminate\Support\Facades\URL::signedRoute('ppdb.print', $candidate->registration_number) }}" target="_blank" title="Cetak Bukti Pendaftaran" class="inline-flex items-center justify-center rounded-lg p-1.5 text-slate-400 hover:bg-slate-800/60 hover:text-slate-100 dark:hover:bg-slate-800">
                            <x-icon name="printer" class="h-4 w-4" />
                        </a>

                        @can('manage-admissions')
                            {{-- Input / Edit Nilai Tes --}}
                            <x-icon-button wire:click="openScoreModal({{ $candidate->id }})" icon="edit-3" label="Input Nilai Seleksi" class="text-sky-600 hover:text-sky-700" />

                            {{-- Verifikasi Berkas (jika masih baru) --}}
                            @if($candidate->isRegistered())
                                <x-icon-button wire:click="verifyCandidate({{ $candidate->id }})" icon="file-check" label="Verifikasi Berkas" class="text-amber-600 hover:text-amber-700" />
                            @endif

                            {{-- Luluskan Seleksi --}}
                            @if(in_array($candidate->status, ['verified', 'tested'], true))
                                <x-icon-button wire:click="markAsPassed({{ $candidate->id }})" icon="check" label="Luluskan Calon Santri" class="text-emerald-600 hover:text-emerald-700" />
                                <x-icon-button wire:click="markAsRejected({{ $candidate->id }})" icon="x" label="Tandai Tidak Lulus" class="text-rose-600 hover:text-rose-700" />
                            @endif

                            {{-- 1-KLIK KONVERSI MENJADI SANTRI AKTIF --}}
                            @if($candidate->isPassed())
                                <button
                                    wire:click="openConvertModal({{ $candidate->id }})"
                                    class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-2.5 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-emerald-700 transition"
                                >
                                    <x-icon name="user-check" class="h-3.5 w-3.5" />
                                    1-Klik Konversi
                                </button>
                            @endif
                        @endcan
                    </div>
                </x-table.td>
            </x-table.tr>
        @empty
            <x-table.tr>
                <x-table.td colspan="7">
                    <x-empty-state icon="users" title="Belum Ada Pendaftar PPDB" description="Calon santri yang mendaftar secara online atau offline akan muncul di tabel ini." />
                </x-table.td>
            </x-table.tr>
        @endforelse
    </x-table>

    {{-- MODAL: Input Nilai Seleksi --}}
    <x-modal name="score-modal" :show="$showScoreModal" max-width="md">
        <form wire:submit="saveScores" class="p-6 space-y-4">
            <x-modal-header title="Input Nilai Tes Seleksi" subtitle="Masukkan skor tes akademik, Al-Qur'an/tahfidz, dan wawancara." icon="edit-3" />

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <x-input-label for="test_score_academic" value="Akademik" />
                    <x-text-input id="test_score_academic" wire:model="test_score_academic" type="number" step="0.1" min="0" max="100" class="mt-1 block w-full" placeholder="0-100" />
                    <x-input-error :messages="$errors->get('test_score_academic')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="test_score_quran" value="Al-Qur'an" />
                    <x-text-input id="test_score_quran" wire:model="test_score_quran" type="number" step="0.1" min="0" max="100" class="mt-1 block w-full" placeholder="0-100" />
                    <x-input-error :messages="$errors->get('test_score_quran')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="test_score_interview" value="Wawancara" />
                    <x-text-input id="test_score_interview" wire:model="test_score_interview" type="number" step="0.1" min="0" max="100" class="mt-1 block w-full" placeholder="0-100" />
                    <x-input-error :messages="$errors->get('test_score_interview')" class="mt-1" />
                </div>
            </div>

            <div>
                <x-input-label for="test_notes" value="Catatan Penguji / Tim Seleksi" />
                <x-textarea id="test_notes" wire:model="test_notes" rows="3" class="mt-1 block w-full" placeholder="Catatan adab, hafalan juz yang sudah lancar, minat bakat..." />
                <x-input-error :messages="$errors->get('test_notes')" class="mt-1" />
            </div>

            <x-modal-actions>
                <x-secondary-button x-on:click="$dispatch('close-modal', 'score-modal')">Batal</x-secondary-button>
                <x-primary-button type="submit">
                    <x-loading-label target="saveScores">Simpan Nilai</x-loading-label>
                </x-primary-button>
            </x-modal-actions>
        </form>
    </x-modal>

    {{-- MODAL: 1-Klik Konversi Menjadi Santri Aktif --}}
    <x-modal name="convert-modal" :show="$showConvertModal" max-width="md">
        <form wire:submit="executeConversion" class="p-6 space-y-4">
            <x-modal-header title="1-Klik Konversi Santri Aktif" subtitle="Pindahkan data calon santri langsung menjadi santri aktif sekolah." icon="user-check" />

            <div class="rounded-xl bg-emerald-50 p-4 text-xs text-emerald-900 border border-emerald-200 dark:bg-emerald-950/30 dark:text-emerald-300 dark:border-emerald-800 space-y-1">
                <strong>Otomatisasi Sistem:</strong>
                <p>1. Data wali murid disinkronkan & akun login portal dibuatkan.</p>
                <p>2. Data siswa resmi terdaftar sebagai Santri Aktif.</p>
                <p>3. Ditempatkan langsung ke kelas rombel yang dipilih.</p>
                <p>4. Notifikasi WhatsApp ucapan selamat datang terkirim ke wali.</p>
            </div>

            <div>
                <x-input-label for="target_class_id" value="Kelas / Rombel Penempatan" />
                <x-select id="target_class_id" wire:model="target_class_id" class="mt-1 block w-full">
                    <option value="">Pilih Kelas Rombel</option>
                    @foreach($schoolClasses as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} (Tingkat {{ $c->grade_level }}) - {{ $c->academicYear?->name }}</option>
                    @endforeach
                </x-select>
                <x-input-error :messages="$errors->get('target_class_id')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="custom_nis" value="Nomor Induk Siswa (NIS) — Opsional" />
                <x-text-input id="custom_nis" wire:model="custom_nis" type="text" class="mt-1 block w-full" placeholder="Biarkan kosong untuk generate otomatis" />
                <x-input-error :messages="$errors->get('custom_nis')" class="mt-1" />
            </div>

            <div class="pt-1">
                <x-checkbox
                    wire:model="create_user_account"
                    label="Buatkan akun login Portal Wali Murid secara otomatis"
                />
            </div>

            <x-modal-actions>
                <x-secondary-button x-on:click="$dispatch('close-modal', 'convert-modal')">Batal</x-secondary-button>
                <x-primary-button type="submit">
                    <x-loading-label target="executeConversion">Eksekusi Konversi Santri</x-loading-label>
                </x-primary-button>
            </x-modal-actions>
        </form>
    </x-modal>
</div>
