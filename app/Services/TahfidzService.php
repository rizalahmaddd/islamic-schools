<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentConductRecord;
use App\Models\TahfidzSession;

class TahfidzService
{
    /**
     * Hitung capaian hafalan 30 Juz santri.
     *
     * @return array{
     *     juz_progress: array<int, array{juz: int, status: string, total_sessions: int, latest_grade: ?string}>,
     *     completed_juz_count: int,
     *     in_progress_juz_count: int,
     *     total_sessions_count: int,
     *     latest_session: ?TahfidzSession
     * }
     */
    public function calculateStudentProgress(Student $student): array
    {
        $sessions = TahfidzSession::query()
            ->with('surah')
            ->where('student_id', $student->id)
            ->orderBy('recorded_at')
            ->get();

        $groupedByJuz = $sessions->groupBy('juz');
        $juzProgress = [];
        $completedCount = 0;
        $inProgressCount = 0;

        for ($juz = 1; $juz <= 30; $juz++) {
            $juzSessions = $groupedByJuz->get($juz, collect());
            $count = $juzSessions->count();
            $latest = $juzSessions->last();

            // Kriteria: Jika sudah ada minimal 1 sesi dengan nilai A/B (Mumtaz/Jayyid Jiddan) pada sesi Ziyadah, dianggap mutqin/in-progress
            $status = 'empty';
            if ($count > 0) {
                $hasPassed = $juzSessions->contains(fn ($s) => in_array($s->grade, ['A', 'B'], true));
                if ($hasPassed && $juzSessions->where('session_type', 'ziyadah')->count() >= 1) {
                    $status = 'completed';
                    $completedCount++;
                } else {
                    $status = 'in_progress';
                    $inProgressCount++;
                }
            }

            $juzProgress[$juz] = [
                'juz' => $juz,
                'status' => $status, // completed, in_progress, empty
                'total_sessions' => $count,
                'latest_grade' => $latest?->grade,
            ];
        }

        return [
            'juz_progress' => $juzProgress,
            'completed_juz_count' => $completedCount,
            'in_progress_juz_count' => $inProgressCount,
            'total_sessions_count' => $sessions->count(),
            'latest_session' => $sessions->last(),
        ];
    }

    /**
     * Kirim notifikasi WhatsApp ke wali santri setiap kali ananda selesai setoran tahfidz.
     *
     * @return array{status: bool, message: string}
     */
    public function sendTahfidzWhatsApp(TahfidzSession $session): array
    {
        $session->loadMissing(['student.guardian', 'student.currentEnrollment.schoolClass', 'surah', 'teacher']);
        $guardianPhone = $session->student?->guardian?->phone;

        if (! $guardianPhone) {
            return ['status' => false, 'message' => 'Nomor WhatsApp wali santri belum terdaftar.'];
        }

        $studentName = $session->student->name;
        $className = $session->student->currentEnrollment?->schoolClass?->name ?? '-';
        $surahName = $session->surah?->name_latin ?? 'Al-Qur\'an';
        $date = $session->recorded_at->translatedFormat('d F Y H:i');
        $teacherName = $session->teacher?->name ?? 'Ustadz Pengampu';

        $message = "Assalamu'alaikum Warahmatullahi Wabarakatuh.\n\n"
            ."Yth. Orang Tua / Wali dari ananda *{$studentName}* (Kelas {$className}),\n\n"
            ."Alhamdulillah, ananda baru saja menyelesaikan setoran hafalan Al-Qur'an pada:\n"
            ."🗓️ *Waktu:* {$date}\n"
            ."📖 *Surat:* QS. {$surahName} (Ayat {$session->verse_start} - {$session->verse_end})\n"
            ."🔢 *Juz:* Juz {$session->juz}\n"
            ."📌 *Jenis:* {$session->session_type_label}\n"
            ."⭐ *Predikat Kelancaran:* *{$session->grade_label}*\n"
            .($session->tajwid_notes ? "📝 *Catatan Ustadz:* {$session->tajwid_notes}\n" : '')
            ."👨‍🏫 *Pembimbing:* {$teacherName}\n\n"
            ."Semoga ananda senantiasa istiqomah dan diberkahi dalam menghafal Al-Qur'an. Aamiin.\n\n"
            .'_Pesan otomatis dikirim oleh Sistem Pesantren & Tahfidz_';

        return app(FonnteService::class)->send($guardianPhone, $message);
    }

    /**
     * Kirim notifikasi WhatsApp ke wali santri jika terdapat catatan pelanggaran adab / disiplin.
     *
     * @return array{status: bool, message: string}
     */
    public function sendConductNotification(StudentConductRecord $record): array
    {
        $record->loadMissing(['student.guardian', 'student.currentEnrollment.schoolClass']);
        $guardianPhone = $record->student?->guardian?->phone;

        if (! $guardianPhone) {
            return ['status' => false, 'message' => 'Nomor WhatsApp wali santri belum terdaftar.'];
        }

        $student = $record->student;
        $studentName = $student->name;
        $className = $student->currentEnrollment?->schoolClass?->name ?? '-';
        $cumulativeViolation = $student->totalViolationPoints();

        $statusSP = match (true) {
            $cumulativeViolation >= 100 => 'PERINGATAN TINGKAT 3 (SP3 / Sidang Pleno)',
            $cumulativeViolation >= 75 => 'SURAT PERINGATAN 2 (SP2)',
            $cumulativeViolation >= 50 => 'SURAT PERINGATAN 1 (SP1)',
            $cumulativeViolation >= 25 => 'PERINGATAN AWAL PEMBINAAN',
            default => 'Catatan Kedisiplinan Harian',
        };

        if ($record->type === 'violation') {
            $message = "Assalamu'alaikum Warahmatullahi Wabarakatuh.\n\n"
                ."Yth. Orang Tua / Wali dari ananda *{$studentName}* (Kelas {$className}),\n\n"
                ."Menginformasikan catatan kedisiplinan & adab ananda di pesantren:\n"
                ."⚠️ *Pelanggaran:* {$record->description}\n"
                ."📂 *Kategori:* {$record->category}\n"
                ."🔺 *Poin Pelanggaran:* +{$record->points} Poin\n"
                ."📊 *Akumulasi Poin:* *{$cumulativeViolation} Poin*\n"
                ."⚖️ *Status:* *{$statusSP}*\n"
                .($record->action_taken ? "🛠️ *Tindakan Pembinaan:* {$record->action_taken}\n\n" : "\n")
                ."Mohon doa dan kerja sama Bapak/Ibu untuk senantiasa menasihati ananda agar menjadi pribadi yang berakhlak mulia.\n\n"
                .'_Pesan resmi Bagian Kepengasuhan & Kedisiplinan Pesantren_';
        } else {
            $message = "Assalamu'alaikum Warahmatullahi Wabarakatuh.\n\n"
                ."Yth. Orang Tua / Wali dari ananda *{$studentName}* (Kelas {$className}),\n\n"
                ."Kabar gembira! Ananda baru saja mencatatkan prestasi / kebaikan di pesantren:\n"
                ."🏆 *Prestasi / Adab:* {$record->description}\n"
                ."📂 *Kategori:* {$record->category}\n"
                ."⭐ *Poin Prestasi:* +{$record->points} Poin\n\n"
                ."Semoga memotivasi ananda untuk terus berprestasi dan berakhlak mulia. Jazakumullahu khairan.\n\n"
                .'_Bagian Kepengasuhan Pesantren_';
        }

        return app(FonnteService::class)->send($guardianPhone, $message);
    }
}
