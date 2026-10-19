<?php

namespace App\Services;

use App\Models\AdmissionBatch;
use App\Models\AdmissionCandidate;
use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Support\Branding;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdmissionConversionService
{
    public function __construct(
        protected DocumentNumberGenerator $docGenerator,
        protected FonnteService $whatsappService,
    ) {}

    /**
     * Pendaftaran santri baru online / mandiri.
     *
     * @param  array{
     *     admission_batch_id: int,
     *     student_name: string,
     *     gender: 'male'|'female',
     *     nisn?: string|null,
     *     nik?: string|null,
     *     pob?: string|null,
     *     dob?: string|null,
     *     previous_school?: string|null,
     *     guardian_name: string,
     *     guardian_relationship?: string|null,
     *     guardian_phone: string,
     *     guardian_email?: string|null,
     *     address?: string|null,
     *     notes?: string|null
     * } $data
     */
    public function registerCandidate(array $data): AdmissionCandidate
    {
        $batch = AdmissionBatch::findOrFail($data['admission_batch_id']);

        if ($reason = $batch->closedReason()) {
            throw ValidationException::withMessages(['admission_batch_id' => $reason]);
        }

        $alreadyRegistered = AdmissionCandidate::query()
            ->where('admission_batch_id', $batch->id)
            ->whereRaw('lower(student_name) = ?', [mb_strtolower(trim($data['student_name']))])
            ->where('guardian_phone', $data['guardian_phone'])
            ->exists();

        if ($alreadyRegistered) {
            throw ValidationException::withMessages([
                'student_name' => 'Calon santri ini sudah terdaftar di gelombang yang sama. Gunakan "Cek Status Pendaftaran" dengan nomor registrasi yang diterima.',
            ]);
        }

        $regNumber = $this->docGenerator->next('REG');

        /** @var AdmissionCandidate $candidate */
        $candidate = AdmissionCandidate::create([
            'admission_batch_id' => $batch->id,
            'registration_number' => $regNumber,
            'student_name' => $data['student_name'],
            'gender' => $data['gender'],
            'nisn' => $data['nisn'] ?? null,
            'nik' => $data['nik'] ?? null,
            'pob' => $data['pob'] ?? null,
            'dob' => $data['dob'] ?? null,
            'previous_school' => $data['previous_school'] ?? null,
            'guardian_name' => $data['guardian_name'],
            'guardian_relationship' => $data['guardian_relationship'] ?? 'Ayah',
            'guardian_phone' => $data['guardian_phone'],
            'guardian_email' => $data['guardian_email'] ?? null,
            'address' => $data['address'] ?? null,
            'status' => 'registered',
            'notes' => $data['notes'] ?? null,
        ]);

        $this->notifyGuardianOnRegistration($candidate);

        return $candidate;
    }

    /**
     * Input nilai hasil tes seleksi (akademik, Al-Qur'an/tahfidz, wawancara).
     *
     * @param  array{
     *     test_score_academic?: float|null,
     *     test_score_quran?: float|null,
     *     test_score_interview?: float|null,
     *     test_notes?: string|null
     * } $scores
     */
    public function updateTestScores(AdmissionCandidate $candidate, array $scores): AdmissionCandidate
    {
        $updates = [
            'test_score_academic' => $scores['test_score_academic'] ?? $candidate->test_score_academic,
            'test_score_quran' => $scores['test_score_quran'] ?? $candidate->test_score_quran,
            'test_score_interview' => $scores['test_score_interview'] ?? $candidate->test_score_interview,
            'test_notes' => $scores['test_notes'] ?? $candidate->test_notes,
        ];

        if (in_array($candidate->status, ['registered', 'verified'], true)) {
            $updates['status'] = 'tested';
        }

        $candidate->update($updates);

        return $candidate;
    }

    /**
     * Update status seleksi (verifikasi, kelulusan, penolakan).
     */
    public function updateStatus(AdmissionCandidate $candidate, string $status): AdmissionCandidate
    {
        if ($candidate->isEnrolled() && $status !== 'enrolled') {
            throw ValidationException::withMessages([
                'status' => 'Santri yang sudah dikonversi menjadi santri aktif tidak dapat diubah statusnya.',
            ]);
        }

        $candidate->update(['status' => $status]);

        if ($status === 'passed') {
            $this->notifyGuardianOnPassing($candidate);
        }

        return $candidate;
    }

    /**
     * 1-KLIK KONVERSI CALON SANTRI MENJADI SANTRI AKTIF.
     * Otomatis membuat data Wali, Siswa/Santri, dan Penempatan Kelas Rombel tanpa input ulang.
     */
    public function convertCandidateToStudent(
        AdmissionCandidate $candidate,
        int $classId,
        ?string $customNis = null,
        bool $createUserAccount = true
    ): Student {
        if ($candidate->isEnrolled()) {
            throw ValidationException::withMessages([
                'candidate' => 'Calon santri ini sudah dikonversi sebelumnya menjadi santri aktif.',
            ]);
        }

        $schoolClass = SchoolClass::with('academicYear')->findOrFail($classId);

        return DB::transaction(function () use ($candidate, $schoolClass, $customNis, $createUserAccount) {
            // 1. Cari atau buat data Wali Murid (Guardian)
            $guardian = Guardian::where('phone', $candidate->guardian_phone)->first();

            if (! $guardian) {
                $guardian = Guardian::create([
                    'name' => $candidate->guardian_name,
                    'phone' => $candidate->guardian_phone,
                    'email' => $candidate->guardian_email,
                    'address' => $candidate->address,
                ]);
            }

            // 2. Buat akun portal wali murid jika diminta dan belum memiliki akun
            if ($createUserAccount && ! $guardian->user_id) {
                $cleanPhone = preg_replace('/[^0-9]/', '', $candidate->guardian_phone);
                $username = 'wali_'.$cleanPhone;

                // Cek keunikan username
                if (User::where('username', $username)->exists()) {
                    $username .= '_'.Str::lower(Str::random(3));
                }

                $email = $candidate->guardian_email;
                if (! $email || User::where('email', $email)->exists()) {
                    $email = $cleanPhone.'@wali.internal';
                }

                $user = User::create([
                    'name' => $candidate->guardian_name,
                    'username' => $username,
                    'email' => $email,
                    'password' => Hash::make('Bismillah123!'),
                ]);
                $user->assignRole('wali_murid');

                $guardian->update(['user_id' => $user->id]);
            }

            // 3. Tentukan NIS santri
            if ($customNis) {
                $nis = $customNis;
            } else {
                $yearPrefix = $schoolClass->academicYear ? substr($schoolClass->academicYear->name, 2, 2) : now()->format('y');
                $nextId = (Student::max('id') ?? 0) + 1;
                $nis = $yearPrefix.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
            }

            // 4. Buat record Siswa / Santri Aktif
            $student = Student::create([
                'guardian_id' => $guardian->id,
                'name' => $candidate->student_name,
                'gender' => $candidate->gender,
                'nis' => $nis,
                'nisn' => $candidate->nisn,
                'nik' => $candidate->nik,
                'pob' => $candidate->pob,
                'dob' => $candidate->dob,
                'address' => $candidate->address,
                'status' => 'active',
            ]);

            // 5. Buat enrollment penempatan rombel kelas
            StudentEnrollment::create([
                'student_id' => $student->id,
                'class_id' => $schoolClass->id,
                'academic_year_id' => $schoolClass->academic_year_id,
                'status' => 'active',
            ]);

            // 6. Tandai calon santri sebagai ENROLLED
            $candidate->update([
                'status' => 'enrolled',
                'converted_student_id' => $student->id,
                'converted_at' => now(),
            ]);

            // 7. Kirim pesan sambutan WhatsApp ke wali murid
            $this->notifyGuardianOnEnrollment($candidate, $student, $schoolClass);

            return $student;
        });
    }

    protected function notifyGuardianOnRegistration(AdmissionCandidate $candidate): void
    {
        $phone = $candidate->guardian_phone;
        if (empty($phone)) {
            return;
        }

        $appName = Branding::appName();
        $message = "Assalamu'alaikum Wr. Wb. Bpk/Ibu *{$candidate->guardian_name}*,\n\n".
            "Terima kasih telah mendaftarkan ananda *{$candidate->student_name}* dalam Penerimaan Peserta Didik Baru (PPDB) di *{$appName}*.\n\n".
            "📋 *Nomor Registrasi:* `{$candidate->registration_number}`\n".
            "📌 *Gelombang:* {$candidate->batch->name}\n".
            "📅 *Tanggal Daftar:* {$candidate->created_at->format('d/m/Y H:i')} WIB\n\n".
            "Simpan nomor registrasi ini untuk mengecek jadwal seleksi dan status kelulusan ananda.\n\n".
            "_{$appName}_";

        $this->whatsappService->send($phone, $message);
    }

    protected function notifyGuardianOnPassing(AdmissionCandidate $candidate): void
    {
        $phone = $candidate->guardian_phone;
        if (empty($phone)) {
            return;
        }

        $appName = Branding::appName();
        $avgScore = $candidate->averageScore() ? ' (Rata-rata Nilai: '.$candidate->averageScore().')' : '';
        $message = "Assalamu'alaikum Wr. Wb. Bpk/Ibu *{$candidate->guardian_name}*,\n\n".
            "Selamat! Ananda *{$candidate->student_name}* ({$candidate->registration_number}) dinyatakan *LULUS SELEKSI* PPDB di *{$appName}*{$avgScore}.\n\n".
            "Silakan segera menyelesaikan proses daftar ulang dan administrasi santri baru sesuai panduan panitia PPDB.\n\n".
            "_{$appName}_";

        $this->whatsappService->send($phone, $message);
    }

    protected function notifyGuardianOnEnrollment(AdmissionCandidate $candidate, Student $student, SchoolClass $schoolClass): void
    {
        $phone = $candidate->guardian_phone;
        if (empty($phone)) {
            return;
        }

        $appName = Branding::appName();
        $message = "Assalamu'alaikum Wr. Wb. Bpk/Ibu *{$candidate->guardian_name}*,\n\n".
            "Ahlan wa Sahlan! Ananda *{$student->name}* telah resmi terdaftar sebagai *SANTRI AKTIF* di *{$appName}*.\n\n".
            "🆔 *NIS Santri:* `{$student->nis}`\n".
            "🏫 *Kelas / Rombel:* {$schoolClass->name} (Tingkat {$schoolClass->grade_level})\n".
            "📅 *Tahun Ajaran:* {$schoolClass->academicYear?->name}\n\n".
            "Bapak/Ibu dapat masuk ke Portal Mandiri Wali Murid untuk memantau presensi, setoran tahfidz, dan tagihan SPP ananda.\n\n".
            "_{$appName}_";

        $this->whatsappService->send($phone, $message);
    }
}
