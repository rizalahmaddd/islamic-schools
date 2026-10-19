<?php

use App\Livewire\Library\BookIndex;
use App\Livewire\Library\CirculationIndex;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/library/books', BookIndex::class)
        ->name('library.books')
        ->middleware('can:view-library');

    Route::get('/library/circulation', CirculationIndex::class)
        ->name('library.circulation')
        ->middleware('can:view-library');
});
