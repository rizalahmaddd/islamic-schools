<x-layouts.print :title="'Bukti Pendaftaran PPDB - '.$candidate->registration_number">
    <div class="paper-sheet bg-white text-slate-900 rounded-xl border border-slate-300 p-6 md:p-8 max-w-3xl mx-auto space-y-6">
        <x-print-letterhead
            :title="'BUKTI PENDAFTARAN SANTRI BARU (PPDB ONLINE)'"
            :number="'NO. REGISTRASI: '.$candidate->registration_number"
            :date="'Tanggal Terdaftar: '.$candidate->created_at->translatedFormat('d F Y H:i').' WIB'"
        >
            <div class="text-right text-xs text-slate-600">
                <div>Gelombang: <span class="font-bold text-slate-800">{{ $batch->name }}</span></div>
                <div>Status: <span class="font-bold uppercase text-emerald-700">{{ $candidate->statusLabel() }}</span></div>
            </div>
        </x-print-letterhead>

        {{-- Nomor Registrasi Box & QR Code --}}
        <div class="grid grid-cols-3 gap-6 items-center rounded-xl border border-slate-200 bg-slate-50 p-4">
            <div class="col-span-2 space-y-2 text-xs">
                <span class="text-slate-500 uppercase font-semibold text-[11px] block">Data Calon Santri:</span>
                <div class="grid grid-cols-2 gap-y-2">
                    <div>
                        <span class="text-slate-500 block">Nama Lengkap:</span>
                        <strong class="text-sm text-slate-900">{{ $candidate->student_name }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Jenis Kelamin:</span>
                        <span class="font-medium text-slate-800">{{ $candidate->gender === 'male' ? 'Laki-laki (Putra)' : 'Perempuan (Putri)' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">NISN / NIK:</span>
                        <span class="font-mono text-slate-800">{{ $candidate->nisn ?: '-' }} / {{ $candidate->nik ?: '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Tempat, Tanggal Lahir:</span>
                        <span class="text-slate-800">{{ $candidate->pob ?: '-' }}, {{ $candidate->dob ? $candidate->dob->format('d/m/Y') : '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Asal Sekolah:</span>
                        <span class="text-slate-800">{{ $candidate->previous_school ?: '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="col-span-1 flex flex-col items-center justify-center border-l border-slate-200 pl-4">
                <div role="img" aria-label="QR Registrasi" class="h-28 w-28 rounded-lg border border-slate-300 bg-white p-1 [&>svg]:h-full [&>svg]:w-full">{!! \App\Support\QrCodeSvg::render($candidate->registration_number) !!}</div>
                <span class="mt-1 font-mono text-[10px] font-bold text-slate-600">VERIFIKASI PANITIA</span>
            </div>
        </div>

        {{-- Identitas Orang Tua / Wali --}}
        <table class="w-full text-xs text-left border-collapse border border-slate-300">
            <tbody>
                <tr class="border-b border-slate-300">
                    <th class="p-2.5 bg-slate-100 w-44 font-semibold text-slate-700">Nama Orang Tua / Wali</th>
                    <td class="p-2.5 font-bold text-slate-900">{{ $candidate->guardian_name }} ({{ $candidate->guardian_relationship }})</td>
                </tr>
                <tr class="border-b border-slate-300">
                    <th class="p-2.5 bg-slate-100 font-semibold text-slate-700">Nomor WhatsApp Aktif</th>
                    <td class="p-2.5 font-mono text-slate-900">{{ $candidate->guardian_phone }}</td>
                </tr>
                <tr class="border-b border-slate-300">
                    <th class="p-2.5 bg-slate-100 font-semibold text-slate-700">Alamat Domisili</th>
                    <td class="p-2.5 text-slate-800">{{ $candidate->address ?: '-' }}</td>
                </tr>
                <tr class="border-b border-slate-300">
                    <th class="p-2.5 bg-slate-100 font-semibold text-slate-700">Biaya Formulir Pendaftaran</th>
                    <td class="p-2.5 font-bold text-slate-900">Rp {{ number_format((float) $batch->registration_fee, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Petunjuk Tahapan Selanjutnya --}}
        <div class="rounded-lg bg-emerald-50 p-4 text-xs text-emerald-950 border border-emerald-200 space-y-1.5">
            <strong>Petunjuk Tahapan Seleksi Masuk:</strong>
            <ol class="list-decimal pl-4 space-y-1 text-[11px] text-emerald-900">
                <li>Bawa lembar cetak bukti pendaftaran ini saat menghadiri tes seleksi masuk di kampus pesantren.</li>
                <li>Materi seleksi mencakup: Tes Kemampuan Akademik Dasar, Tes Baca Tulis Al-Qur'an (Tahsin & Tahfidz), dan Wawancara Wali Santri.</li>
                <li>Pengumuman hasil seleksi kelulusan akan diumumkan melalui Portal PPDB dan notifikasi WhatsApp resmi sekolah.</li>
            </ol>
        </div>

        {{-- Tanda Tangan --}}
        <div class="grid grid-cols-2 gap-4 text-center text-xs pt-4">
            <div>
                <div class="text-slate-500 mb-14">Orang Tua / Wali Calon Santri,</div>
                <div class="font-bold text-slate-900 border-b border-slate-400 pb-1 inline-block min-w-40">
                    {{ $candidate->guardian_name }}
                </div>
            </div>

            <div>
                <div class="text-slate-500 mb-14">Panitia Penerimaan Santri Baru (PPDB),</div>
                <div class="font-bold text-slate-900 border-b border-slate-400 pb-1 inline-block min-w-40">
                    Tim Panitia PPDB
                </div>
            </div>
        </div>
    </div>
</x-layouts.print>
