<?php

use App\Http\Controllers\PrintController;
use App\Livewire\Inventory\InventoryIndex;
use App\Livewire\Inventory\RoomIndex;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/inventory/items', InventoryIndex::class)
        ->name('inventory.items')
        ->middleware('can:view-inventory');

    Route::get('/inventory/rooms', RoomIndex::class)
        ->name('inventory.rooms')
        ->middleware('can:view-inventory');

    Route::get('/inventory/print-item-label/{item}', [PrintController::class, 'inventoryItemQrLabel'])
        ->name('inventory.print-item-label')
        ->middleware('can:view-inventory');

    Route::get('/inventory/print-room-labels/{room}', [PrintController::class, 'inventoryRoomQrLabels'])
        ->name('inventory.print-room-labels')
        ->middleware('can:view-inventory');
});
