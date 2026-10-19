<?php

namespace App\Jobs;

use App\Models\SchoolClass;
use App\Models\Setting;
use App\Models\Student;
use App\Services\FonnteService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendAttendanceWhatsAppNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 5;

    public function __construct(
        public int $studentId,
        public int $classId,
        public string $date,
        public string $status,
        public ?string $notes = null,
    ) {}

    public function handle(FonnteService $fonnteService): void
    {
        $student = Student::with('guardian')->find($this->studentId);
        if (! $student || ! $student->guardian || blank($student->guardian->phone)) {
            Log::info("WhatsApp presensi dilewati: Santri #{$this->studentId} tidak memiliki nomor wali.");

            return;
        }

        $schoolClass = SchoolClass::find($this->classId);
        $className = $schoolClass?->name ?? 'Kelas';
        $schoolName = Setting::get('company_name', config('app.name', 'Sekolah Islam'));

        $phone = preg_replace('/[^0-9]/', '', (string) $student->guardian->phone);
        if (str_starts_with($phone, '08')) {
            $phone = '628'.substr($phone, 2);
        }

        if (blank($phone)) {
            return;
        }

        $statusText = match ($this->status) {
            'sick' => 'Sakit',
            'permit' => 'Izin',
            'absent' => 'Alpa (Tanpa Keterangan)',
            default => ucfirst($this->status),
        };

        $carbonDate = Carbon::parse($this->date)->locale('id');
        $formattedDate = $carbonDate->translatedFormat('l, d F Y');

        $guardianName = $student->guardian->name;
        $studentName = $student->name;
        $noteLine = filled($this->notes) ? "\nKeterangan: *{$this->notes}*" : '';

        $message = "Assalamu'alaikum Warahmatullahi Wabarakatuh.\n\n".
            "Yth. Bpk/Ibu *{$guardianName}* (Wali dari ananda *{$studentName}*),\n\n".
            "Menginformasikan bahwa ananda tercatat *{$statusText}* pada presensi kelas *{$className}* hari {$formattedDate}.{$noteLine}\n\n".
            "Semoga ananda senantiasa berada dalam lindungan dan rahmat Allah SWT. Terima kasih atas perhatian Bpk/Ibu.\n\n".
            "Wassalamu'alaikum Warahmatullahi Wabarakatuh.\n".
            "*{$schoolName}*";

        $result = $fonnteService->send($phone, $message);

        if ($result['status'] ?? false) {
            Log::info("WhatsApp presensi terkirim ke {$student->guardian->phone} untuk santri {$studentName} ({$statusText})");
        } else {
            Log::warning("Gagal mengirim WhatsApp presensi ke {$phone}: ".($result['message'] ?? 'Error tidak diketahui'));
        }
    }
}
