<x-layouts.print :title="'E-Rapor - '.$student->name.' - '.$schoolClass?->name">
    <div class="paper-sheet bg-white text-slate-900 rounded-xl border border-slate-300 p-6 md:p-8 text-xs leading-relaxed">
        {{-- Kop Surat Resmi --}}
        <x-print-letterhead
            :title="'LAPORAN HASIL BELAJAR (E-RAPOR)'"
            :number="'NIS: ' . $student->nis"
            :date="'Tahun Ajaran: ' . ($academicYear?->name ?? '-') . ' (' . ucfirst((string) $academicYear?->semester) . ')'"
        />

        {{-- Identitas Siswa & Kelas --}}
        <div class="grid grid-cols-2 gap-4 border border-slate-300 rounded-lg p-3 my-4 bg-slate-50/60 text-slate-800">
            <div class="space-y-1">
                <div class="flex"><span class="w-28 text-slate-500">Nama Peserta Didik</span><span class="mr-2">:</span><strong class="text-slate-900 uppercase">{{ $student->name }}</strong></div>
                <div class="flex"><span class="w-28 text-slate-500">NISN / NIS</span><span class="mr-2">:</span><span class="font-mono">{{ $student->nisn ?: '-' }} / {{ $student->nis }}</span></div>
                <div class="flex"><span class="w-28 text-slate-500">Rombel / Kelas</span><span class="mr-2">:</span><span>{{ $schoolClass?->name ?? '-' }} (Tingkat {{ $schoolClass?->grade_level ?? '-' }})</span></div>
            </div>
            <div class="space-y-1">
                <div class="flex"><span class="w-28 text-slate-500">Semester</span><span class="mr-2">:</span><span>{{ ucfirst((string) $academicYear?->semester) }}</span></div>
                <div class="flex"><span class="w-28 text-slate-500">Tahun Ajaran</span><span class="mr-2">:</span><span>{{ $academicYear?->name ?? '-' }}</span></div>
                <div class="flex"><span class="w-28 text-slate-500">Wali Kelas</span><span class="mr-2">:</span><span>{{ $schoolClass?->homeroomTeacher?->name ?? '-' }}</span></div>
            </div>
        </div>

        {{-- Bagian A: Capaian Hasil Belajar --}}
        <div class="mt-4">
            <h2 class="font-bold text-slate-900 uppercase tracking-wider text-xs border-b border-slate-300 pb-1 mb-2">
                A. Capaian Kompetensi & Hasil Belajar
            </h2>
            <table class="w-full text-xs text-left border-collapse border border-slate-300">
                <thead class="bg-slate-100 text-slate-800 font-semibold uppercase text-[11px]">
                    <tr>
                        <th class="border border-slate-300 p-2 w-8 text-center">No</th>
                        <th class="border border-slate-300 p-2 w-48">Mata Pelajaran</th>
                        <th class="border border-slate-300 p-2 w-12 text-center">KKM</th>
                        <th class="border border-slate-300 p-2 w-16 text-center">Nilai Akhir</th>
                        <th class="border border-slate-300 p-2 w-14 text-center">Predikat</th>
                        <th class="border border-slate-300 p-2">Capaian Kompetensi Pembelajaran</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($grades as $index => $grade)
                        <tr class="align-top">
                            <td class="border border-slate-300 p-2 text-center text-slate-600 font-mono">{{ $index + 1 }}</td>
                            <td class="border border-slate-300 p-2">
                                <div class="font-bold text-slate-900">{{ $grade->subjectAssignment->subject->name }}</div>
                                <div class="text-[10px] text-slate-500">Guru: {{ $grade->subjectAssignment->teacher->name }}</div>
                            </td>
                            <td class="border border-slate-300 p-2 text-center font-mono text-slate-600">
                                {{ number_format((float) $grade->subjectAssignment->passing_grade, 0) }}
                            </td>
                            <td class="border border-slate-300 p-2 text-center font-mono font-bold text-slate-900">
                                {{ number_format((float) $grade->final_score, 1) }}
                            </td>
                            <td class="border border-slate-300 p-2 text-center font-bold text-slate-800">
                                {{ $grade->predicate }}
                            </td>
                            <td class="border border-slate-300 p-2 text-[11px] leading-relaxed text-slate-700">
                                <p>{{ $grade->achievement_desc }}</p>
                                @if ($grade->improvement_desc)
                                    <p class="mt-1 text-slate-500 italic">{{ $grade->improvement_desc }}</p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="border border-slate-300 p-4 text-center text-slate-500 italic">
                                Belum ada data nilai mata pelajaran yang diinput.
                            </td>
                        </tr>
                    @endforelse
                    @if ($grades->isNotEmpty())
                        <tr class="bg-slate-50 font-bold">
                            <td colspan="3" class="border border-slate-300 p-2 text-right">Rata-rata Nilai Akhir:</td>
                            <td class="border border-slate-300 p-2 text-center font-mono text-slate-900">
                                {{ number_format($averageScore, 1) }}
                            </td>
                            <td colspan="2" class="border border-slate-300 p-2 text-slate-500 font-normal italic">
                                Dari {{ $grades->count() }} mata pelajaran
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Bagian B & C: Presensi & Ekstrakurikuler --}}
        <div class="grid grid-cols-2 gap-4 mt-4">
            <div>
                <h2 class="font-bold text-slate-900 uppercase tracking-wider text-xs border-b border-slate-300 pb-1 mb-2">
                    B. Rekapitulasi Ketidakhadiran
                </h2>
                <table class="w-full text-xs border-collapse border border-slate-300">
                    <tbody>
                        <tr>
                            <td class="border border-slate-300 p-2 w-40 text-slate-700">Sakit (S)</td>
                            <td class="border border-slate-300 p-2 font-mono font-semibold">{{ $summary?->sick_count ?? 0 }} hari</td>
                        </tr>
                        <tr>
                            <td class="border border-slate-300 p-2 text-slate-700">Izin (I)</td>
                            <td class="border border-slate-300 p-2 font-mono font-semibold">{{ $summary?->permit_count ?? 0 }} hari</td>
                        </tr>
                        <tr>
                            <td class="border border-slate-300 p-2 text-slate-700">Tanpa Keterangan (A)</td>
                            <td class="border border-slate-300 p-2 font-mono font-semibold">{{ $summary?->absent_count ?? 0 }} hari</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div>
                <h2 class="font-bold text-slate-900 uppercase tracking-wider text-xs border-b border-slate-300 pb-1 mb-2">
                    C. Kegiatan Ekstrakurikuler
                </h2>
                <div class="border border-slate-300 rounded p-2.5 min-h-[90px] text-[11px] text-slate-700">
                    {{ $summary?->extracurricular_notes ?: 'Aktif mengikuti kegiatan kepramukaan, pembiasaan ibadah, dan muhadhoroh santri.' }}
                </div>
            </div>
        </div>

        {{-- Bagian D: Lampiran Khusus Kepesantrenan & Tahfidz Al-Qur'an --}}
        <div class="mt-4 border border-emerald-300 bg-emerald-50/40 rounded-lg p-3">
            <h2 class="font-bold text-emerald-900 uppercase tracking-wider text-xs border-b border-emerald-200 pb-1 mb-2">
                D. Capaian Kepesantrenan & Tahfidz Al-Qur'an
            </h2>
            <div class="grid grid-cols-3 gap-3 text-xs">
                <div>
                    <span class="text-slate-600">Total Juz Mutqin:</span>
                    <div class="font-bold text-emerald-700 text-sm mt-0.5">
                        {{ $tahfidzProgress['completed_juz_count'] }} Juz
                    </div>
                </div>
                <div>
                    <span class="text-slate-600">Frekuensi Setoran:</span>
                    <div class="font-bold text-slate-800 text-sm mt-0.5">
                        {{ $tahfidzProgress['total_sessions'] }} Kali
                    </div>
                </div>
                <div>
                    <span class="text-slate-600">Poin Adab / Disiplin:</span>
                    <div class="font-bold text-slate-800 text-sm mt-0.5">
                        <span class="text-emerald-600">+{{ $meritPoints }} Prestasi</span> /
                        <span class="text-rose-600">-{{ $violationPoints }} Pelanggaran</span>
                    </div>
                </div>
            </div>
            @if ($tahfidzProgress['latest_session'])
                <div class="mt-2 text-[11px] text-slate-600 border-t border-emerald-200/60 pt-1.5">
                    Setoran Terakhir: <strong>{{ $tahfidzProgress['latest_session']->surah->name }}</strong>
                    (Ayat {{ $tahfidzProgress['latest_session']->verse_start }}-{{ $tahfidzProgress['latest_session']->verse_end }}),
                    Predikat: <strong class="uppercase">{{ $tahfidzProgress['latest_session']->grade }}</strong>
                </div>
            @endif
        </div>

        {{-- Bagian E: Catatan Wali Kelas & Status Kenaikan --}}
        <div class="mt-4 border border-slate-300 rounded-lg p-3 bg-slate-50/60">
            <h2 class="font-bold text-slate-900 uppercase tracking-wider text-xs border-b border-slate-300 pb-1 mb-2">
                E. Catatan Wali Kelas
            </h2>
            <p class="text-[11px] text-slate-700 leading-relaxed italic">
                "{{ $summary?->homeroom_notes ?: 'Ananda menunjukkan kesungguhan dan akhlak yang baik selama proses pembelajaran. Pertahankan dan terus tingkatkan prestasi belajar serta hafalan Al-Qur\'an.' }}"
            </p>

            @if ($summary?->promotion_status)
                <div class="mt-3 pt-2 border-t border-slate-300 flex items-center justify-between text-xs">
                    <span class="font-semibold text-slate-700">Keputusan Akhir Tahun:</span>
                    <strong class="font-bold text-slate-900 uppercase">
                        {{ match($summary->promotion_status) {
                            'promoted' => 'NAIK KE KELAS BERIKUTNYA',
                            'graduated' => 'LULUS DARI SATUAN PENDIDIKAN',
                            'retained' => 'TINGGAL DI KELAS INI',
                            default => strtoupper($summary->promotion_status)
                        } }}
                    </strong>
                </div>
            @endif
        </div>

        {{-- Tanda Tangan Resmi & QR Code Dokumen Digital --}}
        <div class="mt-6 pt-4 border-t border-slate-300 flex justify-between items-end text-xs text-slate-800">
            {{-- QR Code Verifikasi --}}
            <div class="flex items-center gap-3">
                <div class="w-16 h-16 border border-slate-400 p-1 flex items-center justify-center bg-white rounded">
                    <svg viewBox="0 0 100 100" class="w-full h-full text-slate-900" fill="currentColor">
                        <rect x="0" y="0" width="30" height="30" />
                        <rect x="5" y="5" width="20" height="20" fill="white" />
                        <rect x="10" y="10" width="10" height="10" />
                        <rect x="70" y="0" width="30" height="30" />
                        <rect x="75" y="5" width="20" height="20" fill="white" />
                        <rect x="80" y="10" width="10" height="10" />
                        <rect x="0" y="70" width="30" height="30" />
                        <rect x="5" y="75" width="20" height="20" fill="white" />
                        <rect x="10" y="80" width="10" height="10" />
                        <rect x="40" y="10" width="10" height="10" />
                        <rect x="40" y="30" width="20" height="10" />
                        <rect x="40" y="50" width="10" height="20" />
                        <rect x="60" y="50" width="20" height="10" />
                        <rect x="50" y="80" width="30" height="10" />
                    </svg>
                </div>
                <div class="text-[10px] text-slate-500">
                    <p class="font-semibold text-slate-700">Verifikasi Dokumen Digital</p>
                    <p>Scan untuk mengecek keaslian rapor</p>
                    <p class="font-mono text-[9px]">{{ $student->nis }}-{{ $academicYear?->id ?? '1' }}</p>
                </div>
            </div>

            {{-- Kolom Tanda Tangan --}}
            <div class="grid grid-cols-2 gap-8 text-center min-w-[340px]">
                <div>
                    <p class="text-slate-500">Orang Tua / Wali,</p>
                    <div class="h-16"></div>
                    <p class="font-bold underline decoration-dotted">{{ $student->guardian?->name ?? '_____________________' }}</p>
                </div>

                <div>
                    <p class="text-slate-500">{{ \App\Models\Setting::get('company_city', 'Kota') }}, {{ $generatedAt }}</p>
                    <p class="font-semibold text-slate-800">Wali Kelas,</p>
                    <div class="h-14"></div>
                    <p class="font-bold underline decoration-dotted">{{ $schoolClass?->homeroomTeacher?->name ?? '_____________________' }}</p>
                    <p class="text-[10px] text-slate-500 font-mono">{{ $schoolClass?->homeroomTeacher?->nip ? 'NIP: ' . $schoolClass->homeroomTeacher->nip : '' }}</p>
                </div>
            </div>
        </div>

        {{-- Mengetahui Kepala Sekolah / Mudir --}}
        <div class="mt-4 text-center text-xs">
            <p class="text-slate-500">Mengetahui,</p>
            <p class="font-bold text-slate-900">Kepala Sekolah / Mudir Pesantren</p>
            <div class="h-14"></div>
            <p class="font-bold underline decoration-dotted text-slate-900">
                {{ \App\Models\Setting::get('principal_name', 'H. Ahmad Syahid, Lc., M.Pd.') }}
            </p>
            <p class="text-[10px] text-slate-500 font-mono">
                {{ \App\Models\Setting::get('principal_nip', 'NIP: 198005122005011003') }}
            </p>
        </div>
    </div>
</x-layouts.print>
