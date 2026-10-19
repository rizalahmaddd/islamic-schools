<x-layouts.print :title="'Rekapitulasi Presensi - '.$schoolClass->name.' - '.$monthName.' '.$year">
    <div class="paper-sheet bg-white text-slate-900 rounded-xl border border-slate-300 p-6 md:p-8">
        <x-print-letterhead
            :title="'REKAPITULASI PRESENSI SANTRI'"
            :number="'ROMBEL: '.$schoolClass->name.' (TINGKAT '.$schoolClass->grade_level.')'"
            :date="'Periode: '.$monthName.' '.$year"
        >
            <div class="text-right text-xs text-slate-600">
                <div>Tahun Ajaran: <strong>{{ $schoolClass->academicYear?->name ?? '-' }} ({{ ucfirst((string) $schoolClass->academicYear?->semester) }})</strong></div>
                <div>Wali Kelas: <strong>{{ $schoolClass->homeroomTeacher?->name ?? '-' }}</strong></div>
            </div>
        </x-print-letterhead>

        {{-- Tabel Data Rekap --}}
        <table class="w-full text-xs text-left border-collapse border border-slate-300 mt-4">
            <thead class="bg-slate-100 text-slate-800 uppercase font-semibold">
                <tr>
                    <th class="border border-slate-300 p-2 w-10 text-center">No</th>
                    <th class="border border-slate-300 p-2 w-24">NIS</th>
                    <th class="border border-slate-300 p-2">Nama Santri / Siswa</th>
                    <th class="border border-slate-300 p-2 w-12 text-center">L/P</th>
                    <th class="border border-slate-300 p-2 w-14 text-center">Hadir</th>
                    <th class="border border-slate-300 p-2 w-14 text-center">Sakit</th>
                    <th class="border border-slate-300 p-2 w-14 text-center">Izin</th>
                    <th class="border border-slate-300 p-2 w-14 text-center">Alpa</th>
                    <th class="border border-slate-300 p-2 w-16 text-center">Total</th>
                    <th class="border border-slate-300 p-2 w-20 text-right">% Hadir</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recap as $index => $row)
                    <tr>
                        <td class="border border-slate-300 p-2 text-center text-slate-600">{{ $index + 1 }}</td>
                        <td class="border border-slate-300 p-2 font-mono">{{ $row['student']->nis }}</td>
                        <td class="border border-slate-300 p-2 font-medium">{{ $row['student']->name }}</td>
                        <td class="border border-slate-300 p-2 text-center">{{ $row['student']->gender }}</td>
                        <td class="border border-slate-300 p-2 text-center font-bold">{{ $row['present'] }}</td>
                        <td class="border border-slate-300 p-2 text-center">{{ $row['sick'] }}</td>
                        <td class="border border-slate-300 p-2 text-center">{{ $row['permit'] }}</td>
                        <td class="border border-slate-300 p-2 text-center">{{ $row['absent'] }}</td>
                        <td class="border border-slate-300 p-2 text-center font-medium">{{ $row['total_recorded'] }}</td>
                        <td class="border border-slate-300 p-2 text-right font-mono font-bold">{{ $row['rate'] }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="border border-slate-300 p-4 text-center text-slate-500 italic">
                            Tidak ada data presensi santri pada periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Kolom Tanda Tangan Resmi --}}
        <div class="mt-8 grid grid-cols-2 text-xs text-center text-slate-800">
            <div>
                <p>Mengetahui,</p>
                <p class="font-semibold">Kepala Sekolah / Mudir</p>
                <div class="h-20"></div>
                <p class="font-bold underline decoration-dotted">{{ \App\Models\Setting::get('principal_name', '_______________________') }}</p>
                <p class="text-slate-500 font-mono">NIP. {{ \App\Models\Setting::get('principal_nip', '-') }}</p>
            </div>

            <div>
                <p>{{ \App\Models\Setting::get('company_city', 'Kota') }}, {{ now()->translatedFormat('d F Y') }}</p>
                <p class="font-semibold">Wali Kelas Pengampu</p>
                <div class="h-20"></div>
                <p class="font-bold underline decoration-dotted">{{ $schoolClass->homeroomTeacher?->name ?? '_______________________' }}</p>
                <p class="text-slate-500 font-mono">NIP. {{ $schoolClass->homeroomTeacher?->nip ?? '-' }}</p>
            </div>
        </div>
    </div>
</x-layouts.print>
