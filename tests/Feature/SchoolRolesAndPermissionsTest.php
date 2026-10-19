<?php

use App\Models\User;
use App\Policies\RolePolicy;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(PermissionSeeder::class);
});

test('all required school system roles are seeded', function () {
    $expectedRoles = [
        'superadmin',
        'admin',
        'staff',
        'kepala_sekolah',
        'tata_usaha',
        'bendahara',
        'guru',
        'wali_kelas',
        'musyrif',
        'wali_murid',
        'santri',
    ];

    foreach ($expectedRoles as $roleName) {
        expect(Role::where('name', $roleName)->exists())
            ->toBeTrue("Role [{$roleName}] harus ada di database.");
    }
});

test('all school roles are protected from deletion in RolePolicy', function () {
    $policy = new RolePolicy;
    $superadmin = actingAsSuperAdmin();

    foreach (RoleSeeder::ROLES as $roleName) {
        $role = Role::findByName($roleName, 'web');
        expect($policy->delete($superadmin, $role))
            ->toBeFalse("Role [{$roleName}] tidak boleh bisa dihapus karena merupakan peran sistem.");
    }
});

test('kepala sekolah has read-only executive permissions and report printing', function () {
    $user = actingAsRole('kepala_sekolah');

    expect(Gate::allows('view-master-data'))->toBeTrue()
        ->and(Gate::allows('manage-master-data'))->toBeFalse()
        ->and(Gate::allows('view-attendance'))->toBeTrue()
        ->and(Gate::allows('manage-attendance'))->toBeFalse()
        ->and(Gate::allows('view-finance'))->toBeTrue()
        ->and(Gate::allows('cashier-finance'))->toBeFalse()
        ->and(Gate::allows('print-report-card'))->toBeTrue();
});

test('bendahara has full cashier and financial access but no academic grading', function () {
    $user = actingAsRole('bendahara');

    expect(Gate::allows('view-finance'))->toBeTrue()
        ->and(Gate::allows('manage-finance'))->toBeTrue()
        ->and(Gate::allows('cashier-finance'))->toBeTrue()
        ->and(Gate::allows('view-grading'))->toBeFalse()
        ->and(Gate::allows('manage-grading'))->toBeFalse();
});

test('guru has classroom attendance and grading permissions but cannot touch finance', function () {
    $user = actingAsRole('guru');

    expect(Gate::allows('view-attendance'))->toBeTrue()
        ->and(Gate::allows('manage-attendance'))->toBeTrue()
        ->and(Gate::allows('view-grading'))->toBeTrue()
        ->and(Gate::allows('manage-grading'))->toBeTrue()
        ->and(Gate::allows('view-tahfidz'))->toBeTrue()
        ->and(Gate::allows('manage-tahfidz'))->toBeTrue()
        ->and(Gate::allows('cashier-finance'))->toBeFalse()
        ->and(Gate::allows('manage-finance'))->toBeFalse();
});

test('musyrif has boarding and tahfidz mutabaah access', function () {
    $user = actingAsRole('musyrif');

    expect(Gate::allows('view-boarding'))->toBeTrue()
        ->and(Gate::allows('manage-boarding'))->toBeTrue()
        ->and(Gate::allows('view-tahfidz'))->toBeTrue()
        ->and(Gate::allows('manage-tahfidz'))->toBeTrue()
        ->and(Gate::allows('view-conduct'))->toBeTrue()
        ->and(Gate::allows('manage-conduct'))->toBeTrue()
        ->and(Gate::allows('cashier-finance'))->toBeFalse();
});

test('wali murid only has guardian portal access', function () {
    $user = actingAsRole('wali_murid');

    expect(Gate::allows('view-guardian-portal'))->toBeTrue()
        ->and(Gate::allows('view-master-data'))->toBeFalse()
        ->and(Gate::allows('manage-attendance'))->toBeFalse()
        ->and(Gate::allows('cashier-finance'))->toBeFalse();
});

test('demo users for all school roles are properly seeded and assigned roles', function () {
    $this->seed(DatabaseSeeder::class);

    $expectedDemoUsers = [
        'superadmin@example.test' => 'superadmin',
        'admin@example.test' => 'admin',
        'staff@example.test' => 'staff',
        'kepalasekolah@example.test' => 'kepala_sekolah',
        'tu@example.test' => 'tata_usaha',
        'bendahara@example.test' => 'bendahara',
        'guru@example.test' => 'guru',
        'walikelas@example.test' => 'wali_kelas',
        'musyrif@example.test' => 'musyrif',
        'walimurid@example.test' => 'wali_murid',
        'santri@example.test' => 'santri',
    ];

    foreach ($expectedDemoUsers as $email => $role) {
        $user = User::where('email', $email)->first();
        expect($user)->not->toBeNull("Demo user {$email} harus dibuat.")
            ->and($user->hasRole($role))->toBeTrue("User {$email} harus memiliki peran {$role}.");
    }
});
