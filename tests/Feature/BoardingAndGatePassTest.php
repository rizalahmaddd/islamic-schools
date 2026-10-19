<?php

use App\Livewire\Boarding\DormRooms;
use App\Livewire\Boarding\SecurityGateScanner;
use App\Livewire\Boarding\StudentLeaves;
use App\Models\AcademicYear;
use App\Models\DormBuilding;
use App\Models\DormPlacement;
use App\Models\DormRoom;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\StudentConductRecord;
use App\Models\StudentLeaveRequest;
use App\Models\User;
use App\Services\BoardingLeaveService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(PermissionSeeder::class);

    Http::fake();
});

test('dorm building and room creation accurately calculates capacity and available beds', function () {
    $building = DormBuilding::factory()->create([
        'name' => 'Gedung Abu Bakar',
        'gender' => 'male',
    ]);

    $musyrif = User::factory()->create();
    $musyrif->assignRole('musyrif');

    $room = DormRoom::factory()->create([
        'dorm_building_id' => $building->id,
        'room_number' => '101',
        'capacity' => 4,
        'musyrif_id' => $musyrif->id,
    ]);

    $academicYear = AcademicYear::factory()->create(['is_active' => true]);

    expect($room->capacity)->toBe(4)
        ->and($room->occupiedCount($academicYear->id))->toBe(0)
        ->and($room->availableBeds($academicYear->id))->toBe(4);

    // Tempatkan 1 santri
    $student = Student::factory()->create(['gender' => 'male']);
    DormPlacement::factory()->create([
        'student_id' => $student->id,
        'dorm_room_id' => $room->id,
        'academic_year_id' => $academicYear->id,
        'bed_number' => 'Kasur A1',
    ]);

    expect($room->occupiedCount($academicYear->id))->toBe(1)
        ->and($room->availableBeds($academicYear->id))->toBe(3);
});

test('createLeaveRequest generates sequential IZN document number and secure qr token', function () {
    $academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $guardian = Guardian::factory()->create(['phone' => '08123456789']);
    $student = Student::factory()->create(['guardian_id' => $guardian->id]);

    /** @var BoardingLeaveService $service */
    $service = app(BoardingLeaveService::class);

    $leave = $service->createLeaveRequest($student, [
        'academic_year_id' => $academicYear->id,
        'leave_type' => 'weekend',
        'departure_plan' => now()->addDay()->setHour(8)->setMinute(0),
        'return_plan' => now()->addDays(2)->setHour(17)->setMinute(0),
        'reason' => 'Pulang akhir pekan ke rumah orang tua.',
        'pickup_person' => 'Bapak Fauzi (08123456789)',
    ]);

    expect($leave)->toBeInstanceOf(StudentLeaveRequest::class)
        ->and($leave->leave_number)->toStartWith('IZN-')
        ->and($leave->qr_pass_token)->not->toBeEmpty()
        ->and(strlen($leave->qr_pass_token))->toBe(32)
        ->and($leave->isPending())->toBeTrue();
});

test('musyrif can approve leave request and notify guardian via whatsapp', function () {
    $academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $guardian = Guardian::factory()->create(['phone' => '081299998888']);
    $student = Student::factory()->create(['guardian_id' => $guardian->id]);

    $musyrif = User::factory()->create();
    $musyrif->assignRole('musyrif');

    /** @var BoardingLeaveService $service */
    $service = app(BoardingLeaveService::class);

    $leave = $service->createLeaveRequest($student, [
        'academic_year_id' => $academicYear->id,
        'leave_type' => 'sick',
        'departure_plan' => now()->addHours(1),
        'return_plan' => now()->addDays(3),
        'reason' => 'Pemeriksaan ke dokter spesialis di RS.',
    ]);

    $service->approveLeave($leave, $musyrif);

    $leave->refresh();
    expect($leave->status)->toBe('approved_mentor')
        ->and($leave->approved_by)->toBe($musyrif->id)
        ->and($leave->approved_at)->not->toBeNull()
        ->and($leave->isApproved())->toBeTrue();

    // Verifikasi WhatsApp dipanggil
    Http::assertSent(fn ($req) => str_contains($req['target'], '081299998888') && str_contains($req['message'], 'DISETUJUI'));
});

