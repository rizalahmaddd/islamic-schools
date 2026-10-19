<div class="space-y-4 sm:space-y-6">
    {{-- Header & Filter Section --}}
    <div class="bg-slate-900/80 rounded-xl border border-slate-800/80 p-4 sm:p-5 space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-slate-100 flex items-center gap-2">
                    <i data-lucide="calendar-check" class="w-5 h-5 text-emerald-400"></i>
                    <span>Lembar Presensi Harian Santri</span>
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Pilih rombel dan tanggal untuk melakukan absensi kehadiran kelas secara cepat.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('attendance.reports') }}" wire:navigate class="inline-flex items-center gap-1.5 min-h-[44px] sm:min-h-0 px-3 py-1.5 rounded-lg text-xs font-medium text-slate-300 bg-slate-800 hover:bg-slate-700 hover:text-slate-100 transition border border-slate-700">
                    <i data-lucide="file-bar-chart" class="w-4 h-4 text-emerald-400"></i>
                    <span>Rekap Bulanan</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3.5 pt-2 border-t border-slate-800/80">
            <div>
                <x-input-label for="classId" value="Pilih Kelas / Rombel" />
                <x-select wire:model.live="classId" id="classId" class="w-full">
                    <option value="">Pilih Rombongan Belajar</option>
                    @foreach ($schoolClasses as $schoolClass)
                        <option value="{{ $schoolClass->id }}">{{ $schoolClass->name }} ({{ $schoolClass->academicYear?->name ?? 'Nonaktif' }})</option>
                    @endforeach
                </x-select>
                <x-input-error :messages="$errors->get('classId')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="date" value="Tanggal Presensi" />
                <x-text-input wire:model.live="date" id="date" type="date" class="w-full" max="{{ now()->format('Y-m-d') }}" />
                <x-input-error :messages="$errors->get('date')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="searchQuery" value="Cari Siswa di Kelas" />
                <x-search-input wire:model.live.debounce.300ms="searchQuery" id="searchQuery" placeholder="Ketik nama atau NIS..." />
            </div>

            <div class="flex items-end pb-1">
                @if ($this->canManage() && count($attendanceData) > 0)
                    <x-secondary-button type="button" wire:click="setAllPresent" class="w-full justify-center">
                        <i data-lucide="check-check" class="w-4 h-4 text-emerald-400"></i>
                        <span>Semua Hadir</span>
                    </x-secondary-button>
                @endif
            </div>
        </div>
    </div>

    {{-- Ringkasan Kehadiran Cepat (Stats Pills) --}}
    @if ($selectedClass && count($attendanceData) > 0)
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5 sm:gap-3">
            <div class="bg-slate-900/60 rounded-xl border border-slate-800/80 p-3 flex items-center justify-between">
                <div>
                    <span class="text-[11px] text-slate-400 uppercase font-semibold">Total Siswa</span>
                    <div class="text-lg font-bold text-slate-100">{{ $stats['total'] }}</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
            </div>

            <div class="bg-emerald-950/20 rounded-xl border border-emerald-500/20 p-3 flex items-center justify-between">
                <div>
                    <span class="text-[11px] text-emerald-400 uppercase font-semibold">Hadir (H)</span>
                    <div class="text-lg font-bold text-emerald-400">{{ $stats['present'] }}</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                    <i data-lucide="check" class="w-4 h-4"></i>
                </div>
            </div>

            <div class="bg-amber-950/20 rounded-xl border border-amber-500/20 p-3 flex items-center justify-between">
                <div>
                    <span class="text-[11px] text-amber-400 uppercase font-semibold">Sakit (S)</span>
                    <div class="text-lg font-bold text-amber-400">{{ $stats['sick'] }}</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center text-amber-400">
                    <i data-lucide="thermometer" class="w-4 h-4"></i>
                </div>
            </div>

            <div class="bg-sky-950/20 rounded-xl border border-sky-500/20 p-3 flex items-center justify-between">
                <div>
                    <span class="text-[11px] text-sky-400 uppercase font-semibold">Izin (I)</span>
                    <div class="text-lg font-bold text-sky-400">{{ $stats['permit'] }}</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-sky-500/10 flex items-center justify-center text-sky-400">
                    <i data-lucide="info" class="w-4 h-4"></i>
                </div>
            </div>

            <div class="col-span-2 sm:col-span-1 bg-rose-950/20 rounded-xl border border-rose-500/20 p-3 flex items-center justify-between">
                <div>
                    <span class="text-[11px] text-rose-400 uppercase font-semibold">Alpa (A)</span>
                    <div class="text-lg font-bold text-rose-400">{{ $stats['absent'] }}</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-rose-500/10 flex items-center justify-center text-rose-400">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </div>
            </div>
        </div>
    @endif

    {{-- Daftar Santri & Input Status --}}
    @if (! $selectedClass)
        <div class="bg-slate-900/80 rounded-xl border border-slate-800/80 overflow-hidden">
            <x-empty-state
                icon="layout-grid"
                title="Pilih Kelas Terlebih Dahulu"
                description="Silakan pilih rombongan belajar di atas untuk menampilkan daftar santri dan memulai presensi."
            />
        </div>
    @elseif ($students->isEmpty())
        <div class="bg-slate-900/80 rounded-xl border border-slate-800/80 overflow-hidden">
            <x-empty-state
                icon="users"
                title="{{ $searchQuery ? 'Tidak ada santri yang cocok' : 'Belum ada santri di kelas ini' }}"
                description="{{ $searchQuery ? 'Coba ubah kata kunci pencarian.' : 'Rombel ini belum memiliki santri aktif yang terdaftar.' }}"
            />
        </div>
    @else
        <form wire:submit="save" class="space-y-4">
            @if ($savedCount > 0)
                <p class="flex items-center gap-2 text-xs text-emerald-400"><i data-lucide="check-circle-2" class="w-4 h-4 shrink-0" aria-hidden="true"></i> Presensi tanggal ini sudah tersimpan untuk {{ $savedCount }} santri. Mengubah status lalu menyimpan akan memperbarui catatan.</p>
            @else
                <p class="flex items-center gap-2 text-xs text-amber-400"><i data-lucide="alert-triangle" class="w-4 h-4 shrink-0" aria-hidden="true"></i> Belum disimpan. Semua santri tampil Hadir sampai Anda menekan Simpan.</p>
            @endif
            <div class="bg-slate-900/80 rounded-xl border border-slate-800/80 overflow-hidden divide-y divide-slate-800/60">
                @foreach ($students as $index => $student)
                    @php
                        $currentStatus = $attendanceData[$student->id]['status'] ?? 'present';
                    @endphp
                    <div wire:key="student-row-{{ $student->id }}" class="p-3.5 sm:p-4 hover:bg-slate-800/30 transition flex flex-col md:flex-row md:items-center justify-between gap-3">
                        {{-- Info Santri --}}
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="text-xs font-mono text-slate-500 w-6 text-right shrink-0">{{ $index + 1 }}.</span>
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center font-bold text-xs shrink-0 {{ $student->gender === 'L' ? 'bg-sky-500/10 text-sky-400 border border-sky-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                                {{ $student->gender === 'L' ? 'IKH' : 'AKH' }}
                            </div>
                            <div class="min-w-0">
                                <div class="font-medium text-slate-200 text-sm truncate flex items-center gap-2">
                                    <span>{{ $student->name }}</span>
                                    @if ($student->guardian && $student->guardian->phone)
                                        <span class="text-[10px] text-emerald-400/80 font-mono hidden sm:inline" title="Nomor WA Wali Terhubung">
                                            <i data-lucide="message-square" class="w-3 h-3 inline"></i>
                                        </span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-slate-400 font-mono">
                                    NIS: {{ $student->nis }}
                                    @if ($student->guardian)
                                        <span class="text-slate-500 font-sans">• Wali: {{ $student->guardian->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Tombol Pilihan Status H / S / I / A & Form Catatan --}}
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 shrink-0 pl-9 md:pl-0">
                            {{-- Status Buttons Group --}}
                            <div class="grid grid-cols-4 w-full sm:w-auto sm:inline-flex rounded-lg border border-slate-700/80 p-0.5 bg-slate-950/80 shrink-0" role="group" aria-label="Status kehadiran {{ $student->name }}">
                                {{-- Hadir (H) --}}
                                <button
                                    type="button"
                                    wire:click="setStatus({{ $student->id }}, 'present')"
                                    aria-label="Hadir" aria-pressed="{{ $currentStatus === 'present' ? 'true' : 'false' }}"
                                    class="min-h-[44px] sm:min-h-[36px] sm:min-w-[44px] px-3 py-1.5 rounded-md text-xs font-bold transition flex items-center justify-center gap-1 {{ $currentStatus === 'present' ? 'bg-emerald-600 text-white shadow' : 'text-slate-400 hover:text-emerald-400' }}"
                                >
                                    <span>H</span>
                                    <span class="hidden sm:inline font-normal text-[11px]">Hadir</span>
                                </button>

                                {{-- Sakit (S) --}}
                                <button
                                    type="button"
                                    wire:click="setStatus({{ $student->id }}, 'sick')"
                                    aria-label="Sakit" aria-pressed="{{ $currentStatus === 'sick' ? 'true' : 'false' }}"
                                    class="min-h-[44px] sm:min-h-[36px] sm:min-w-[44px] px-3 py-1.5 rounded-md text-xs font-bold transition flex items-center justify-center gap-1 {{ $currentStatus === 'sick' ? 'bg-amber-600 text-white shadow' : 'text-slate-400 hover:text-amber-400' }}"
                                >
                                    <span>S</span>
                                    <span class="hidden sm:inline font-normal text-[11px]">Sakit</span>
                                </button>

                                {{-- Izin (I) --}}
                                <button
                                    type="button"
                                    wire:click="setStatus({{ $student->id }}, 'permit')"
                                    aria-label="Izin" aria-pressed="{{ $currentStatus === 'permit' ? 'true' : 'false' }}"
                                    class="min-h-[44px] sm:min-h-[36px] sm:min-w-[44px] px-3 py-1.5 rounded-md text-xs font-bold transition flex items-center justify-center gap-1 {{ $currentStatus === 'permit' ? 'bg-sky-600 text-white shadow' : 'text-slate-400 hover:text-sky-400' }}"
                                >
                                    <span>I</span>
                                    <span class="hidden sm:inline font-normal text-[11px]">Izin</span>
                                </button>

                                {{-- Alpa (A) --}}
                                <button
                                    type="button"
                                    wire:click="setStatus({{ $student->id }}, 'absent')"
                                    aria-label="Alpa" aria-pressed="{{ $currentStatus === 'absent' ? 'true' : 'false' }}"
                                    class="min-h-[44px] sm:min-h-[36px] sm:min-w-[44px] px-3 py-1.5 rounded-md text-xs font-bold transition flex items-center justify-center gap-1 {{ $currentStatus === 'absent' ? 'bg-rose-600 text-white shadow' : 'text-slate-400 hover:text-rose-400' }}"
                                >
                                    <span>A</span>
                                    <span class="hidden sm:inline font-normal text-[11px]">Alpa</span>
                                </button>
                            </div>

                            {{-- Catatan (Hanya tampil atau diisi jika tidak hadir) --}}
                            <div class="w-full sm:w-48">
                                <x-text-input
                                    wire:model="attendanceData.{{ $student->id }}.notes"
                                    type="text"
                                    class="w-full text-xs"
                                    aria-label="Catatan {{ $student->name }}"
                                    placeholder="{{ $currentStatus !== 'present' ? 'Keterangan...' : 'Catatan (opsional)' }}"
                                />
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Action Toolbar & Save Section --}}
            <div class="sticky bottom-[calc(4.5rem+env(safe-area-inset-bottom))] md:bottom-4 bg-slate-900/95 backdrop-blur-md rounded-xl border border-slate-700/80 p-4 shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <x-checkbox
                        wire:model="sendWhatsApp"
                        id="sendWhatsApp"
                        label="Kirim notifikasi WhatsApp ke wali santri yang tidak hadir (Sakit / Izin / Alpa)"
                    />
                </div>

                <div class="flex items-center justify-end gap-3 shrink-0">
                    <x-secondary-button type="button" wire:click="loadAttendance">
                        Reset
                    </x-secondary-button>

                    @if ($this->canManage())
                        <x-primary-button type="submit" wire:loading.attr="disabled">
                            <x-loading-label target="save" label="Simpan Presensi Kelas" />
                        </x-primary-button>
                    @endif
                </div>
            </div>
        </form>
    @endif
</div>
