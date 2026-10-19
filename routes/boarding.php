<?php

use App\Http\Controllers\PrintController;
use App\Livewire\Boarding\DormRooms;
use App\Livewire\Boarding\SecurityGateScanner;
use App\Livewire\Boarding\StudentLeaves;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    // 1. Kamar & Gedung Asrama
    Route::get('/boarding/rooms', DormRooms::class)
        ->name('boarding.rooms')
        ->middleware('can:view-boarding');

    // 2. Perizinan Keluar Santri
    Route::get('/boarding/leaves', StudentLeaves::class)
        ->name('boarding.leaves')
        ->middleware('can:view-boarding');

    // 3. Scanner Satpam Pos Gerbang
    Route::get('/boarding/scanner', SecurityGateScanner::class)
        ->name('boarding.scanner')
        ->middleware('can:scan-gate-pass');

    // 4. Cetak Surat Izin Keluar Santri (Gate Pass)
    Route::get('/boarding/leaves/{leave}/print', [PrintController::class, 'studentLeavePass'])
        ->name('boarding.leaves.print');
});
