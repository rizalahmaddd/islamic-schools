<div class="space-y-4 sm:space-y-6">
    {{-- Header & Filters --}}
    <div class="bg-slate-900/80 rounded-xl border border-slate-800/80 p-4 sm:p-5 space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-slate-100 flex items-center gap-2">
                    <i data-lucide="clock" class="w-5 h-5 text-emerald-400"></i>
                    <span>Monitoring Presensi Harian Pendidik & Staf</span>
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Pemantauan jam masuk, jam pulang, verifikasi jarak GPS, dan ketepatan waktu guru.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('attendance.teachers.check-in') }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-emerald-400 bg-emerald-500/10 hover:bg-emerald-500/20 transition border border-emerald-500/20">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                    <span>Check-In Mandiri GPS</span>
                </a>

                <x-table.export-button action="export" label="Ekspor Laporan" />
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 pt-2 border-t border-slate-800/80">
            <div>
                <x-input-label for="date" value="Tanggal Presensi" />
                <x-text-input wire:model.live="date" id="date" type="date" class="w-full" />
            </div>

            <div>
                <x-input-label for="statusFilter" value="Status Kehadiran" />
                <x-select wire:model.live="statusFilter" id="statusFilter" class="w-full">
                    <option value="">Semua Status</option>
                    <option value="present">Hadir Tepat Waktu</option>
                    <option value="late">Terlambat</option>
                    <option value="permit">Izin</option>
                    <option value="sick">Sakit</option>
                    <option value="unrecorded">Belum Hadir / Belum Presensi</option>
                </x-select>
            </div>

            <div>
                <x-input-label for="search" value="Cari Pendidik" />
                <x-search-input wire:model.live.debounce.300ms="search" id="search" placeholder="Cari nama, NIP, atau mata pelajaran..." />
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5 sm:gap-3">
        <div class="bg-slate-900/60 rounded-xl border border-slate-800/80 p-3 flex items-center justify-between">
            <div>
                <span class="text-[11px] text-slate-400 uppercase font-semibold">Total Pendidik</span>
                <div class="text-lg font-bold text-slate-100">{{ $stats['total'] }}</div>
            </div>
            <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400">
                <i data-lucide="users" class="w-4 h-4"></i>
            </div>
        </div>

        <div class="bg-emerald-950/20 rounded-xl border border-emerald-500/20 p-3 flex items-center justify-between">
            <div>
                <span class="text-[11px] text-emerald-400 uppercase font-semibold">Tepat Waktu</span>
                <div class="text-lg font-bold text-emerald-400">{{ $stats['present'] }}</div>
            </div>
            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
            </div>
        </div>

        <div class="bg-amber-950/20 rounded-xl border border-amber-500/20 p-3 flex items-center justify-between">
            <div>
                <span class="text-[11px] text-amber-400 uppercase font-semibold">Terlambat</span>
                <div class="text-lg font-bold text-amber-400">{{ $stats['late'] }}</div>
            </div>
            <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center text-amber-400">
                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
            </div>
        </div>

        <div class="bg-sky-950/20 rounded-xl border border-sky-500/20 p-3 flex items-center justify-between">
            <div>
                <span class="text-[11px] text-sky-400 uppercase font-semibold">Izin / Sakit</span>
                <div class="text-lg font-bold text-sky-400">{{ $stats['permit'] }}</div>
            </div>
            <div class="w-8 h-8 rounded-lg bg-sky-500/10 flex items-center justify-center text-sky-400">
                <i data-lucide="info" class="w-4 h-4"></i>
            </div>
        </div>

        <div class="col-span-2 sm:col-span-1 bg-slate-900/60 rounded-xl border border-slate-800/80 p-3 flex items-center justify-between">
            <div>
                <span class="text-[11px] text-slate-400 uppercase font-semibold">Belum Presensi</span>
                <div class="text-lg font-bold text-slate-400">{{ $stats['unrecorded'] }}</div>
            </div>
            <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-slate-500">
                <i data-lucide="clock" class="w-4 h-4"></i>
            </div>
        </div>
    </div>

    {{-- Tabel Monitoring Presensi Guru --}}
    @if ($teachers->isEmpty())
        <div class="bg-slate-900/80 rounded-xl border border-slate-800/80 overflow-hidden">
            <x-empty-state
                icon="briefcase"
                title="Tidak ada data pendidik yang cocok"
                description="Coba ubah kata kunci pencarian atau filter status presensi."
            />
        </div>
    @else
        <div class="bg-slate-900/80 rounded-xl border border-slate-800/80 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left border-collapse">
                    <thead class="bg-slate-950 text-slate-400 font-semibold uppercase tracking-wider border-b border-slate-800">
                        <tr>
                            <th class="px-4 py-3 w-10 text-center">No</th>
                            <th class="px-4 py-3">Nama Pendidik & Gelar</th>
                            <th class="px-4 py-3">Bidang Ajar</th>
                            <th class="px-4 py-3 text-center">Jam Masuk</th>
                            <th class="px-4 py-3 text-center">Jam Pulang</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Metode & Jarak</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @foreach ($teachers as $index => $teacher)
                            @php
                                $att = $attendances->get($teacher->id);
                            @endphp
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="px-4 py-3 text-center text-slate-500 font-mono">{{ $index + 1 }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-200">{{ $teacher->name }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">NIP: {{ $teacher->nip ?: '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-slate-300">
                                    {{ $teacher->specialization ?: '-' }}
                                </td>
                                <td class="px-4 py-3 text-center font-mono font-medium {{ $att?->check_in_time ? 'text-slate-200' : 'text-slate-500' }}">
                                    {{ $att?->check_in_time ? substr((string) $att->check_in_time, 0, 5) : '-' }}
                                </td>
                                <td class="px-4 py-3 text-center font-mono font-medium {{ $att?->check_out_time ? 'text-slate-200' : 'text-slate-500' }}">
                                    {{ $att?->check_out_time ? substr((string) $att->check_out_time, 0, 5) : '-' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($att)
                                        <x-badge :color="$att->statusColor()">
                                            {{ $att->statusLabel() }}
                                        </x-badge>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-normal text-slate-400 bg-slate-800/60">
                                            Belum Hadir
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center text-slate-400">
                                    @if ($att)
                                        <div class="capitalize text-slate-300 font-medium">{{ $att->method }}</div>
                                        @if ($att->distance_meters !== null)
                                            <div class="text-[10px] font-mono {{ $att->distance_meters <= 100 ? 'text-emerald-400' : 'text-rose-400' }}">
                                                {{ $att->distance_meters }}m dari sekolah
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-slate-600">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button
                                        type="button"
                                        wire:click="openManualModal({{ $teacher->id }})"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-medium text-slate-300 hover:text-slate-100 bg-slate-800 hover:bg-slate-700 transition border border-slate-700"
                                        title="Koreksi / Input Presensi Manual"
                                    >
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                        <span>Koreksi</span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Modal Koreksi Presensi Manual --}}
    <x-modal name="manual-attendance-modal" max-width="lg">
        <div class="p-6 space-y-4">
            <x-modal-header
                title="Input / Koreksi Presensi Guru"
                :subtitle="'Pendidik: '.($editingTeacher?->name ?? 'Guru').' (Tanggal: '.\Carbon\Carbon::parse($date)->translatedFormat('d F Y').')'"
                icon="clock"
            />

            <form wire:submit="saveManual" class="space-y-4">
                <div>
                    <x-input-label for="formStatus" value="Status Kehadiran *" />
                    <x-select wire:model="formStatus" id="formStatus" class="w-full">
                        <option value="present">Hadir Tepat Waktu</option>
                        <option value="late">Terlambat</option>
                        <option value="permit">Izin</option>
                        <option value="sick">Sakit</option>
                        <option value="absent">Alpa (Tanpa Keterangan)</option>
                    </x-select>
                    <x-input-error :messages="$errors->get('formStatus')" class="mt-1" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-input-label for="formCheckIn" value="Jam Masuk (HH:MM)" />
                        <x-text-input wire:model="formCheckIn" id="formCheckIn" type="time" class="w-full font-mono" />
                        <x-input-error :messages="$errors->get('formCheckIn')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="formCheckOut" value="Jam Pulang (HH:MM)" />
                        <x-text-input wire:model="formCheckOut" id="formCheckOut" type="time" class="w-full font-mono" />
                        <x-input-error :messages="$errors->get('formCheckOut')" class="mt-1" />
                    </div>
                </div>

                <div>
                    <x-input-label for="formNotes" value="Keterangan / Catatan Tambahan" />
                    <x-text-input wire:model="formNotes" id="formNotes" class="w-full" placeholder="Contoh: Mengisi pelatihan dinas, dll." />
                    <x-input-error :messages="$errors->get('formNotes')" class="mt-1" />
                </div>

                <x-modal-actions>
                    <x-secondary-button @click="$dispatch('close-modal', 'manual-attendance-modal')">Batal</x-secondary-button>
                    <x-primary-button type="submit" wire:loading.attr="disabled">
                        <x-loading-label target="saveManual" label="Simpan Presensi" />
                    </x-primary-button>
                </x-modal-actions>
            </form>
        </div>
    </x-modal>
</div>
