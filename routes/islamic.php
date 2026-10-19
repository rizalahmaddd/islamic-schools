<?php

use App\Livewire\Islamic\ConductRecords;
use App\Livewire\Islamic\MutabaahWorship;
use App\Livewire\Islamic\TahfidzSheet;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('kepesantrenan')->name('islamic.')->group(function () {
    Route::get('tahfidz', TahfidzSheet::class)->middleware('can:view-tahfidz')->name('tahfidz');
    Route::get('mutabaah', MutabaahWorship::class)->middleware('can:view-tahfidz')->name('mutabaah');
    Route::get('adab-disiplin', ConductRecords::class)->middleware('can:view-conduct')->name('conduct');
});
