<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Student;
use App\Services\AcademicGradingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Controller cetak dokumen resmi E-Rapor Kurikulum Merdeka / Pesantren.
 * Dibuka di tab baru dan dicetak / disimpan PDF via window.print() bawaan browser.
 */
class PrintReportCardController extends Controller
{
    public function print(Student $student, Request $request, AcademicGradingService $gradingService): View
    {
        Gate::authorize('print-report-card', $student);

        $academicYearId = $request->query('academic_year_id');
        $academicYear = $academicYearId ? AcademicYear::find($academicYearId) : null;

        $reportData = $gradingService->getStudentReportCardData($student, $academicYear?->id);

        $schoolClass = $reportData['class'];
        $academicYear = $reportData['academicYear'];

        return view('print.report-card', [
            'student' => $student,
            'schoolClass' => $schoolClass,
            'academicYear' => $academicYear,
            'grades' => $reportData['grades'],
            'averageScore' => $reportData['averageScore'],
            'summary' => $reportData['summary'],
            'tahfidzProgress' => $reportData['tahfidzProgress'],
            'violationPoints' => $reportData['violationPoints'],
            'meritPoints' => $reportData['meritPoints'],
            'generatedAt' => now()->translatedFormat('d F Y'),
        ]);
    }
}
