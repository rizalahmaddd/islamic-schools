<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Nilai awal branding aplikasi dan kop surat dokumen cetak resmi pesantren/sekolah.
 */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        Setting::putMany([
            'app_name' => 'SIM Pesantren & Sekolah Islam',
            'app_tagline' => 'Sistem Informasi Akademik, Keuangan & Kepesantrenan',
            'company_name' => 'Pondok Pesantren & Madrasah Modern Al-Izzah',
            'company_tagline' => 'Mencetak Generasi Qur\'ani, Berakhlak Mulia & Berwawasan Global',
            'company_address' => 'Jl. Pesantren Luhur No. 99, Cibubur, Jakarta Timur 13720',
            'company_phone' => '021-88997766 / 0812-3456-7890',
            'school_lat' => -6.200000,
            'school_lng' => 106.816666,
            'school_radius' => 100,
        ]);
    }
}
