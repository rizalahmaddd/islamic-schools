<?php

use App\Livewire\Layout\NotificationBell;
use App\Models\Student;
use App\Models\User;
use App\Notifications\BackupFinishedNotification;
use App\Support\Features;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * Klik notifikasi wajib menandainya dibaca DAN pindah ke halaman yang dimaksud, bukan cuma
 * menandai dibaca tanpa aksi lanjutan.
 */
beforeEach(function () {
    $this->recipient = User::factory()->create();
    $this->recipient->assignRole(seededRole('admin'));

    actingAsAdmin();
    $this->student = Student::factory()->create();
    $this->notification = $this->recipient->notifications()->create([
        'id' => Str::uuid()->toString(),
        'type' => BackupFinishedNotification::class,
        'data' => ['message' => 'Santri baru ditambahkan.', 'url' => route('master-data.students.show', $this->student)],
    ]);
});

test('opening a notification marks it read and redirects to its page', function () {
    expect($this->notification)->not->toBeNull();

    $this->actingAs($this->recipient);

    Livewire::test(NotificationBell::class)
        ->call('open', $this->notification->id)
        ->assertRedirect(route('master-data.students.show', $this->student->id));

    expect($this->recipient->fresh()->unreadNotifications)->toHaveCount(0);
});

test('opening a notification belonging to someone else does nothing', function () {
    actingAsAdmin();

    Livewire::test(NotificationBell::class)
        ->call('open', $this->notification->id)
        ->assertNoRedirect();

    expect($this->recipient->fresh()->unreadNotifications)->toHaveCount(1);
});

test('notifications pointing at a disabled feature are hidden', function () {
    $this->actingAs($this->recipient);
    Features::setDisabled(['master-data.students']);

    Livewire::test(NotificationBell::class)
        ->assertViewHas('unreadCount', 0);
});
