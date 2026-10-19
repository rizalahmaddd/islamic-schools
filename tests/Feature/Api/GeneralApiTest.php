<?php

use App\Models\Student;
use App\Notifications\BackupFinishedNotification;
use App\Support\Features;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

it('shows only the stats a role may see', function () {
    Student::factory()->count(2)->create(['status' => 'active']);

    apiActingAs('staff');
    $this->getJson('/api/v1/dashboard')
        ->assertOk()
        ->assertJsonPath('data.stats.0.key', 'students_active')
        ->assertJsonPath('data.stats.0.count', 2);
});

it('drops stats of disabled features', function () {
    apiActingAs('admin');
    Features::setDisabled(['master-data']);

    expect($this->getJson('/api/v1/dashboard')->json('data.stats'))->toBe([]);
});

it('lists notifications with a deep link and marks them read', function () {
    $user = apiActingAs('admin');
    $student = Student::factory()->create();
    $user->notifications()->create([
        'id' => Str::uuid()->toString(),
        'type' => BackupFinishedNotification::class,
        'data' => ['message' => 'Santri baru ditambahkan.', 'url' => route('master-data.students.show', $student)],
    ]);
    Sanctum::actingAs($user);

    $notification = $this->getJson('/api/v1/notifications')
        ->assertOk()
        ->assertJsonPath('meta.unread_count', 1)
        ->assertJsonPath('data.0.target', ['type' => 'student', 'id' => $student->id])
        ->json('data.0');

    $this->postJson("/api/v1/notifications/{$notification['id']}/read")->assertOk();
    $this->getJson('/api/v1/notifications/unread-count')->assertJsonPath('data.unread_count', 0);
});

it('searches students the same way as the web search box', function () {
    apiActingAs('staff');
    $student = Student::factory()->create(['name' => 'Ahmad Pencarian Unik']);

    $this->getJson('/api/v1/search?q=Pencarian')
        ->assertOk()
        ->assertJsonPath('data.0.group', 'Siswa / Santri')
        ->assertJsonPath('data.0.items.0.target', ['type' => 'student', 'id' => $student->id]);
});

it('returns app configuration in meta', function () {
    apiActingAs('staff');

    $this->getJson('/api/v1/meta')
        ->assertOk()
        ->assertJsonStructure(['data' => ['app' => ['name', 'company_name', 'tagline', 'logo_url'], 'enums']]);
});
