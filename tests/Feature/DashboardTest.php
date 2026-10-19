<?php

use App\Models\Student;
use App\Models\User;

test('root renders public landing page', function () {
    $this->get('/')->assertOk();
});

test('guests are redirected to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('a user without a role sees the dashboard without activity feed', function () {
    Student::factory()->count(3)->create(['status' => 'active']);
    $user = User::factory()->create();

    $this->actingAs($user)->get('/dashboard')
        ->assertOk()
        ->assertDontSee('Aktivitas Terbaru');
});

test('staff sees the student count but not the activity feed', function () {
    Student::factory()->count(3)->create(['status' => 'active']);
    Student::factory()->create(['status' => 'graduated']);
    actingAsRole('staff');

    $this->get('/dashboard')
        ->assertOk()
        ->assertSee('Santri & Siswa')
        ->assertSee('3')
        ->assertDontSee('Aktivitas Terbaru');
});

test('admin sees the recent activity feed', function () {
    actingAsAdmin();
    Student::factory()->create(['name' => 'Santri Contoh Audit']);

    $this->get('/dashboard')
        ->assertOk()
        ->assertSee('Aktivitas Terbaru');
});

test('a user without any role is told to ask an admin for access', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Akun Anda belum punya peran');
});
