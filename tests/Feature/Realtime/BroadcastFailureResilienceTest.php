<?php

use App\Models\Student;

/**
 * Menyimpan data tidak boleh gagal hanya karena Reverb tidak jalan: semua event realtime wajib
 * ShouldRescue (App\Events\Concerns\BroadcastsToDashboard). Test ini menyalakan driver "reverb"
 * sungguhan (bukan "null" bawaan phpunit.xml) supaya koneksi ke server yang mati benar-benar dicoba.
 */
test('saving a student succeeds even when the broadcast server is unreachable', function () {
    config(['broadcasting.default' => 'reverb']);
    actingAsAdmin();

    $student = Student::factory()->create();

    expect($student->exists)->toBeTrue();
    $this->assertDatabaseHas('students', ['id' => $student->id]);
});