test('security scan exit rejects unapproved leave and accepts approved leave with real-time departure time', function () {
    $academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $guardian = Guardian::factory()->create(['phone' => '081288887777']);
    $student = Student::factory()->create(['guardian_id' => $guardian->id]);

    $satpam = User::factory()->create();
    $satpam->assignRole('satpam');

    $musyrif = User::factory()->create();
    $musyrif->assignRole('musyrif');

    /** @var BoardingLeaveService $service */
    $service = app(BoardingLeaveService::class);

    $leave = $service->createLeaveRequest($student, [
        'academic_year_id' => $academicYear->id,
        'leave_type' => 'family',
        'departure_plan' => now()->addMinutes(30),
        'return_plan' => now()->addDays(2),
        'reason' => 'Menghadiri pernikahan kakak kandung.',
    ]);

    // 1. Scan saat status masih pending -> ditolak
    expect(fn () => $service->scanSecurityExit($leave->qr_pass_token, $satpam))
        ->toThrow(ValidationException::class);

    // 2. Disetujui oleh musyrif
    $service->approveLeave($leave, $musyrif);

    // 3. Scan keluar oleh satpam -> berhasil
    $result = $service->scanSecurityExit($leave->qr_pass_token, $satpam);

    expect($result['success'])->toBeTrue();

    $leave->refresh();
    expect($leave->status)->toBe('active')
        ->and($leave->actual_departure_at)->not->toBeNull()
        ->and($leave->security_departure_user_id)->toBe($satpam->id)
        ->and($leave->isActive())->toBeTrue();

    // Verifikasi WhatsApp santri telah resmi keluar gerbang
    Http::assertSent(fn ($req) => str_contains($req['target'], '081288887777') && str_contains($req['message'], 'RESMI KELUAR'));
});

test('security scan return on time marks status as returned', function () {
    $academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $guardian = Guardian::factory()->create(['phone' => '081277776666']);
    $student = Student::factory()->create(['guardian_id' => $guardian->id]);

    $satpam = User::factory()->create();
    $satpam->assignRole('satpam');

    $musyrif = User::factory()->create();
    $musyrif->assignRole('musyrif');

    /** @var BoardingLeaveService $service */
    $service = app(BoardingLeaveService::class);

    $leave = $service->createLeaveRequest($student, [
        'academic_year_id' => $academicYear->id,
        'leave_type' => 'weekend',
        'departure_plan' => now()->subDay(),
        'return_plan' => now()->addHours(4), // batas kembali masih 4 jam ke depan (tepat waktu)
        'reason' => 'Pulang akhir pekan.',
    ]);

    $service->approveLeave($leave, $musyrif);
    $service->scanSecurityExit($leave->qr_pass_token, $satpam);

    // Scan kembali
    $result = $service->scanSecurityReturn($leave->qr_pass_token, $satpam, 'Kembali dengan tertib');

    expect($result['success'])->toBeTrue()
        ->and($result['is_late'])->toBeFalse();

    $leave->refresh();
    expect($leave->status)->toBe('returned')
        ->and($leave->actual_returned_at)->not->toBeNull()
        ->and($leave->security_return_user_id)->toBe($satpam->id)
        ->and(StudentConductRecord::where('student_id', $student->id)->count())->toBe(0);
});

test('security scan return late marks status as late and automatically creates student conduct violation record', function () {
    $academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $guardian = Guardian::factory()->create(['phone' => '081255554444']);
    $student = Student::factory()->create(['guardian_id' => $guardian->id]);

    $satpam = User::factory()->create();
    $satpam->assignRole('satpam');

    $musyrif = User::factory()->create();
    $musyrif->assignRole('musyrif');

    /** @var BoardingLeaveService $service */
    $service = app(BoardingLeaveService::class);

    // Rencana kembali adalah 2 jam yang lalu (sudah terlambat)
    $leave = $service->createLeaveRequest($student, [
        'academic_year_id' => $academicYear->id,
        'leave_type' => 'weekend',
        'departure_plan' => now()->subDays(2),
        'return_plan' => now()->subHours(2),
        'reason' => 'Pulang akhir pekan.',
    ]);

    $service->approveLeave($leave, $musyrif);

    // Santri keluar saat izin masih berlaku, lalu baru kembali setelah batas waktunya.
    $this->travelTo(now()->subDays(1));
    $service->scanSecurityExit($leave->qr_pass_token, $satpam);
    $this->travelBack();

    // Scan kembali terlambat
    $result = $service->scanSecurityReturn($leave->qr_pass_token, $satpam, 'Santri diantar terlambat oleh keluarga');

    expect($result['success'])->toBeTrue()
        ->and($result['is_late'])->toBeTrue();

    $leave->refresh();
    expect($leave->status)->toBe('late')
        ->and($leave->actual_returned_at)->not->toBeNull();

    // Verifikasi otomatis tercatat sebagai pelanggaran disiplin (Fase 5)
    $violation = StudentConductRecord::where('student_id', $student->id)->first();
    expect($violation)->not->toBeNull()
        ->and($violation->type)->toBe('violation')
        ->and($violation->category)->toBe('Disiplin Asrama')
        ->and($violation->points)->toBe(10)
        ->and($violation->description)->toContain($leave->leave_number)
        ->and($violation->recorded_by)->toBe($satpam->id);

    // Verifikasi WhatsApp notifikasi keterlambatan terkirim
    Http::assertSent(fn ($req) => str_contains($req['target'], '081255554444') && str_contains($req['message'], 'Catatan Kedisiplinan'));
});

