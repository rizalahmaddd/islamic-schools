<?php

use App\Livewire\Reports\ActivityLogReport;
use App\Livewire\Reports\ReportsCenter;
use Illuminate\Support\Facades\Route;

// Menu "Laporan". Setiap komponen menggate aksesnya sendiri di mount().
Route::middleware(['auth', 'verified'])->prefix('laporan')->name('reports.')->group(function () {
    Route::get('/', ReportsCenter::class)->name('center');
    Route::get('aktivitas', ActivityLogReport::class)->name('activity-log');
});
