<?php

use App\Http\Controllers\Api\V1\MasterData\StudentController;
use Illuminate\Support\Facades\Route;

Route::prefix('master-data')->name('master-data.')->group(function () {
    Route::apiResource('students', StudentController::class)->middleware('feature:master-data.students');
});
