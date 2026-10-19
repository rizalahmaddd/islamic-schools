<?php

use App\Livewire\Cashless\CanteenPos;
use App\Livewire\Cashless\CardManagement;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    // 1. Kasir Kantin POS (Smart Card RFID)
    Route::get('/cashless/pos', CanteenPos::class)
        ->name('cashless.pos')
        ->middleware('can:view-cashless');

    // 2. Manajemen Kartu RFID, Saldo, dan Batas Limit Harian
    Route::get('/cashless/cards', CardManagement::class)
        ->name('cashless.cards')
        ->middleware('can:manage-cashless');
});
