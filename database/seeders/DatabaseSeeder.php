<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(SettingsSeeder::class);

        $this->seedDemoUsers();

        $this->call(QuranSurahSeeder::class);
        $this->call(MasterDataSeeder::class);
    }

    /**
     * Satu user demo per peran supaya tiap peran bisa langsung dicoba login selama pengembangan.
     * Password sama untuk semua: "password". Jangan jalankan seeder ini di production.
     */
    protected function seedDemoUsers(): void
    {
        $demoUsers = [
            ['name' => 'Superadmin Demo', 'email' => 'superadmin@example.test', 'role' => 'superadmin'],
            ['name' => 'Admin Demo', 'email' => 'admin@example.test', 'role' => 'admin'],
            ['name' => 'Staff Demo', 'email' => 'staff@example.test', 'role' => 'staff'],
            ['name' => 'Kepala Sekolah Demo', 'email' => 'kepalasekolah@example.test', 'role' => 'kepala_sekolah'],
            ['name' => 'Tata Usaha Demo', 'email' => 'tu@example.test', 'role' => 'tata_usaha'],
            ['name' => 'Bendahara Demo', 'email' => 'bendahara@example.test', 'role' => 'bendahara'],
            ['name' => 'Guru Demo', 'email' => 'guru@example.test', 'role' => 'guru'],
            ['name' => 'Wali Kelas Demo', 'email' => 'walikelas@example.test', 'role' => 'wali_kelas'],
            ['name' => 'Musyrif Demo', 'email' => 'musyrif@example.test', 'role' => 'musyrif'],
            ['name' => 'Wali Murid Demo', 'email' => 'walimurid@example.test', 'role' => 'wali_murid'],
            ['name' => 'Santri Demo', 'email' => 'santri@example.test', 'role' => 'santri'],
            ['name' => 'Satpam Demo', 'email' => 'satpam@example.test', 'role' => 'satpam'],
        ];

        foreach ($demoUsers as $demoUser) {
            User::firstOrCreate(
                ['email' => $demoUser['email']],
                [
                    'name' => $demoUser['name'],
                    'username' => Str::before($demoUser['email'], '@'),
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            )->assignRole($demoUser['role']);
        }
    }
}
