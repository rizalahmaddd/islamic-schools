<?php

use App\Livewire\MasterData\Students;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentBill;
use App\Services\StudentExcelService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(PermissionSeeder::class);
});

test('unauthorized users cannot view students list', function () {
    actingAsRole('santri');

    $this->get(route('master-data.students'))->assertForbidden();
});

test('authorized users can view students list', function () {
    actingAsAdmin();

    $this->get(route('master-data.students'))->assertOk();
});

test('admin can create student with guardian and class enrollment', function () {
    actingAsAdmin();
    $year = AcademicYear::factory()->active()->create();
    $class = SchoolClass::factory()->create(['academic_year_id' => $year->id, 'name' => '7A']);

    Livewire::test(Students::class)
        ->set('nis', '20261001')
        ->set('nisn', '0098765432')
        ->set('name', 'Muhammad Faiz Rahman')
        ->set('gender', 'L')
        ->set('guardian_name', 'H. Agus Salim')
        ->set('guardian_phone', '081234567800')
        ->set('guardian_relationship', 'Ayah')
        ->set('target_class_id', $class->id)
        ->call('save')
        ->assertHasNoErrors();

    $student = Student::where('nis', '20261001')->first();
    expect($student)->not->toBeNull()
        ->and($student->name)->toBe('Muhammad Faiz Rahman')
        ->and($student->guardian)->not->toBeNull()
        ->and($student->guardian->name)->toBe('H. Agus Salim')
        ->and($student->guardian->phone)->toBe('6281234567800')
        ->and($student->currentClass()?->id)->toBe($class->id);
});

test('student requires valid NIS and guardian data', function () {
    actingAsAdmin();

    Livewire::test(Students::class)
        ->set('nis', '')
        ->set('name', '')
        ->set('guardian_name', '')
        ->set('guardian_phone', '')
        ->call('save')
        ->assertHasErrors(['nis', 'name', 'guardian_name', 'guardian_phone']);
});

test('user can view student detail show page', function () {
    actingAsAdmin();
    $student = Student::factory()->create(['name' => 'Santri Khusus']);

    $this->get(route('master-data.students.show', $student))
        ->assertOk()
        ->assertSee('Santri Khusus');
});

test('admin can download excel template', function () {
    actingAsAdmin();

    $response = Livewire::test(Students::class)
        ->call('downloadTemplate');

    expect($response)->not->toBeNull();
});

test('student excel import service validates rows and imports valid data', function () {
    $year = AcademicYear::factory()->active()->create();
    $class = SchoolClass::factory()->create(['academic_year_id' => $year->id, 'name' => '7A']);

    // Buat spreadsheet uji coba di memory
    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'NIS *');
    $sheet->setCellValue('B1', 'NISN');
    $sheet->setCellValue('C1', 'NIK');
    $sheet->setCellValue('D1', 'Nama Lengkap *');
    $sheet->setCellValue('E1', 'Nama Panggilan');
    $sheet->setCellValue('F1', 'Jenis Kelamin (L/P) *');
    $sheet->setCellValue('G1', 'Tempat Lahir');
    $sheet->setCellValue('H1', 'Tanggal Lahir (YYYY-MM-DD)');
    $sheet->setCellValue('I1', 'No Telepon Siswa');
    $sheet->setCellValue('J1', 'Nama Wali *');
    $sheet->setCellValue('K1', 'No WhatsApp Wali *');
    $sheet->setCellValue('L1', 'Hubungan Wali');
    $sheet->setCellValue('M1', 'Pekerjaan Wali');
    $sheet->setCellValue('N1', 'Alamat');
    $sheet->setCellValue('O1', 'Kelas Target *');

    // Baris 2: Data Benar
    $sheet->setCellValue('A2', '999001');
    $sheet->setCellValue('D2', 'Santri Uji Coba 1');
    $sheet->setCellValue('F2', 'L');
    $sheet->setCellValue('H2', '2012-04-10');
    $sheet->setCellValue('J2', 'Wali Santri 1');
    $sheet->setCellValue('K2', '081234567888');
    $sheet->setCellValue('O2', '7A');

    // Baris 3: Data Salah (Tidak ada Nama Wali)
    $sheet->setCellValue('A3', '999002');
    $sheet->setCellValue('D3', 'Santri Tanpa Wali');
    $sheet->setCellValue('F3', 'P');
    $sheet->setCellValue('J3', ''); // Empty
    $sheet->setCellValue('K3', '');

    $tempPath = tempnam(sys_get_temp_dir(), 'test_import').'.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($tempPath);

    $uploadedFile = new UploadedFile($tempPath, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    $service = new StudentExcelService;
    $result = $service->import($uploadedFile, $year->id);

    expect($result['imported'])->toBe(1)
        ->and($result['skipped'])->toBe(1)
        ->and(count($result['errors']))->toBe(1)
        ->and($result['errors'][0]['row'])->toBe(3);

    expect(Student::where('nis', '999001')->exists())->toBeTrue()
        ->and(Student::where('nis', '999002')->exists())->toBeFalse();

    @unlink($tempPath);
});

test('student search keeps the status filter instead of leaking other rows', function () {
    actingAsAdmin();
    Student::factory()->create(['name' => 'Aisyah Aktif', 'status' => 'active']);
    Student::factory()->create(['name' => 'Aisyah Lulus', 'status' => 'graduated']);

    Livewire::test(Students::class)
        ->set('statusFilter', 'active')
        ->set('search', 'Aisyah')
        ->assertSee('Aisyah Aktif')
        ->assertDontSee('Aisyah Lulus');
});

test('guardian phone must be an indonesian mobile number', function (string $phone, bool $valid) {
    actingAsAdmin();

    $component = Livewire::test(Students::class)
        ->set('nis', '20269001')
        ->set('name', 'Uji Nomor')
        ->set('gender', 'L')
        ->set('guardian_name', 'Wali Uji')
        ->set('guardian_relationship', 'Ayah')
        ->set('guardian_phone', $phone)
        ->call('save');

    $valid ? $component->assertHasNoErrors() : $component->assertHasErrors(['guardian_phone']);
})->with([
    'plain letters' => ['abc123', false],
    'too short' => ['0812', false],
    'local format' => ['0812-3456-7890', true],
    'international format' => ['+62 812 3456 7890', true],
    'excel dropped the leading zero' => ['81234567890', true],
]);

test('student excel import rejects a file that is not the official template', function () {
    $spreadsheet = new Spreadsheet;
    $spreadsheet->getActiveSheet()->setCellValue('A1', 'Produk')->setCellValue('B1', 'Harga');
    $path = tempnam(sys_get_temp_dir(), 'bad_import').'.xlsx';
    (new Xlsx($spreadsheet))->save($path);

    expect(fn () => app(StudentExcelService::class)->import(new UploadedFile($path, 'bukan-template.xlsx', null, null, true)))
        ->toThrow(ValidationException::class, 'bukan template');
});

test('a student with unpaid bills cannot be deleted', function () {
    actingAsAdmin();
    $student = Student::factory()->create();
    StudentBill::factory()->create(['student_id' => $student->id, 'status' => 'unpaid']);

    Livewire::test(Students::class)
        ->call('confirmDelete', $student->id)
        ->call('delete');

    expect(Student::find($student->id))->not->toBeNull();
});
