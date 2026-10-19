<?php

use App\Http\Controllers\PrintController;
use App\Livewire\Attendance\StudentAttendanceReport;
use App\Livewire\Attendance\StudentAttendanceSheet;
use App\Livewire\Attendance\TeacherAttendanceList;
use App\Livewire\Attendance\TeacherCheckIn;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:view-attendance'])->prefix('presensi')->name('attendance.')->group(function () {
    Route::get('siswa', StudentAttendanceSheet::class)->name('students');
    Route::get('rekapitulasi', StudentAttendanceReport::class)->name('reports');
    Route::get('rekapitulasi/{schoolClass}/cetak', [PrintController::class, 'attendanceReport'])->name('reports.print');
    Route::get('guru/check-in', TeacherCheckIn::class)->name('teachers.check-in');
    Route::get('guru', TeacherAttendanceList::class)->name('teachers');
});
