<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Definisi lengkap hak akses (perizinan) sistem yang dikelompokkan per modul. Halaman
     * Peran & Perizinan membaca daftar ini, jadi modul baru cukup menambah grupnya di sini.
     *
     * @var array<string, array<string, array{label: string, description: string}>>
     */
    public const PERMISSION_GROUPS = [
        'Master Data' => [
            'master-data.view' => [
                'label' => 'Lihat Master Data',
                'description' => 'Melihat data siswa, guru, kelas/rombel, dan tahun ajaran.',
            ],
            'master-data.manage' => [
                'label' => 'Kelola Master Data',
                'description' => 'Menambah, mengubah, mengimpor Excel, dan menghapus data siswa, guru, kelas.',
            ],
        ],
        'Presensi Harian' => [
            'attendance.view' => [
                'label' => 'Lihat Presensi',
                'description' => 'Melihat rekapitulasi kehadiran harian dan bulanan siswa serta guru.',
            ],
            'attendance.manage' => [
                'label' => 'Kelola Presensi Siswa/Guru',
                'description' => 'Melakukan absensi harian kelas, check-in guru, dan kirim notifikasi WhatsApp.',
            ],
        ],
        'Keuangan & SPP' => [
            'finance.view' => [
                'label' => 'Lihat Keuangan & Tagihan',
                'description' => 'Melihat daftar tagihan SPP, tunggakan siswa, dan riwayat pembayaran.',
            ],
            'finance.manage' => [
                'label' => 'Kelola Tarif & Tagihan',
                'description' => 'Mengatur tarif SPP, alokasi beasiswa/keringanan, dan generate tagihan.',
            ],
            'finance.cashier' => [
                'label' => 'Kasir Pembayaran (POS TU)',
                'description' => 'Menerima pembayaran tunai/transfer dan mencetak kuitansi resmi.',
            ],
        ],
        'Kepesantrenan & Tahfidz' => [
            'islamic.tahfidz.view' => [
                'label' => 'Lihat Capaian Tahfidz',
                'description' => 'Melihat riwayat hafalan Quran (ziyadah & murajaah) santri.',
            ],
            'islamic.tahfidz.manage' => [
                'label' => 'Kelola Setoran Tahfidz',
                'description' => 'Mencatat setoran hafalan surat, ayat, juz, dan nilai tajwid.',
            ],
            'islamic.conduct.view' => [
                'label' => 'Lihat Poin Adab & Disiplin',
                'description' => 'Melihat buku catatan pelanggaran dan prestasi adab santri.',
            ],
            'islamic.conduct.manage' => [
                'label' => 'Kelola Pelanggaran & Prestasi',
                'description' => 'Mencatat poin pelanggaran atau prestasi santri dan tindak lanjutnya.',
            ],
        ],
        'Akademik & E-Rapor' => [
            'academic.journal.view' => [
                'label' => 'Lihat Jurnal Mengajar',
                'description' => 'Melihat catatan materi ajar dan aktivitas harian guru di kelas.',
            ],
            'academic.journal.manage' => [
                'label' => 'Kelola Jurnal Mengajar',
                'description' => 'Menambah, mengubah, dan mengunggah foto dokumentasi jurnal mengajar.',
            ],
            'academic.grading.view' => [
                'label' => 'Lihat Nilai & Ledger Kelas',
                'description' => 'Melihat rekap nilai formatif, sumatif, dan ledger akademik.',
            ],
            'academic.grading.manage' => [
                'label' => 'Kelola Nilai Mata Pelajaran',
                'description' => 'Menginput nilai tugas, UTS, UAS, dan deskripsi capaian pembelajaran.',
            ],
            'academic.assignments.manage' => [
                'label' => 'Kelola Pembagian Tugas Mengajar',
                'description' => 'Menetapkan guru pengampu mata pelajaran per kelas dan KKM.',
            ],
            'academic.report-card.print' => [
                'label' => 'Cetak E-Rapor Resmi',
                'description' => 'Mencetak dokumen E-Rapor resmi PDF beserta lampiran tahfidz.',
            ],
        ],
        'Keasramaan & Asrama' => [
            'boarding.view' => [
                'label' => 'Lihat Keasramaan & Kamar',
                'description' => 'Melihat penempatan kamar santri dan riwayat perizinan.',
            ],
            'boarding.manage' => [
                'label' => 'Kelola Asrama & Perizinan',
                'description' => 'Mengatur kamar asrama dan memvalidasi persetujuan izin keluar/pulang santri.',
            ],
            'boarding.gate-pass.scan' => [
                'label' => 'Scan QR Izin Gerbang (Satpam)',
                'description' => 'Memindai tiket QR izin santri di pos gerbang keamanan.',
            ],
        ],
        'PPDB Online' => [
            'admissions.view' => [
                'label' => 'Lihat Pendaftar PPDB',
                'description' => 'Melihat daftar calon santri baru dan berkas persyaratan.',
            ],
            'admissions.manage' => [
                'label' => 'Kelola PPDB & Konversi Siswa',
                'description' => 'Mengatur gelombang PPDB, seleksi tes, dan 1-klik konversi jadi siswa aktif.',
            ],
        ],
        'Sarpras & Perpustakaan' => [
            'inventory.view' => [
                'label' => 'Lihat Inventaris Sarpras',
                'description' => 'Melihat daftar aset ruangan dan rincian barang inventaris.',
            ],
            'inventory.manage' => [
                'label' => 'Kelola Inventaris Sarpras',
                'description' => 'Mencatat aset ruangan dan mencetak label QR barang inventaris.',
            ],
            'library.view' => [
                'label' => 'Lihat Perpustakaan & Katalog',
                'description' => 'Melihat katalog buku/kitab dan status sirkulasi peminjaman.',
            ],
            'library.manage' => [
                'label' => 'Kelola Perpustakaan',
                'description' => 'Mengelola katalog buku dan sirkulasi peminjaman siswa.',
            ],
        ],
        'Kantin & Kartu RFID' => [
            'cashless.view' => [
                'label' => 'Lihat Kantin & Saldo',
                'description' => 'Melihat daftar kartu RFID, monitoring saldo dompet santri, dan riwayat belanja.',
            ],
            'cashless.manage' => [
                'label' => 'Kelola Kartu & Saldo',
                'description' => 'Mendaftarkan kartu RFID santri, top-up saldo, blokir kartu, dan konfigurasi limit harian.',
            ],
            'cashless.cashier' => [
                'label' => 'Kasir POS Kantin',
                'description' => 'Mengoperasikan kasir kantin untuk transaksi belanja santri via tap kartu RFID.',
            ],
        ],
        'Portal Mandiri Wali Murid' => [
            'portal.guardian.view' => [
                'label' => 'Akses Portal Wali Murid',
                'description' => 'Akses mandiri orang tua untuk melihat absensi, nilai, tahfidz, dan SPP anak.',
            ],
        ],
        'Laporan & Audit' => [
            'reports.activity.view' => [
                'label' => 'Log Aktivitas (Audit Trail)',
                'description' => 'Melihat seluruh jejak audit aktivitas pengguna sistem.',
            ],
            'reports.financial.view' => [
                'label' => 'Laporan Arus Kas & Tunggakan',
                'description' => 'Melihat rekap kas masuk harian/bulanan dan laporan sisa piutang SPP.',
            ],
        ],
        'Pengaturan & Keamanan' => [
            'settings.company.manage' => [
                'label' => 'Pengaturan Profil Lembaga',
                'description' => 'Mengatur nama aplikasi, identitas sekolah/yayasan, kop surat cetak, dan logo.',
            ],
            'roles.manage' => [
                'label' => 'Kelola Peran & Perizinan',
                'description' => 'Membuat peran baru, mengubah perizinan, dan mengatur matriks akses.',
            ],
            'users.manage' => [
                'label' => 'Kelola Penugasan Peran Pengguna',
                'description' => 'Menugaskan dan mencabut peran dari akun pengguna sistem.',
            ],
        ],
    ];

    /**
     * Izin bawaan per peran; "Kembalikan ke default" di halaman Peran & Perizinan memakai daftar ini.
     *
     * @var array<string, list<string>>
     */
    public const DEFAULT_ROLE_PERMISSIONS = [
        'superadmin' => ['*'],
        'admin' => [
            'master-data.view', 'master-data.manage',
            'attendance.view', 'attendance.manage',
            'finance.view', 'finance.manage', 'finance.cashier',
            'islamic.tahfidz.view', 'islamic.tahfidz.manage', 'islamic.conduct.view', 'islamic.conduct.manage',
            'academic.journal.view', 'academic.journal.manage',
            'academic.grading.view', 'academic.grading.manage', 'academic.assignments.manage', 'academic.report-card.print',
            'boarding.view', 'boarding.manage', 'boarding.gate-pass.scan',
            'admissions.view', 'admissions.manage',
            'inventory.view', 'inventory.manage', 'library.view', 'library.manage',
            'cashless.view', 'cashless.manage', 'cashless.cashier',
            'reports.activity.view', 'reports.financial.view',
            'settings.company.manage', 'roles.manage', 'users.manage',
        ],
        'staff' => [
            'master-data.view',
            'attendance.view',
            'inventory.view',
            'library.view',
            'cashless.view',
            'cashless.cashier',
        ],
        'kepala_sekolah' => [
            'master-data.view',
            'attendance.view',
            'finance.view',
            'islamic.tahfidz.view',
            'islamic.conduct.view',
            'academic.journal.view',
            'academic.grading.view',
            'academic.assignments.manage',
            'academic.report-card.print',
            'boarding.view',
            'admissions.view',
            'inventory.view',
            'library.view',
            'cashless.view',
            'reports.activity.view',
            'reports.financial.view',
            'settings.company.manage',
        ],
        'tata_usaha' => [
            'master-data.view', 'master-data.manage',
            'attendance.view',
            'finance.view', 'finance.cashier',
            'academic.assignments.manage',
            'academic.report-card.print',
            'admissions.view', 'admissions.manage',
            'inventory.view', 'inventory.manage', 'library.view', 'library.manage',
            'cashless.view', 'cashless.manage', 'cashless.cashier',
            'settings.company.manage',
        ],
        'bendahara' => [
            'finance.view', 'finance.manage', 'finance.cashier',
            'cashless.view', 'cashless.manage',
            'reports.financial.view',
        ],
        'guru' => [
            'master-data.view',
            'attendance.view', 'attendance.manage',
            'academic.journal.view', 'academic.journal.manage',
            'academic.grading.view', 'academic.grading.manage',
            'islamic.tahfidz.view', 'islamic.tahfidz.manage',
        ],
        'wali_kelas' => [
            'master-data.view',
            'attendance.view', 'attendance.manage',
            'academic.journal.view', 'academic.journal.manage',
            'academic.grading.view', 'academic.grading.manage', 'academic.report-card.print',
            'islamic.tahfidz.view', 'islamic.conduct.view', 'islamic.conduct.manage',
        ],
        'musyrif' => [
            'islamic.tahfidz.view', 'islamic.tahfidz.manage',
            'islamic.conduct.view', 'islamic.conduct.manage',
            'boarding.view', 'boarding.manage', 'boarding.gate-pass.scan',
        ],
        'satpam' => [
            'boarding.gate-pass.scan',
        ],
        'wali_murid' => [
            'portal.guardian.view',
        ],
        'santri' => [
            'portal.guardian.view',
        ],
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $allPermissions = [];
        foreach (self::PERMISSION_GROUPS as $permissions) {
            foreach ($permissions as $permissionName => $meta) {
                Permission::findOrCreate($permissionName, 'web');
                $allPermissions[] = $permissionName;
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::DEFAULT_ROLE_PERMISSIONS as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');

            if ($permissions === ['*']) {
                $role->syncPermissions($allPermissions);
            } else {
                $role->syncPermissions($permissions);
            }
        }
    }
}
