<?php

use App\Http\Controllers\PrintController;
use App\Livewire\Finance\CashierPos;
use App\Livewire\Finance\FeeCategories;
use App\Livewire\Finance\PaymentTransactions;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:view-finance'])->prefix('keuangan')->name('finance.')->group(function () {
    Route::get('kasir', CashierPos::class)->name('cashier');
    Route::get('transaksi', PaymentTransactions::class)->name('transactions');
    Route::get('tarif', FeeCategories::class)->name('fees');
    Route::get('kuitansi/{paymentTransaction}/cetak', [PrintController::class, 'receipt'])->name('receipts.print');
    Route::get('buku-kas/cetak', [PrintController::class, 'cashbook'])->name('cashbook.print');
});
