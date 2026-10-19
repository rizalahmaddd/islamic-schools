<?php

use App\Http\Controllers\PrintReportCardController;
use App\Livewire\Academic\GradeSheet;
use App\Livewire\Academic\ReportCardIndex;
use App\Livewire\Academic\SubjectAssignments;
use App\Livewire\Academic\TeachingJournals;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('akademik')->name('academic.')->group(function () {
    Route::get('jurnal-mengajar', TeachingJournals::class)->middleware('can:view-teaching-journal')->name('teaching-journals');
    Route::get('pembagian-tugas', SubjectAssignments::class)->middleware('can:view-grading')->name('subject-assignments');
    Route::get('buku-nilai', GradeSheet::class)->middleware('can:view-grading')->name('grade-sheet');
    Route::get('rapor', ReportCardIndex::class)->middleware('can:view-grading')->name('report-cards');
    Route::get('rapor/{student}/cetak', [PrintReportCardController::class, 'print'])->middleware('can:print-report-card,student')->name('report-cards.print');
});
