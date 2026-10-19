<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Perizinan Keluar Santri</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Pengajuan izin keluar/pulang santri, persetujuan musyrif, dan tiket barcode/QR Gate Pass satpam.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @can('scan-gate-pass')
                <x-secondary-button :href="route('boarding.scanner')" wire:navigate>
                    <x-icon name="qr-code" class="mr-1.5 h-4 w-4 text-emerald-600" />
                    Buka Scanner Satpam
                </x-secondary-button>
            @endcan
            @can('manage-boarding')
                <x-primary-button wire:click="openCreateModal">
                    <x-icon name="plus" class="mr-1.5 h-4 w-4" />
                    Ajukan Izin Santri
                </x-primary-button>
            @endcan
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="w-full sm:w-72">
            <x-search-input wire:model.live.debounce.300ms="search" placeholder="Cari santri, no. izin, penjemput..." />
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="w-44">
                <x-select wire:model.live="statusFilter">
                    <option value="">Semua Status</option>
                    <option value="pending">Menunggu Persetujuan</option>
                    <option value="approved_mentor">Disetujui Musyrif</option>
                    <option value="active">Di Luar Pondok</option>
                    <option value="returned">Kembali Tepat Waktu</option>
                    <option value="late">Kembali Terlambat</option>
                    <option value="rejected">Ditolak</option>
                </x-select>
            </div>

            <div class="w-48">
                <x-select wire:model.live="leaveTypeFilter">
                    <option value="">Semua Tipe Izin</option>
                    <option value="weekend">Pulang Rutin / Weekend</option>
                    <option value="sick">Izin Sakit / Berobat</option>
                    <option value="family">Acara Keluarga</option>
                    <option value="dispensation">Dispensasi Lomba / Dinas</option>
                </x-select>
            </div>
        </div>
    </div>

    {{-- Table of Leave Requests --}}
    <x-table :pagination="$leaves">
        <x-slot:header>
            <x-table.th>No. Izin & Tipe</x-table.th>
            <x-table.th>Santri / Kamar</x-table.th>
            <x-table.th>Jadwal Rencana</x-table.th>
            <x-table.th>Riil Keberangkatan / Kembali</x-table.th>
            <x-table.th>Penjemput & Keperluan</x-table.th>
            <x-table.th>Status</x-table.th>
            <x-table.th class="text-right">Aksi</x-table.th>
        </x-slot:header>

        @forelse($leaves as $leave)
            <x-table.tr wire:key="leave-{{ $leave->id }}">
                <x-table.td>
                    <div class="font-mono text-xs font-bold text-slate-900 dark:text-white">{{ $leave->leave_number }}</div>
                    <div class="text-xs text-slate-500">{{ $leave->leaveTypeLabel() }}</div>
                </x-table.td>
                <x-table.td>
                    <div class="font-semibold text-slate-900 dark:text-white">{{ $leave->student->name }}</div>
                    <div class="text-xs text-slate-500">
                        NIS: {{ $leave->student->nis ?: '-' }}
                        @php
                            $placement = $leave->student->dormPlacements->first();
                        @endphp
                        @if($placement)
                            · {{ $placement->room->building->name }} - {{ $placement->room->room_number }}
                        @endif
                    </div>
                </x-table.td>
                <x-table.td class="text-xs">
                    <div class="text-slate-800 dark:text-slate-200">
                        <span class="font-medium text-slate-500">Berangkat:</span> {{ $leave->departure_plan->format('d/m/Y H:i') }}
                    </div>
                    <div class="text-slate-800 dark:text-slate-200">
                        <span class="font-medium text-slate-500">Kembali:</span> {{ $leave->return_plan->format('d/m/Y H:i') }}
                    </div>
                </x-table.td>
                <x-table.td class="text-xs">
                    @if($leave->actual_departure_at)
                        <div class="text-emerald-700 dark:text-emerald-400">
                            Keluar: {{ $leave->actual_departure_at->format('d/m/Y H:i') }}
                        </div>
                    @else
                        <div class="text-slate-400 italic">Belum keluar</div>
                    @endif

                    @if($leave->actual_returned_at)
                        <div class="{{ $leave->status === 'late' ? 'font-semibold text-rose-600 dark:text-rose-400' : 'text-slate-700 dark:text-slate-300' }}">
                            Kembali: {{ $leave->actual_returned_at->format('d/m/Y H:i') }}
                        </div>
                    @elseif($leave->isActive())
                        <div class="font-medium text-amber-600 dark:text-amber-400 animate-pulse">Sedang di luar</div>
                    @endif
                </x-table.td>
                <x-table.td class="max-w-xs text-xs">
                    <div class="font-medium text-slate-800 dark:text-slate-200">{{ $leave->pickup_person ?: 'Mandiri / Wali Murid' }}</div>
                    <div class="truncate text-slate-500" title="{{ $leave->reason }}">{{ $leave->reason }}</div>
                </x-table.td>
                <x-table.td>
                    <x-badge :color="$leave->statusBadgeColor()">
                        {{ $leave->statusLabel() }}
                    </x-badge>
                </x-table.td>
                <x-table.td class="text-right">
                    <div class="flex items-center justify-end gap-1">
                        {{-- Tombol Lihat Tiket Gate Pass --}}
                        <x-icon-button wire:click="viewTicket({{ $leave->id }})" icon="qr-code" label="Lihat Tiket Gate Pass" class="text-emerald-600 hover:text-emerald-700" />

                        {{-- Tombol Cetak Dokumen Surat Izin Resmi --}}
                        <a href="{{ route('boarding.leaves.print', $leave->id) }}" target="_blank" title="Cetak Surat Izin" class="inline-flex items-center justify-center rounded-lg p-1.5 text-slate-400 hover:bg-slate-800/60 hover:text-slate-100 dark:hover:bg-slate-800">
                            <x-icon name="printer" class="h-4 w-4" />
                        </a>

                        @can('manage-boarding')
                            @if($leave->isPending())
                                <x-icon-button wire:click="approve({{ $leave->id }})" icon="check" label="Setujui Izin" class="text-sky-600 hover:text-sky-700" />
                                <x-icon-button wire:click="openRejectModal({{ $leave->id }})" icon="x" label="Tolak Izin" class="text-rose-600 hover:text-rose-700" />
                            @endif
                        @endcan
                    </div>
                </x-table.td>
            </x-table.tr>
        @empty
            <x-table.tr>
                <x-table.td colspan="7">
                    <x-empty-state icon="file-text" title="Belum Ada Data Perizinan" description="Gunakan tombol Ajukan Izin Santri untuk membuat perizinan baru." />
                </x-table.td>
            </x-table.tr>
        @endforelse
    </x-table>

    {{-- MODAL: Ajukan Izin Santri --}}
    <x-modal name="leave-create-form" :show="$showCreateModal" max-width="lg">
        <form wire:submit="saveLeave" class="p-6 space-y-4">
            <x-modal-header title="Pengajuan Izin Keluar / Pulang Santri" subtitle="Isi data santri, jadwal kepulangan, dan penjemput." icon="file-text" />

            <div>
                <x-input-label for="student_id" value="Pilih Santri" />
                <x-select id="student_id" wire:model="student_id" class="mt-1 block w-full">
                    <option value="">Pilih Santri</option>
                    @foreach($students as $s)
                        <option value="{{ $s->id }}">{{ $s->name }} (NIS: {{ $s->nis ?: '-' }})</option>
                    @endforeach
                </x-select>
                <x-input-error :messages="$errors->get('student_id')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="leave_type" value="Kategori Izin" />
                <x-select id="leave_type" wire:model="leave_type" class="mt-1 block w-full">
                    <option value="weekend">Pulang Rutin / Weekend</option>
                    <option value="sick">Izin Berobat / Sakit</option>
                    <option value="family">Acara Keluarga / Hajatan</option>
                    <option value="dispensation">Dispensasi Lomba / Dinas</option>
                </x-select>
                <x-input-error :messages="$errors->get('leave_type')" class="mt-1" />
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="departure_plan" value="Rencana Berangkat" />
                    <x-text-input id="departure_plan" wire:model="departure_plan" type="datetime-local" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('departure_plan')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="return_plan" value="Rencana Kembali" />
                    <x-text-input id="return_plan" wire:model="return_plan" type="datetime-local" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('return_plan')" class="mt-1" />
                </div>
            </div>

            <div>
                <x-input-label for="pickup_person" value="Nama Penjemput & Kontak HP (Opsional)" />
                <x-text-input id="pickup_person" wire:model="pickup_person" type="text" class="mt-1 block w-full" placeholder="Contoh: Bapak Ahmad Fauzi (Ayah) - 08123456789" />
                <x-input-error :messages="$errors->get('pickup_person')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="reason" value="Keperluan / Alasan Izin" />
                <x-textarea id="reason" wire:model="reason" rows="3" class="mt-1 block w-full" placeholder="Tuliskan alasan izin santri secara lengkap..." />
                <x-input-error :messages="$errors->get('reason')" class="mt-1" />
            </div>

            <x-modal-actions>
                <x-secondary-button x-on:click="$dispatch('close-modal', 'leave-create-form')">Batal</x-secondary-button>
                <x-primary-button type="submit">
                    <x-loading-label target="saveLeave">Terbitkan Surat Izin</x-loading-label>
                </x-primary-button>
            </x-modal-actions>
        </form>
    </x-modal>

    {{-- MODAL: Tolak Izin --}}
    <x-modal name="reject-modal" :show="$showRejectModal" max-width="md">
        <form wire:submit="confirmReject" class="p-6 space-y-4">
            <x-modal-header title="Tolak Pengajuan Izin" subtitle="Berikan alasan penolakan izin santri." icon="x-circle" />

            <div>
                <x-input-label for="rejection_reason" value="Alasan Penolakan" />
                <x-textarea id="rejection_reason" wire:model="rejection_reason" rows="3" class="mt-1 block w-full" placeholder="Contoh: Ada agenda ujian tahfidz akhir pekan / berkas belum lengkap..." />
                <x-input-error :messages="$errors->get('rejection_reason')" class="mt-1" />
            </div>

            <x-modal-actions>
                <x-secondary-button x-on:click="$dispatch('close-modal', 'reject-modal')">Batal</x-secondary-button>
                <x-danger-button type="submit">
                    <x-loading-label target="confirmReject">Tolak Izin</x-loading-label>
                </x-danger-button>
            </x-modal-actions>
        </form>
    </x-modal>

    {{-- MODAL: Tiket Gate Pass Satpam --}}
    <x-modal name="ticket-modal" :show="$showTicketModal" max-width="md">
        @if($activeTicketLeave)
            <div class="p-6 space-y-5">
                <div class="text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-300">
                        <x-icon name="qr-code" class="h-6 w-6" />
                    </div>
                    <h3 class="mt-3 text-lg font-bold text-slate-900 dark:text-white">Tiket Gate Pass Satpam</h3>
                    <p class="text-xs text-slate-500">Tunjukkan barcode / QR ini kepada petugas satpam gerbang pos keamanan.</p>
                </div>

                {{-- QR Code Card --}}
                <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-emerald-300 bg-emerald-50/50 p-6 dark:border-emerald-800 dark:bg-emerald-950/20">
                    <div role="img" aria-label="QR Gate Pass" class="h-44 w-44 rounded-xl border border-slate-200 bg-white p-2 shadow-sm dark:border-slate-700 [&>svg]:h-full [&>svg]:w-full">{!! \App\Support\QrCodeSvg::render($activeTicketLeave->qr_pass_token) !!}</div>
                    <div class="mt-3 font-mono text-xs font-bold text-slate-600 dark:text-slate-300">{{ $activeTicketLeave->leave_number }}</div>
                    <div class="text-[11px] text-slate-400">Token: {{ substr($activeTicketLeave->qr_pass_token, 0, 16) }}...</div>
                </div>

                {{-- Ringkasan Santri --}}
                <div class="rounded-xl bg-slate-50 p-4 text-xs space-y-2 dark:bg-slate-800/60">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Nama Santri:</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $activeTicketLeave->student->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Kategori Izin:</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ $activeTicketLeave->leaveTypeLabel() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Rencana Berangkat:</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ $activeTicketLeave->departure_plan->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Batas Waktu Kembali:</span>
                        <span class="font-semibold text-rose-600 dark:text-rose-400">{{ $activeTicketLeave->return_plan->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Penjemput:</span>
                        <span class="text-slate-800 dark:text-slate-200">{{ $activeTicketLeave->pickup_person ?: '-' }}</span>
                    </div>
                    <div class="flex justify-between pt-1 border-t border-slate-200 dark:border-slate-700">
                        <span class="text-slate-500">Status Saat Ini:</span>
                        <x-badge :color="$activeTicketLeave->statusBadgeColor()">{{ $activeTicketLeave->statusLabel() }}</x-badge>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3">
                    <a href="{{ route('boarding.leaves.print', $activeTicketLeave->id) }}" target="_blank" class="flex-1 inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        <x-icon name="printer" class="mr-1.5 h-4 w-4" />
                        Cetak Surat
                    </a>
                    <x-secondary-button x-on:click="$dispatch('close-modal', 'ticket-modal')" class="flex-1 justify-center">
                        Tutup
                    </x-secondary-button>
                </div>
            </div>
        @endif
    </x-modal>
</div>
