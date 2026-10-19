<x-layouts.print :title="'Surat Izin Santri - '.$leave->leave_number">
    <div class="paper-sheet bg-white text-slate-900 rounded-xl border border-slate-300 p-6 md:p-8 max-w-3xl mx-auto space-y-6">
        <x-print-letterhead
            :title="'SURAT IZIN KELUAR / PULANG SANTRI (GATE PASS)'"
            :number="'NO: '.$leave->leave_number"
            :date="'Tanggal Terbit: '.$leave->created_at->translatedFormat('d F Y')"
        >
            <div class="text-right text-xs text-slate-600">
                <div>Kategori Izin: <span class="font-bold uppercase text-slate-800">{{ $leave->leaveTypeLabel() }}</span></div>
                <div>Status: <span class="font-bold uppercase {{ $leave->isApproved() || $leave->isActive() ? 'text-emerald-700' : 'text-slate-700' }}">{{ $leave->statusLabel() }}</span></div>
            </div>
        </x-print-letterhead>

        {{-- QR Code & Data Santri Grid --}}
        <div class="grid grid-cols-3 gap-6 items-center rounded-xl border border-slate-200 bg-slate-50 p-4">
            <div class="col-span-2 space-y-2 text-xs">
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <span class="text-slate-500 block">Nama Santri:</span>
                        <strong class="text-sm text-slate-900">{{ $student->name }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block">NIS / Rombel:</span>
                        <span class="font-mono text-slate-800">{{ $student->nis ?: '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Kamar Asrama:</span>
                        @php
                            $placement = $student->dormPlacements->first();
                        @endphp
                        <span class="text-slate-800 font-medium">
                            {{ $placement ? $placement->room->building->name.' - '.$placement->room->room_number : 'Asrama Santri' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Wali Santri / No. HP:</span>
                        <span class="text-slate-800">{{ $student->guardian?->name ?: '-' }} ({{ $student->guardian?->phone ?: '-' }})</span>
                    </div>
                </div>
            </div>

            <div class="col-span-1 flex flex-col items-center justify-center border-l border-slate-200 pl-4">
                <div role="img" aria-label="QR Gate Pass" class="h-28 w-28 rounded-lg border border-slate-300 bg-white p-1 [&>svg]:h-full [&>svg]:w-full">{!! \App\Support\QrCodeSvg::render($leave->qr_pass_token) !!}</div>
                <span class="mt-1 font-mono text-[10px] font-bold text-slate-600">SCAN SATPAM GERBANG</span>
            </div>
        </div>

        {{-- Jadwal & Rincian Perizinan --}}
        <table class="w-full text-xs text-left border-collapse border border-slate-300">
            <tbody>
                <tr class="border-b border-slate-300">
                    <th class="p-2.5 bg-slate-100 w-44 font-semibold text-slate-700">Rencana Berangkat</th>
                    <td class="p-2.5 font-bold text-slate-900">{{ $leave->departure_plan->format('d/m/Y H:i') }} WIB</td>
                </tr>
                <tr class="border-b border-slate-300">
                    <th class="p-2.5 bg-slate-100 font-semibold text-slate-700">Batas Waktu Kembali</th>
                    <td class="p-2.5 font-bold text-rose-700">{{ $leave->return_plan->format('d/m/Y H:i') }} WIB</td>
                </tr>
                <tr class="border-b border-slate-300">
                    <th class="p-2.5 bg-slate-100 font-semibold text-slate-700">Penjemput / Pendamping</th>
                    <td class="p-2.5 text-slate-800">{{ $leave->pickup_person ?: 'Mandiri / Wali Santri' }}</td>
                </tr>
                <tr class="border-b border-slate-300">
                    <th class="p-2.5 bg-slate-100 font-semibold text-slate-700">Keperluan / Alasan</th>
                    <td class="p-2.5 text-slate-800">{{ $leave->reason }}</td>
                </tr>
                <tr>
                    <th class="p-2.5 bg-slate-100 font-semibold text-slate-700">Catatan Khusus</th>
                    <td class="p-2.5 text-slate-600">{{ $leave->notes ?: 'Santri wajib menjaga nama baik almamater pesantren dan kembali tepat waktu.' }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Petunjuk Penggunaan Gate Pass --}}
        <div class="rounded-lg bg-amber-50 p-3 text-[11px] text-amber-900 border border-amber-200">
            <strong>Instruksi Pos Keamanan Gerbang:</strong>
            <ol class="list-decimal pl-4 mt-1 space-y-0.5">
                <li>Santri wajib memperlihatkan lembar surat/QR code ini kepada petugas satpam saat keluar dan kembali.</li>
                <li>Petugas satpam memindai QR code via modul Scanner Satpam untuk mencatat jam riil pergerakan santri.</li>
                <li>Keterlambatan kembali dari batas waktu yang ditentukan akan otomatis dikenakan sanksi kedisiplinan santri.</li>
            </ol>
        </div>

        {{-- Lembar Tanda Tangan --}}
        <div class="grid grid-cols-3 gap-4 text-center text-xs pt-4">
            <div>
                <div class="text-slate-500 mb-14">Wali Santri / Penjemput,</div>
                <div class="font-bold text-slate-900 border-b border-slate-400 pb-1 inline-block min-w-32">
                    {{ $student->guardian?->name ?: '(...........................)' }}
                </div>
            </div>

            <div>
                <div class="text-slate-500 mb-14">Satpam Pos Gerbang,</div>
                <div class="font-bold text-slate-900 border-b border-slate-400 pb-1 inline-block min-w-32">
                    {{ $leave->securityDepartureUser?->name ?: '(...........................)' }}
                </div>
            </div>

            <div>
                <div class="text-slate-500 mb-14">Musyrif / Pembina Asrama,</div>
                <div class="font-bold text-slate-900 border-b border-slate-400 pb-1 inline-block min-w-32">
                    {{ $leave->approver?->name ?: 'Ustadz Pembina' }}
                </div>
            </div>
        </div>
    </div>
</x-layouts.print>
