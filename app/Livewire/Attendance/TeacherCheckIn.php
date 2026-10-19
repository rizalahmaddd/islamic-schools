<?php

namespace App\Livewire\Attendance;

use App\Models\Setting;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app', ['heading' => 'Presensi Mandiri Guru'])]
#[Title('Presensi Mandiri Guru')]
class TeacherCheckIn extends Component
{
    public ?Teacher $teacher = null;

    public ?TeacherAttendance $todayAttendance = null;

    public float $schoolLat = -6.200000;

    public float $schoolLng = 106.816666;

    public int $radiusMeters = 100;

    public string $checkInLateTime = '07:30';

    public ?float $currentLat = null;

    public ?float $currentLng = null;

    public ?float $calculatedDistance = null;

    public ?string $errorMessage = null;

    public function mount(): void
    {
        $this->schoolLat = (float) Setting::get('school_lat', -6.200000);
        $this->schoolLng = (float) Setting::get('school_lng', 106.816666);
        $this->radiusMeters = (int) Setting::get('school_radius_meters', 100);
        $this->checkInLateTime = (string) Setting::get('teacher_check_in_late', '07:30');

        $user = Auth::user();
        $this->teacher = Teacher::where('user_id', $user?->id)->first()
            ?? Teacher::where('email', $user?->email)->first()
            ?? Teacher::first();

        $this->refreshTodayAttendance();
    }

    public function refreshTodayAttendance(): void
    {
        if ($this->teacher) {
            $this->todayAttendance = TeacherAttendance::where('teacher_id', $this->teacher->id)
                ->where('date', now()->toDateString())
                ->first();
        }
    }

    public function processCheckIn(float $latitude, float $longitude): void
    {
        $this->errorMessage = null;
        $this->currentLat = $latitude;
        $this->currentLng = $longitude;

        if (! $this->teacher) {
            $this->errorMessage = 'Data profil pendidik Anda belum ditautkan ke akun ini.';

            return;
        }

        $distance = TeacherAttendance::calculateDistance(
            $latitude,
            $longitude,
            $this->schoolLat,
            $this->schoolLng
        );

        $this->calculatedDistance = $distance;

        // Validasi geofence (kecuali jika bypass diaktifkan di setting atau testing)
        $bypassGeofence = (bool) Setting::get('bypass_geofence', app()->environment('testing'));
        if (! $bypassGeofence && $distance > $this->radiusMeters) {
            $this->errorMessage = "Posisi Anda berada di luar area sekolah ({$distance} meter dari sekolah). Maksimal radius yang diizinkan adalah {$this->radiusMeters} meter.";

            return;
        }

        $currentTime = now()->format('H:i:s');
        $isLate = now()->format('H:i') > $this->checkInLateTime;
        $status = $isLate ? TeacherAttendance::STATUS_LATE : TeacherAttendance::STATUS_PRESENT;

        TeacherAttendance::updateOrCreate(
            [
                'teacher_id' => $this->teacher->id,
                'date' => now()->toDateString(),
            ],
            [
                'check_in_time' => $currentTime,
                'method' => 'gps',
                'latitude' => $latitude,
                'longitude' => $longitude,
                'distance_meters' => (int) $distance,
                'status' => $status,
                'recorded_by' => Auth::id(),
            ]
        );

        $this->refreshTodayAttendance();
        $statusLabel = $isLate ? 'Terlambat' : 'Tepat Waktu';
        $this->dispatch('notify', "Check-in berhasil ({$statusLabel}) pada {$currentTime}!");
    }

    public function processCheckOut(float $latitude, float $longitude): void
    {
        $this->errorMessage = null;

        if (! $this->teacher || ! $this->todayAttendance) {
            $this->errorMessage = 'Anda belum melakukan check-in hari ini.';

            return;
        }

        $currentTime = now()->format('H:i:s');

        $this->todayAttendance->update([
            'check_out_time' => $currentTime,
        ]);

        $this->refreshTodayAttendance();
        $this->dispatch('notify', "Check-out kepulangan berhasil dicatat pada {$currentTime}!");
    }

    public function render(): View
    {
        return view('livewire.attendance.teacher-check-in');
    }
}
