<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\StudentBill;
use App\Models\StudentEnrollment;
use App\Models\StudentFeeWaiver;
use Illuminate\Support\Facades\DB;

class BillingService
{
    /**
     * Generate tagihan bulanan untuk seluruh siswa aktif pada tahun ajaran, bulan, dan tahun tertentu.
     *
     * @return array{created: int, skipped: int}
     */
    public function generateMonthlyBills(int $academicYearId, int $feeCategoryId, int $month, int $year): array
    {
        $category = FeeCategory::query()->findOrFail($feeCategoryId);
        $academicYear = AcademicYear::query()->findOrFail($academicYearId);

        // Ambil semua struktur tarif untuk kategori & tahun ajaran ini
        $structures = FeeStructure::query()
            ->where('academic_year_id', $academicYearId)
            ->where('fee_category_id', $feeCategoryId)
            ->get();

        if ($structures->isEmpty()) {
            return ['created' => 0, 'skipped' => 0];
        }

        // Ambil semua siswa yang terdaftar aktif di tahun ajaran ini beserta kelasnya
        $enrollments = StudentEnrollment::query()
            ->with(['student', 'schoolClass'])
            ->where('academic_year_id', $academicYearId)
            ->where('status', 'active')
            ->whereHas('student', fn ($q) => $q->where('status', 'active'))
            ->get();

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($enrollments, $structures, $academicYearId, $feeCategoryId, $category, $month, $year, &$created, &$skipped) {
            foreach ($enrollments as $enrollment) {
                $student = $enrollment->student;
                $gradeLevel = $enrollment->schoolClass?->grade_level;

                // Cari struktur biaya yang cocok: spesifik grade_level terlebih dahulu, atau null (berlaku semua)
                $structure = $structures->firstWhere('grade_level', $gradeLevel)
                    ?? $structures->firstWhere('grade_level', null);

                if (! $structure) {
                    $skipped++;

                    continue;
                }

                $baseAmount = (float) $structure->default_amount;

                // Cek apakah ada beasiswa / keringanan khusus untuk siswa ini
                $waiver = StudentFeeWaiver::query()
                    ->where('student_id', $student->id)
                    ->where('fee_category_id', $feeCategoryId)
                    ->where('academic_year_id', $academicYearId)
                    ->first();

                $discount = 0.0;
                if ($waiver) {
                    if ($waiver->discount_type === 'percentage') {
                        $discount = ($baseAmount * (float) $waiver->discount_value) / 100;
                    } else {
                        $discount = min($baseAmount, (float) $waiver->discount_value);
                    }
                }

                $netAmountDue = max(0, $baseAmount - $discount);
                $monthName = StudentBill::MONTH_NAMES[$month] ?? (string) $month;

                $bill = StudentBill::query()->firstOrCreate(
                    [
                        'student_id' => $student->id,
                        'fee_category_id' => $feeCategoryId,
                        'academic_year_id' => $academicYearId,
                        'month' => $month,
                        'year' => $year,
                    ],
                    [
                        'amount_due' => $netAmountDue,
                        'amount_paid' => $netAmountDue == 0 ? 0 : 0,
                        'status' => $netAmountDue == 0 ? 'paid' : 'unpaid',
                        'due_date' => sprintf('%04d-%02d-10', $year, $month),
                        'notes' => "Tagihan {$category->name} {$monthName} {$year}".($discount > 0 ? ' (Diskon Rp '.number_format($discount, 0, ',', '.').')' : ''),
                    ]
                );

                if ($bill->wasRecentlyCreated) {
                    $created++;
                } else {
                    $skipped++;
                }
            }
        });

        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * Konversi angka nominal rupiah ke kalimat Terbilang bahasa Indonesia.
     * Contoh: 250000 -> "Dua Ratus Lima Puluh Ribu Rupiah"
     */
    public static function terbilang(float|int $number): string
    {
        $number = abs($number);
        $words = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];

        if ($number < 12) {
            $result = $words[(int) $number];
        } elseif ($number < 20) {
            $result = self::terbilang($number - 10).' Belas';
        } elseif ($number < 100) {
            $result = self::terbilang((int) ($number / 10)).' Puluh '.self::terbilang($number % 10);
        } elseif ($number < 200) {
            $result = 'Seratus '.self::terbilang($number - 100);
        } elseif ($number < 1000) {
            $result = self::terbilang((int) ($number / 100)).' Ratus '.self::terbilang($number % 100);
        } elseif ($number < 2000) {
            $result = 'Seribu '.self::terbilang($number - 1000);
        } elseif ($number < 1000000) {
            $result = self::terbilang((int) ($number / 1000)).' Ribu '.self::terbilang($number % 1000);
        } elseif ($number < 1000000000) {
            $result = self::terbilang((int) ($number / 1000000)).' Juta '.self::terbilang($number % 1000000);
        } elseif ($number < 1000000000000) {
            $result = self::terbilang((int) ($number / 1000000000)).' Miliar '.self::terbilang(fmod($number, 1000000000));
        } else {
            $result = self::terbilang((int) ($number / 1000000000000)).' Triliun '.self::terbilang(fmod($number, 1000000000000));
        }

        return trim(preg_replace('/\s+/', ' ', $result));
    }
}
