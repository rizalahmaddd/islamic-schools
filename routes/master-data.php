<?php

use App\Livewire\MasterData\AcademicYears;
use App\Livewire\MasterData\SchoolClasses;
use App\Livewire\MasterData\Students;
use App\Livewire\MasterData\StudentShow;
use App\Livewire\MasterData\Teachers;
use Illuminate\Support\Facades\Route;

// Daftar & detail butuh izin lihat (view-master-data); tambah/ubah/hapus dibatasi di dalam
// komponen lewat WithCrudActions::canManage().
Route::middleware(['auth', 'verified', 'can:view-master-data'])->prefix('master-data')->name('master-data.')->group(function () {
    Route::get('tahun-ajaran', AcademicYears::class)->name('academic-years');
    Route::get('guru', Teachers::class)->name('teachers');
    Route::get('kelas', SchoolClasses::class)->name('classes');
    Route::get('siswa', Students::class)->name('students');
    Route::get('siswa/{student}', StudentShow::class)->name('students.show');
});
