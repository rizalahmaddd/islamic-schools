<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\StudentConductRecord;
use App\Models\StudentLeaveRequest;
use App\Models\User;
use App\Support\Branding;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BoardingLeaveService
{
    public function __construct(
        protected DocumentNumberGenerator $docGenerator,
        protected FonnteService $whatsappService,
    ) {}

    /**
     * Buat surat izin keluar/pulang baru untuk santri.
     *
     * @param  array{
     *     academic_year_id?: int|null,
     *     leave_type: 'weekend'|'sick'|'family'|'dispensation',
     *     departure_plan: string|\DateTimeInterface,
     *     return_plan: string|\DateTimeInterface,
     *     reason: string,
     *     pickup_person?: string|null,
     *     notes?: string|null
     * } $data
     */
    public function createLeaveRequest(Student $student, array $data, ?User $creator = null): StudentLeaveRequest
    {
        $academicYearId = $data['academic_year_id']
            ?? $student->enrollments()->latest()->first()?->academic_year_id
            ?? AcademicYear::where('is_active', true)->first()?->id;

        if (! $academicYearId) {
            $academicYear = AcademicYear::firstOrCreate(
                ['name' => '2026/2027', 'semester' => 'ganjil'],
                ['start_date' => now()->startOfYear(), 'end_date' => now()->endOfYear(), 'is_active' => true]
            );
            $academicYearId = $academicYear->id;
        }

        $overlappingLeave = StudentLeaveRequest::query()
            ->where('student_id', $student->id)
            ->whereNotIn('status', ['returned', 'late', 'rejected'])
            ->where('departure_plan', '<', $data['return_plan'])
            ->where('return_plan', '>', $data['departure_plan'])
            ->first();

        if ($overlappingLeave) {
            throw ValidationException::withMessages([
                'student_id' => "Santri ini masih punya izin {$overlappingLeave->leave_number} ({$overlappingLeave->statusLabel()}) pada rentang waktu yang sama.",
            ]);
        }

        $leaveNumber = $this->docGenerator->next('IZN');
        $qrToken = Str::random(32);

        return StudentLeaveRequest::create([
            'leave_number' => $leaveNumber,
            'student_id' => $student->id,
            'academic_year_id' => $academicYearId,
            'leave_type' => $data['leave_type'] ?? 'weekend',
            'reason' => $data['reason'],
            'departure_plan' => $data['departure_plan'],
            'return_plan' => $data['return_plan'],
            'pickup_person' => $data['pickup_person'] ?? null,
            'status' => 'pending',
            'qr_pass_token' => $qrToken,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Persetujuan izin santri oleh musyrif atau pimpinan asrama.
     */
    public function approveLeave(
        StudentLeaveRequest $leave,
        User $approver,
        string $status = 'approved_mentor'
    ): StudentLeaveRequest {
        if (! $leave->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'Izin santri ini sudah diproses dan tidak dapat disetujui ulang.',
            ]);
        }

        $leave->update([
            'status' => $status,
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        // Kirim WhatsApp pemberitahuan ke wali murid
        $this->notifyGuardianOnApproval($leave);

        return $leave;
    }

    /**
     * Penolakan pengajuan izin santri.
     */
    public function rejectLeave(
        StudentLeaveRequest $leave,
        User $approver,
        string $rejectionReason
    ): StudentLeaveRequest {
        if (! $leave->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'Izin santri ini sudah diproses dan tidak dapat ditolak ulang.',
            ]);
        }

        $leave->update([
            'status' => 'rejected',
            'approved_by' => $approver->id,
            'rejection_reason' => $rejectionReason,
        ]);

        return $leave;
    }

    /**
     * Satpam gerbang memindai tiket QR saat santri KELUAR gerbang pesantren.
     *
     * @return array{success: bool, message: string, leave: StudentLeaveRequest}
     */
    public function scanSecurityExit(string $qrToken, User $securityUser): array
    {
        /** @var StudentLeaveRequest|null $leave */
        $leave = StudentLeaveRequest::where('qr_pass_token', $qrToken)->first();

        if (! $leave) {
            throw ValidationException::withMessages([
                'qr_token' => 'Tiket Gate Pass tidak valid atau tidak ditemukan dalam sistem.',
            ]);
        }

        if ($leave->isActive()) {
            throw ValidationException::withMessages([
                'qr_token' => 'Santri ini sudah tercatat KELUAR gerbang pada '.$leave->actual_departure_at?->format('d/m/Y H:i').'.',
            ]);
        }

        if ($leave->isReturned()) {
            throw ValidationException::withMessages([
                'qr_token' => 'Tiket izin ini sudah selesai/telah kembali ke pondok.',
            ]);
        }

        if ($leave->isRejected()) {
            throw ValidationException::withMessages([
                'qr_token' => 'Tiket izin ini telah DITOLAK: '.$leave->rejection_reason,
            ]);
        }

        if ($leave->isPending()) {
            throw ValidationException::withMessages([
                'qr_token' => 'Tiket izin ini belum disetujui oleh Musyrif / Pimpinan Asrama.',
            ]);
        }

        if (now()->lt($leave->departure_plan->copy()->subHour())) {
            throw ValidationException::withMessages([
                'qr_token' => 'Izin baru berlaku mulai '.$leave->departure_plan->format('d/m/Y H:i').' WIB. Santri belum boleh keluar gerbang.',
            ]);
        }

        if (now()->gt($leave->return_plan)) {
            throw ValidationException::withMessages([
                'qr_token' => 'Izin ini sudah melewati batas waktu kembali ('.$leave->return_plan->format('d/m/Y H:i').' WIB). Ajukan izin baru.',
            ]);
        }

        $departureTime = now();
        $leave->update([
            'status' => 'active',
            'actual_departure_at' => $departureTime,
            'security_departure_user_id' => $securityUser->id,
        ]);

        // Kirim WhatsApp real-time ke wali santri
        $this->notifyGuardianOnExit($leave, $departureTime);

        return [
            'success' => true,
            'message' => 'Santri '.$leave->student->name.' resmi KELUAR gerbang pada '.$departureTime->format('d/m/Y H:i').'. Notifikasi WA telah dikirimkan ke wali.',
            'leave' => $leave,
        ];
    }

    /**
     * Satpam gerbang memindai tiket QR saat santri KEMBALI ke pesantren.
     *
     * @return array{success: bool, is_late: bool, message: string, leave: StudentLeaveRequest}
     */
    public function scanSecurityReturn(string $qrToken, User $securityUser, ?string $notes = null): array
    {
        /** @var StudentLeaveRequest|null $leave */
        $leave = StudentLeaveRequest::where('qr_pass_token', $qrToken)->first();

        if (! $leave) {
            throw ValidationException::withMessages([
                'qr_token' => 'Tiket Gate Pass tidak valid atau tidak ditemukan dalam sistem.',
            ]);
        }

        if ($leave->isReturned()) {
            throw ValidationException::withMessages([
                'qr_token' => 'Santri ini sudah tercatat kembali ke pondok pada '.$leave->actual_returned_at?->format('d/m/Y H:i').'.',
            ]);
        }

        if (! $leave->isActive()) {
            throw ValidationException::withMessages([
                'qr_token' => 'Status izin santri belum aktif keluar gerbang (status saat ini: '.$leave->statusLabel().').',
            ]);
        }

        $returnTime = now();
        $isLate = $returnTime->greaterThan($leave->return_plan);
        $finalStatus = $isLate ? 'late' : 'returned';

        $leave->update([
            'status' => $finalStatus,
            'actual_returned_at' => $returnTime,
            'security_return_user_id' => $securityUser->id,
            'notes' => $notes ?: $leave->notes,
        ]);

        // Jika terlambat kembali: otomatis catat pelanggaran disiplin santri di Fase 5
        if ($isLate) {
            $diffMinutes = (int) $leave->return_plan->diffInMinutes($returnTime);
            $hours = intdiv($diffMinutes, 60);
            $minutes = $diffMinutes % 60;
            $lateDuration = $hours > 0 ? "{$hours} jam {$minutes} menit" : "{$minutes} menit";

            StudentConductRecord::create([
                'student_id' => $leave->student_id,
                'date' => $returnTime->toDateString(),
                'type' => 'violation',
                'points' => 10,
                'category' => 'Disiplin Asrama',
                'description' => "Terlambat kembali ke asrama ({$leave->leave_number}). Rencana: {$leave->return_plan->format('d/m/Y H:i')}, riil kembali: {$returnTime->format('d/m/Y H:i')} (telat {$lateDuration}).",
                'action_taken' => 'Peringatan otomatis sistem gate pass & pembinaan musyrif asrama',
                'recorded_by' => $securityUser->id,
            ]);
        }

        // Kirim WhatsApp real-time ke wali santri
        $this->notifyGuardianOnReturn($leave, $returnTime, $isLate);

        $msg = $isLate
            ? 'Santri '.$leave->student->name.' KEMBALI TERLAMBAT pada '.$returnTime->format('d/m/Y H:i').'. Poin pelanggaran (10 poin) otomatis dicatat ke rekam disiplin.'
            : 'Santri '.$leave->student->name.' resmi KEMBALI ke pondok tepat waktu pada '.$returnTime->format('d/m/Y H:i').'.';

        return [
            'success' => true,
            'is_late' => $isLate,
            'message' => $msg,
            'leave' => $leave,
        ];
    }

    protected function notifyGuardianOnApproval(StudentLeaveRequest $leave): void
    {
        $phone = $leave->student->guardian?->phone;
        if (empty($phone)) {
            return;
        }

        $appName = Branding::appName();
        $message = "Assalamu'alaikum Wr. Wb. Bpk/Ibu Wali Santri,\n\n".
            "Surat Izin Keluar/Pulang Santri *{$leave->student->name}* telah *DISETUJUI*.\n\n".
            "📋 *Nomor Izin:* {$leave->leave_number}\n".
            "📌 *Keperluan:* {$leave->reason}\n".
            "🗓 *Rencana Berangkat:* {$leave->departure_plan->format('d/m/Y H:i')}\n".
            "🗓 *Rencana Kembali:* {$leave->return_plan->format('d/m/Y H:i')}\n\n".
            "Santri wajib menunjukkan QR Gate Pass kepada petugas satpam di pos gerbang saat berangkat dan kembali.\n\n".
            "_{$appName}_";

        $this->whatsappService->send($phone, $message);
    }

    protected function notifyGuardianOnExit(StudentLeaveRequest $leave, Carbon $departureTime): void
    {
        $phone = $leave->student->guardian?->phone;
        if (empty($phone)) {
            return;
        }

        $appName = Branding::appName();
        $pickup = $leave->pickup_person ? " ({$leave->pickup_person})" : '';
        $message = "Assalamu'alaikum Wr. Wb. Bpk/Ibu Wali Santri,\n\n".
            "Pemberitahuan: Santri *{$leave->student->name}* telah *RESMI KELUAR* dari gerbang pesantren.\n\n".
            "⏰ *Waktu Keluar:* {$departureTime->format('d/m/Y H:i')} WIB\n".
            "📋 *Nomor Izin:* {$leave->leave_number}\n".
            "🚗 *Penjemput:* {$pickup}\n".
            "🗓 *Batas Waktu Kembali:* {$leave->return_plan->format('d/m/Y H:i')} WIB\n\n".
            "Mohon dampingi santri agar dapat kembali ke asrama tepat waktu sesuai jadwal perizinan.\n\n".
            "_{$appName}_";

        $this->whatsappService->send($phone, $message);
    }

    protected function notifyGuardianOnReturn(StudentLeaveRequest $leave, Carbon $returnTime, bool $isLate): void
    {
        $phone = $leave->student->guardian?->phone;
        if (empty($phone)) {
            return;
        }

        $appName = Branding::appName();
        if ($isLate) {
            $message = "Assalamu'alaikum Wr. Wb. Bpk/Ibu Wali Santri,\n\n".
                "Santri *{$leave->student->name}* telah *KEMBALI KE ASRAMA* pada {$returnTime->format('d/m/Y H:i')} WIB.\n\n".
                "⚠️ *Catatan Kedisiplinan:* Santri kembali melewati batas waktu izin ({$leave->return_plan->format('d/m/Y H:i')}). Catatan keterlambatan telah masuk ke sistem pembinaan asrama.\n\n".
                "_{$appName}_";
        } else {
            $message = "Assalamu'alaikum Wr. Wb. Bpk/Ibu Wali Santri,\n\n".
                "Alhamdulillah, santri *{$leave->student->name}* telah *KEMBALI KE ASRAMA* dengan selamat dan tepat waktu pada {$returnTime->format('d/m/Y H:i')} WIB.\n\n".
                "_{$appName}_";
        }

        $this->whatsappService->send($phone, $message);
    }
}