test('satpam can use SecurityGateScanner livewire component to check token, confirm exit, and confirm return', function () {
    $satpam = User::factory()->create();
    $satpam->assignRole('satpam');

    $academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $guardian = Guardian::factory()->create(['phone' => '081233332222']);
    $student = Student::factory()->create(['guardian_id' => $guardian->id, 'name' => 'Fulan bin Fulan']);

    $musyrif = User::factory()->create();
    $musyrif->assignRole('musyrif');

    /** @var BoardingLeaveService $service */
    $service = app(BoardingLeaveService::class);
    $leave = $service->createLeaveRequest($student, [
        'academic_year_id' => $academicYear->id,
        'leave_type' => 'weekend',
        'departure_plan' => now()->addHour(),
        'return_plan' => now()->addDays(2),
        'reason' => 'Pulang rutin santri.',
    ]);
    $service->approveLeave($leave, $musyrif);

    Livewire::actingAs($satpam)
        ->test(SecurityGateScanner::class)
        ->assertSee('Pos Keamanan Gerbang')
        // Scan / masukkan nomor izin
        ->call('checkToken', $leave->leave_number)
        ->assertSet('qr_token_input', $leave->qr_pass_token)
        ->assertSee('Fulan bin Fulan')
        ->assertSee('Konfirmasi Santri KELUAR Gerbang')
        // Eksekusi konfirmasi keluar
        ->call('confirmExit')
        ->assertSee('resmi KELUAR gerbang');

    $leave->refresh();
    expect($leave->status)->toBe('active');

    // Scan ulang untuk kembali
    Livewire::actingAs($satpam)
        ->test(SecurityGateScanner::class)
        ->call('checkToken', $leave->qr_pass_token)
        ->assertSee('Konfirmasi Santri KEMBALI ke Pondok')
        ->set('notes', 'Santri tiba dengan sehat')
        ->call('confirmReturn')
        ->assertSee('resmi KEMBALI ke pondok tepat waktu');

    $leave->refresh();
    expect($leave->status)->toBe('returned');
});

test('print route renders printable leave pass document with letterhead and qr pass', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $student = Student::factory()->create(['name' => 'Ahmad Sholihin']);

    $leave = StudentLeaveRequest::factory()->create([
        'student_id' => $student->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'approved_mentor',
    ]);

    $this->actingAs($admin)
        ->get(route('boarding.leaves.print', $leave->id))
        ->assertOk()
        ->assertSee('SURAT IZIN KELUAR / PULANG SANTRI')
        ->assertSee($leave->leave_number)
        ->assertSee('Ahmad Sholihin');
});

test('musyrif can manage dorm rooms and place students via DormRooms livewire component', function () {
    $musyrif = User::factory()->create();
    $musyrif->assignRole('musyrif');

    $building = DormBuilding::factory()->create(['name' => 'Gedung Ali bin Abi Thalib', 'gender' => 'male']);
    $academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $student = Student::factory()->create(['name' => 'Santri Budi', 'gender' => 'male']);

    Livewire::actingAs($musyrif)
        ->test(DormRooms::class)
        ->assertSee('Asrama Santri')
        // Tambah Kamar
        ->set('dorm_building_id', $building->id)
        ->set('room_number', 'Kamar 102')
        ->set('capacity', 6)
        ->set('musyrif_id', $musyrif->id)
        ->call('save')
        ->assertSee('Kamar 102');

    $room = DormRoom::where('room_number', 'Kamar 102')->first();
    expect($room)->not->toBeNull();

    // Pindah ke tab penempatan
    Livewire::actingAs($musyrif)
        ->test(DormRooms::class)
        ->call('setTab', 'placements')
        ->assertSee('Penempatan Santri')
        // Buka modal penempatan dan simpan
        ->call('openPlacementModal', $room->id)
        ->set('placement_room_id', $room->id)
        ->set('placement_student_id', $student->id)
        ->set('placement_bed_number', 'Ranjang Atas No 1')
        ->call('savePlacement');

    expect(DormPlacement::where('student_id', $student->id)->where('dorm_room_id', $room->id)->exists())->toBeTrue();
});

