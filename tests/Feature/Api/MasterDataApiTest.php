<?php

use App\Models\Student;
use App\Models\User;
use App\Support\Features;
use Laravel\Sanctum\Sanctum;

it('lets accounts with view access read students', function () {
    apiActingAs('staff');
    Student::factory()->create(['name' => 'Ahmad Faiz', 'status' => 'active']);
    Student::factory()->create(['name' => 'Zaidan Hakim', 'status' => 'inactive']);

    $this->getJson('/api/v1/master-data/students?status=active')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Ahmad Faiz')
        ->assertJsonStructure(['links', 'meta' => ['total', 'per_page']]);
});

it('keeps accounts without a role out of master data', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/v1/master-data/students')->assertForbidden();
});

it('only lets roles with the manage permission change students', function () {
    $payload = [
        'nis' => 'NIS-API-001',
        'nisn' => '0099887766',
        'name' => 'Bilal Al-Habasyi',
        'gender' => 'L',
    ];

    apiActingAs('staff');
    $this->postJson('/api/v1/master-data/students', $payload)->assertForbidden();

    apiActingAs('admin');
    $this->postJson('/api/v1/master-data/students', $payload)
        ->assertCreated()
        ->assertJsonPath('data.nis', 'NIS-API-001');

    $this->postJson('/api/v1/master-data/students', [...$payload, 'name' => 'Santri Lain'])
        ->assertJsonValidationErrors('nis');
});

it('ignores the record itself when checking unique fields on update', function () {
    apiActingAs('admin');
    $student = Student::factory()->create(['nis' => 'NIS-SAME-01', 'name' => 'Nama Lama', 'gender' => 'L']);

    $this->putJson("/api/v1/master-data/students/{$student->id}", [
        'nis' => 'NIS-SAME-01',
        'name' => 'Nama Baru',
        'gender' => 'L',
    ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Nama Baru');
});

it('soft deletes a student', function () {
    apiActingAs('admin');
    $student = Student::factory()->create();

    $this->deleteJson("/api/v1/master-data/students/{$student->id}")->assertNoContent();

    $this->assertSoftDeleted($student);
});

it('returns a friendly 404 for a missing record', function () {
    apiActingAs('admin');

    $this->getJson('/api/v1/master-data/students/999999')
        ->assertNotFound()
        ->assertJsonPath('message', 'Data tidak ditemukan.');
});

it('closes endpoints of a disabled feature', function () {
    apiActingAs('admin');
    Features::setDisabled(['master-data.students']);

    $this->getJson('/api/v1/master-data/students')
        ->assertForbidden()
        ->assertJsonPath('message', 'Fitur ini sedang dinonaktifkan.');
});
