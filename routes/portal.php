<?php

use App\Http\Controllers\PrintController;
use App\Http\Controllers\PrintReportCardController;
use App\Livewire\Portal\GuardianDashboard;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:view-guardian-portal'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/', GuardianDashboard::class)->name('guardian');
    Route::get('rapor/{student}/cetak', [PrintReportCardController::class, 'print'])->name('report-cards.print');
    Route::get('kuitansi/{paymentTransaction}/cetak', [PrintController::class, 'receipt'])->name('receipts.print');
});