test('musyrif can manage student leaves via StudentLeaves livewire component', function () {
    $musyrif = User::factory()->create();
    $musyrif->assignRole('musyrif');

    $academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $student = Student::factory()->create(['name' => 'Muhammad Zaky']);

    Livewire::actingAs($musyrif)
        ->test(StudentLeaves::class)
        ->assertSee('Perizinan Keluar Santri')
        ->call('openCreateModal')
        ->set('student_id', $student->id)
        ->set('leave_type', 'weekend')
        ->set('reason', 'Pulang rutin ke rumah orang tua')
        ->set('departure_plan', now()->addDay()->format('Y-m-d\TH:i'))
        ->set('return_plan', now()->addDays(2)->format('Y-m-d\TH:i'))
        ->call('saveLeave');

    $leave = StudentLeaveRequest::where('student_id', $student->id)->first();
    expect($leave)->not->toBeNull()
        ->and($leave->status)->toBe('pending');

    // Musyrif approve izin
    Livewire::actingAs($musyrif)
        ->test(StudentLeaves::class)
        ->call('approve', $leave->id);

    $leave->refresh();
    expect($leave->status)->toBe('approved_mentor');

    // Buka modal tiket
    Livewire::actingAs($musyrif)
        ->test(StudentLeaves::class)
        ->call('viewTicket', $leave->id)
        ->assertSet('showTicketModal', true)
        ->assertSee('Tiket Gate Pass Satpam');
});

test('unauthorized user cannot access boarding scanner pos satpam', function () {
    $guru = User::factory()->create();
    $guru->assignRole('guru'); // Guru tidak memiliki izin boarding.gate-pass.scan bawaan

    $this->actingAs($guru)
        ->get(route('boarding.scanner'))
        ->assertForbidden();
});

test('a student cannot have two overlapping active leave requests', function () {
    $student = Student::factory()->create();
    $service = app(BoardingLeaveService::class);
    $payload = [
        'leave_type' => 'weekend',
        'departure_plan' => now()->addDay(),
        'return_plan' => now()->addDays(3),
        'reason' => 'Pulang akhir pekan',
    ];

    $first = $service->createLeaveRequest($student, $payload);

    expect(fn () => $service->createLeaveRequest($student, $payload))->toThrow(ValidationException::class);

    $first->update(['status' => 'rejected']);

    expect($service->createLeaveRequest($student, $payload))->toBeInstanceOf(StudentLeaveRequest::class);
});

test('gate rejects an approved leave that has not started yet or has already expired', function () {
    $satpam = User::factory()->create();
    $satpam->assignRole('satpam');
    $musyrif = User::factory()->create();
    $musyrif->assignRole('musyrif');
    $service = app(BoardingLeaveService::class);

    $early = $service->createLeaveRequest(Student::factory()->create(), [
        'leave_type' => 'weekend',
        'departure_plan' => now()->addDay(),
        'return_plan' => now()->addDays(2),
        'reason' => 'Pulang akhir pekan',
    ]);
    $service->approveLeave($early, $musyrif);

    $expired = $service->createLeaveRequest(Student::factory()->create(), [
        'leave_type' => 'weekend',
        'departure_plan' => now()->subDays(3),
        'return_plan' => now()->subDay(),
        'reason' => 'Pulang akhir pekan',
    ]);
    $service->approveLeave($expired, $musyrif);

    expect(fn () => $service->scanSecurityExit($early->qr_pass_token, $satpam))->toThrow(ValidationException::class, 'belum boleh keluar')
        ->and(fn () => $service->scanSecurityExit($expired->qr_pass_token, $satpam))->toThrow(ValidationException::class, 'batas waktu kembali');
});

test('a guardian can print the leave pass of their own child but not of another family', function () {
    $parent = User::factory()->create();
    $parent->assignRole('wali_murid');
    $ownGuardian = Guardian::factory()->create(['user_id' => $parent->id]);
    $ownChild = Student::factory()->create(['guardian_id' => $ownGuardian->id]);
    $otherChild = Student::factory()->create(['guardian_id' => Guardian::factory()->create()->id]);

    $ownLeave = StudentLeaveRequest::factory()->create(['student_id' => $ownChild->id]);
    $otherLeave = StudentLeaveRequest::factory()->create(['student_id' => $otherChild->id]);

    $this->actingAs($parent)->get(route('boarding.leaves.print', $ownLeave->id))->assertOk();
    $this->actingAs($parent)->get(route('boarding.leaves.print', $otherLeave->id))->assertForbidden();
});
