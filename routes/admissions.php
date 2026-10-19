<?php

use App\Http\Controllers\PrintController;
use App\Livewire\Admissions\AdmissionBatches;
use App\Livewire\Admissions\CandidateIndex;
use App\Livewire\Admissions\PublicRegistration;
use Illuminate\Support\Facades\Route;

// 1. Rute Publik PPDB (tanpa login). Cetak bukti wajib URL bertanda tangan karena nomor registrasi berurutan.
Route::get('/ppdb', PublicRegistration::class)->name('ppdb.register');
Route::get('/ppdb/cetak/{registration_number}', [PrintController::class, 'admissionRegistrationProof'])->middleware('signed')->name('ppdb.print');

// 2. Rute Internal Manajemen PPDB (Memerlukan autentikasi)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admissions/candidates', CandidateIndex::class)
        ->name('admissions.candidates')
        ->middleware('can:view-admissions');

    Route::get('/admissions/batches', AdmissionBatches::class)
        ->name('admissions.batches')
        ->middleware('can:manage-admissions');
});
