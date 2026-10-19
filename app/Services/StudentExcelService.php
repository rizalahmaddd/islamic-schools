<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Support\Phone;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentExcelService
{
    /**
     * Download file template Excel resmi untuk impor siswa.
     */
    public function downloadTemplate(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Siswa');

        // Header kolom
        $headers = [
            'A1' => 'NIS *',
            'B1' => 'NISN',
            'C1' => 'NIK',
            'D1' => 'Nama Lengkap *',
            'E1' => 'Nama Panggilan',
            'F1' => 'Jenis Kelamin (L/P) *',
            'G1' => 'Tempat Lahir',
            'H1' => 'Tanggal Lahir (YYYY-MM-DD)',
            'I1' => 'No Telepon Siswa',
            'J1' => 'Nama Wali *',
            'K1' => 'No WhatsApp Wali *',
            'L1' => 'Hubungan Wali (Ayah/Ibu/Wali)',
            'M1' => 'Pekerjaan Wali',
            'N1' => 'Alamat',
            'O1' => 'Kelas Target *',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Contoh baris 1
        $sampleRow = [
            'A2' => '2026001',
            'B2' => '0081234567',
            'C2' => '3201010101080001',
            'D2' => 'Ahmad Fauzi Rahman',
            'E2' => 'Fauzi',
            'F2' => 'L',
            'G2' => 'Jakarta',
            'H2' => '2012-05-14',
            'I2' => '081234567890',
            'J2' => 'H. Rahman Hidayat',
            'K2' => '081298765432',
            'L2' => 'Ayah',
            'M2' => 'Wiraswasta',
            'N2' => 'Jl. Kebon Jeruk No. 12, Jakarta Barat',
            'O2' => '7A',
        ];

        foreach ($sampleRow as $cell => $val) {
            $sheet->setCellValueExplicit($cell, $val, DataType::TYPE_STRING);
        }

        // Styling Header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']], // emerald-600
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '047857']]],
        ];
        $sheet->getStyle('A1:O1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Auto width
        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 'template-impor-siswa.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Parsing dan impor data siswa dari file Excel dengan validasi per baris.
     *
     * @return array{
     *     total: int,
     *     imported: int,
     *     updated: int,
     *     skipped: int,
     *     errors: list<array{row: int, nis: ?string, name: ?string, error: string}>
     * }
     */
    public function import(UploadedFile $file, ?int $academicYearId = null): array
    {
        $academicYear = $academicYearId
            ? AcademicYear::find($academicYearId)
            : AcademicYear::where('is_active', true)->first();

        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        if (! str_starts_with(strtoupper(trim((string) $sheet->getCell('A1')->getValue())), 'NIS') || ! str_contains(strtoupper((string) $sheet->getCell('D1')->getValue()), 'NAMA')) {
            throw ValidationException::withMessages([
                'importFile' => 'File ini bukan template impor siswa. Unduh template resmi lalu isi mulai baris ke-2.',
            ]);
        }

        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        // Loop mulai dari baris 2 (baris 1 adalah header)
        for ($row = 2; $row <= $highestRow; $row++) {
            $nis = trim((string) $sheet->getCell("A{$row}")->getValue());
            $nisn = trim((string) $sheet->getCell("B{$row}")->getValue()) ?: null;
            $nik = trim((string) $sheet->getCell("C{$row}")->getValue()) ?: null;
            $fullName = trim((string) $sheet->getCell("D{$row}")->getValue());
            $nickname = trim((string) $sheet->getCell("E{$row}")->getValue()) ?: null;
            $gender = strtoupper(trim((string) $sheet->getCell("F{$row}")->getValue()));
            $pob = trim((string) $sheet->getCell("G{$row}")->getValue()) ?: null;
            $rawDob = trim((string) $sheet->getCell("H{$row}")->getValue());
            $phone = trim((string) $sheet->getCell("I{$row}")->getValue()) ?: null;
            $guardianName = trim((string) $sheet->getCell("J{$row}")->getValue());
            $guardianPhone = trim((string) $sheet->getCell("K{$row}")->getValue());
            $relationship = trim((string) $sheet->getCell("L{$row}")->getValue()) ?: 'Orang Tua';
            $occupation = trim((string) $sheet->getCell("M{$row}")->getValue()) ?: null;
            $address = trim((string) $sheet->getCell("N{$row}")->getValue()) ?: null;
            $className = trim((string) $sheet->getCell("O{$row}")->getValue());

            // Lewati baris kosong
            if (empty($nis) && empty($fullName)) {
                continue;
            }

            // Validasi Field Wajib
            if (empty($nis)) {
                $errors[] = ['row' => $row, 'nis' => null, 'name' => $fullName, 'error' => 'NIS wajib diisi.'];
                $skipped++;

                continue;
            }

            if (empty($fullName)) {
                $errors[] = ['row' => $row, 'nis' => $nis, 'name' => null, 'error' => 'Nama lengkap wajib diisi.'];
                $skipped++;

                continue;
            }

            if (! in_array($gender, ['L', 'P'], true)) {
                $errors[] = ['row' => $row, 'nis' => $nis, 'name' => $fullName, 'error' => "Jenis kelamin harus 'L' atau 'P' (ditemukan: '{$gender}')."];
                $skipped++;

                continue;
            }

            if (empty($guardianName)) {
                $errors[] = ['row' => $row, 'nis' => $nis, 'name' => $fullName, 'error' => 'Nama wali murid wajib diisi.'];
                $skipped++;

                continue;
            }

            if (empty($guardianPhone)) {
                $errors[] = ['row' => $row, 'nis' => $nis, 'name' => $fullName, 'error' => 'Nomor WhatsApp wali wajib diisi.'];
                $skipped++;

                continue;
            }

            if (! Phone::isValidMobile($guardianPhone)) {
                $errors[] = ['row' => $row, 'nis' => $nis, 'name' => $fullName, 'error' => "Nomor WhatsApp wali '{$guardianPhone}' tidak valid (contoh: 081234567890)."];
                $skipped++;

                continue;
            }

            $guardianPhoneClean = Phone::international($guardianPhone);
            $phone = $phone ? Phone::international($phone) : null;

            // Parse tanggal lahir
            $dob = null;
            if (! empty($rawDob)) {
                try {
                    $dob = Carbon::parse($rawDob)->format('Y-m-d');
                } catch (\Throwable) {
                    $errors[] = ['row' => $row, 'nis' => $nis, 'name' => $fullName, 'error' => "Format tanggal lahir '{$rawDob}' tidak valid (gunakan YYYY-MM-DD)."];
                    $skipped++;

                    continue;
                }
            }

            // Cari atau cocokkan kelas
            $class = null;
            if (! empty($className) && $academicYear) {
                $classesInYear = SchoolClass::where('academic_year_id', $academicYear->id);
                $class = (clone $classesInYear)->where('name', $className)->first()
                    ?? (clone $classesInYear)->where('name', 'like', "%{$className}%")->orderBy('name')->first();

                if (! $class) {
                    // Buat kelas jika belum ada
                    preg_match('/\d+/', $className, $matches);
                    $grade = ! empty($matches) ? (int) $matches[0] : 7;
                    $class = SchoolClass::create([
                        'academic_year_id' => $academicYear->id,
                        'name' => $className,
                        'grade_level' => $grade,
                        'capacity' => 36,
                    ]);
                }
            }

            $alreadyExists = Student::where('nis', $nis)->exists();

            // Eksekusi Simpan Data dalam Transaksi Baris
            try {
                DB::transaction(function () use (
                    $nis, $nisn, $nik, $fullName, $nickname, $gender, $pob, $dob, $phone,
                    $guardianName, $guardianPhoneClean, $relationship, $occupation, $address,
                    $class, $academicYear
                ) {
                    // 1. Simpan atau cari Wali
                    $guardian = Guardian::firstOrCreate(
                        ['phone' => $guardianPhoneClean],
                        [
                            'name' => $guardianName,
                            'relationship' => $relationship,
                            'occupation' => $occupation,
                            'address' => $address,
                        ]
                    );

                    // 2. Simpan atau Update Siswa
                    $student = Student::updateOrCreate(
                        ['nis' => $nis],
                        [
                            'guardian_id' => $guardian->id,
                            'nisn' => $nisn,
                            'nik' => $nik,
                            'name' => $fullName,
                            'nickname' => $nickname,
                            'gender' => $gender,
                            'pob' => $pob,
                            'dob' => $dob,
                            'phone' => $phone,
                            'address' => $address,
                            'status' => 'active',
                        ]
                    );

                    // 3. Masukkan ke Rombel Kelas jika ada kelas & tahun ajaran aktif
                    if ($class && $academicYear) {
                        StudentEnrollment::updateOrCreate(
                            [
                                'student_id' => $student->id,
                                'academic_year_id' => $academicYear->id,
                            ],
                            [
                                'class_id' => $class->id,
                                'enrolled_at' => now(),
                                'status' => 'active',
                            ]
                        );
                    }
                });

                $imported++;
                $alreadyExists && $updated++;
            } catch (\Throwable $e) {
                report($e);
                $errors[] = [
                    'row' => $row,
                    'nis' => $nis,
                    'name' => $fullName,
                    'error' => 'Data baris ini gagal disimpan. Periksa isian lalu impor ulang baris ini.',
                ];
                $skipped++;
            }
        }

        return [
            'total' => $imported + $skipped,
            'imported' => $imported,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }
}
