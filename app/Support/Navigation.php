<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

/**
 * Satu-satunya daftar menu aplikasi: sidebar, flyout sidebar ciut, navigasi bawah mobile, dan
 * pencarian menu di global search membaca dari sini. Modul baru cukup menambah entri di items().
 *
 * Setiap tautan boleh punya:
 * - feature: kunci Features yang harus menyala
 * - can: ability Gate, atau [ability, model] untuk policy
 * - superadmin: true bila hanya untuk Superadmin
 * - mobile: true bila layak jadi tujuan di navigasi bawah mobile (maks. 4 yang lolos izin)
 */
class Navigation
{
    /**
     * @return list<array{label: ?string, items: list<array<string, mixed>>}>
     */
    public static function items(): array
    {
        return [
            [
                'label' => null,
                'items' => [
                    ['label' => 'Dashboard', 'icon' => 'layout-dashboard', 'route' => 'dashboard', 'active' => 'dashboard', 'mobile' => true, 'mobile_label' => 'Beranda', 'keywords' => 'beranda ringkasan home utama'],
                ],
            ],
            [
                'label' => 'Akademik & Santri',
                'items' => [
                    [
                        'key' => 'master-data',
                        'label' => 'Master Data',
                        'icon' => 'database',
                        'feature' => 'master-data',
                        'children' => [
                            ['label' => 'Siswa / Santri', 'icon' => 'graduation-cap', 'route' => 'master-data.students', 'active' => 'master-data.students*', 'feature' => 'master-data.students', 'can' => 'view-master-data', 'mobile' => true, 'mobile_label' => 'Siswa', 'keywords' => 'siswa santri murid anak nis biodata'],
                            ['label' => 'Guru & Staf', 'icon' => 'briefcase', 'route' => 'master-data.teachers', 'active' => 'master-data.teachers*', 'feature' => 'master-data.teachers', 'can' => 'view-master-data', 'keywords' => 'guru ustadz pendidik pengajar staff nip'],
                            ['label' => 'Kelas & Rombel', 'icon' => 'layout-grid', 'route' => 'master-data.classes', 'active' => 'master-data.classes*', 'feature' => 'master-data.classes', 'can' => 'view-master-data', 'keywords' => 'kelas rombel rombongan belajar tingkat wali kelas'],
                            ['label' => 'Tahun Ajaran', 'icon' => 'calendar', 'route' => 'master-data.academic-years', 'active' => 'master-data.academic-years*', 'feature' => 'master-data.academic-years', 'can' => 'view-master-data', 'keywords' => 'tahun ajaran semester kalender akademik'],
                        ],
                    ],
                    [
                        'key' => 'attendance',
                        'label' => 'Presensi Harian',
                        'icon' => 'calendar-check',
                        'feature' => 'attendance',
                        'children' => [
                            ['label' => 'Presensi Siswa', 'icon' => 'calendar-check', 'route' => 'attendance.students', 'active' => 'attendance.students*', 'feature' => 'attendance.student-attendance', 'can' => 'view-attendance', 'mobile' => true, 'mobile_label' => 'Presensi', 'keywords' => 'presensi absensi kehadiran harian siswa santri kelas rombel'],
                            ['label' => 'Rekap Presensi Siswa', 'icon' => 'file-bar-chart', 'route' => 'attendance.reports', 'active' => 'attendance.reports*', 'feature' => 'attendance.student-attendance', 'can' => 'view-attendance', 'keywords' => 'rekapitulasi absensi bulanan persentase hadir sakit izin alpa cetak'],
                            ['label' => 'Check-In Mandiri Guru', 'icon' => 'map-pin', 'route' => 'attendance.teachers.check-in', 'active' => 'attendance.teachers.check-in', 'feature' => 'attendance.teacher-attendance', 'can' => 'view-attendance', 'mobile' => true, 'mobile_label' => 'Check-In', 'keywords' => 'check-in check-out presensi masuk pulang guru gps radius geofencing'],
                            ['label' => 'Presensi Guru & Staf', 'icon' => 'clock', 'route' => 'attendance.teachers', 'active' => 'attendance.teachers', 'feature' => 'attendance.teacher-attendance', 'can' => 'manage-attendance', 'keywords' => 'monitoring absensi guru terlambat tepat waktu dinas'],
                        ],
                    ],
                    [
                        'key' => 'academic',
                        'label' => 'Akademik & E-Rapor',
                        'icon' => 'graduation-cap',
                        'feature' => 'academic',
                        'children' => [
                            ['label' => 'Jurnal Mengajar', 'icon' => 'book-open', 'route' => 'academic.teaching-journals', 'active' => 'academic.teaching-journals*', 'feature' => 'academic.teaching-journal', 'can' => 'view-teaching-journal', 'keywords' => 'jurnal mengajar guru ustadz materi pokok aktivitas kelas'],
                            ['label' => 'Pembagian Tugas Mengajar', 'icon' => 'users-round', 'route' => 'academic.subject-assignments', 'active' => 'academic.subject-assignments*', 'feature' => 'academic.assignments', 'can' => 'view-grading', 'keywords' => 'pembagian tugas mengajar guru pengampu kkm tp kurikulum merdeka'],
                            ['label' => 'Buku Nilai Mata Pelajaran', 'icon' => 'table', 'route' => 'academic.grade-sheet', 'active' => 'academic.grade-sheet*', 'feature' => 'academic.grading', 'can' => 'view-grading', 'keywords' => 'buku nilai ledger formatif sumatif uts uas deskripsi capaian pembelajaran'],
                            ['label' => 'E-Rapor Santri', 'icon' => 'file-text', 'route' => 'academic.report-cards', 'active' => 'academic.report-cards*', 'feature' => 'academic.report-cards', 'can' => 'view-grading', 'keywords' => 'rapor e-rapor cetak pdf catatan wali kelas kenaikan kelulusan mutqin'],
                        ],
                    ],
                ],
            ],
            [
                'label' => 'Kepesantrenan & Asrama',
                'items' => [
                    [
                        'key' => 'islamic',
                        'label' => 'Kepesantrenan & Tahfidz',
                        'icon' => 'book-marked',
                        'feature' => 'islamic',
                        'children' => [
                            ['label' => 'Buku Tahfidz Al-Qur\'an', 'icon' => 'book-open', 'route' => 'islamic.tahfidz', 'active' => 'islamic.tahfidz*', 'feature' => 'islamic.tahfidz', 'can' => 'view-tahfidz', 'mobile' => true, 'mobile_label' => 'Tahfidz', 'keywords' => 'tahfidz hafalan quran ziyadah murajaah surat ayat juz halaqah'],
                            ['label' => 'Mutaba\'ah Ibadah', 'icon' => 'calendar-check-2', 'route' => 'islamic.mutabaah', 'active' => 'islamic.mutabaah*', 'feature' => 'islamic.mutabaah', 'can' => 'view-tahfidz', 'mobile' => true, 'mobile_label' => 'Mutabaah', 'keywords' => 'mutabaah ibadah shalat berjamaah subuh dzuhur ashar maghrib isya dhuha tahajjud tilawah'],
                            ['label' => 'Poin Adab & Disiplin', 'icon' => 'shield-alert', 'route' => 'islamic.conduct', 'active' => 'islamic.conduct*', 'feature' => 'islamic.conduct', 'can' => 'view-conduct', 'keywords' => 'poin pelanggaran prestasi adab disiplin tata tertib santri asrama sp'],
                        ],
                    ],
                    [
                        'key' => 'boarding',
                        'label' => 'Keasramaan & Asrama',
                        'icon' => 'home',
                        'feature' => 'boarding',
                        'children' => [
                            ['label' => 'Kamar & Gedung Asrama', 'icon' => 'bed', 'route' => 'boarding.rooms', 'active' => 'boarding.rooms*', 'feature' => 'boarding.rooms', 'can' => 'view-boarding', 'keywords' => 'asrama kamar santri gedung ranjang kasur musyrif penempatan pembina'],
                            ['label' => 'Perizinan Keluar Santri', 'icon' => 'file-text', 'route' => 'boarding.leaves', 'active' => 'boarding.leaves*', 'feature' => 'boarding.leaves', 'can' => 'view-boarding', 'keywords' => 'perizinan santri izin keluar pulang izin sakit tiket gerbang pass surat izin'],
                            ['label' => 'Scanner Gerbang Satpam', 'icon' => 'qr-code', 'route' => 'boarding.scanner', 'active' => 'boarding.scanner*', 'feature' => 'boarding.gate-pass', 'can' => 'scan-gate-pass', 'mobile' => true, 'mobile_label' => 'Scan Gerbang', 'keywords' => 'scanner satpam gerbang pos keamanan tiket barcode qr code izin santri keluar masuk'],
                        ],
                    ],
                ],
            ],
            [
                'label' => 'Keuangan & Bisnis',
                'items' => [
                    [
                        'key' => 'finance',
                        'label' => 'Keuangan & SPP',
                        'icon' => 'wallet',
                        'feature' => 'finance',
                        'children' => [
                            ['label' => 'Kasir Pembayaran (POS)', 'icon' => 'credit-card', 'route' => 'finance.cashier', 'active' => 'finance.cashier*', 'feature' => 'finance.cashier', 'can' => 'cashier-finance', 'mobile' => true, 'mobile_label' => 'Kasir SPP', 'keywords' => 'kasir spp pos pembayaran tagihan kuitansi struk tu'],
                            ['label' => 'Buku Kas & Transaksi', 'icon' => 'receipt', 'route' => 'finance.transactions', 'active' => 'finance.transactions*', 'feature' => 'finance.transactions', 'can' => 'view-finance', 'keywords' => 'transaksi kuitansi riwayat rekap buku kas penerimaan tu'],
                            ['label' => 'Tarif & Kategori Biaya', 'icon' => 'calculator', 'route' => 'finance.fees', 'active' => 'finance.fees*', 'feature' => 'finance.fees', 'can' => 'manage-finance', 'keywords' => 'tarif spp biaya beasiswa keringanan generate tagihan bulanan'],
                        ],
                    ],
                    [
                        'key' => 'cashless',
                        'label' => 'Kantin & Kartu RFID',
                        'icon' => 'credit-card',
                        'feature' => 'cashless',
                        'children' => [
                            ['label' => 'Kasir Kantin (POS)', 'icon' => 'shopping-cart', 'route' => 'cashless.pos', 'active' => 'cashless.pos*', 'feature' => 'cashless.canteen-pos', 'can' => 'cashier-cashless', 'mobile' => true, 'mobile_label' => 'POS Kantin', 'keywords' => 'pos kantin kasir cashless rfid smart card jajan makan minum santri tap scanner'],
                            ['label' => 'Kartu RFID & Saldo', 'icon' => 'credit-card', 'route' => 'cashless.cards', 'active' => 'cashless.cards*', 'feature' => 'cashless.cards', 'can' => 'manage-cashless', 'keywords' => 'kartu rfid smart card saldo dompet santri topup limit daily batas harian jajan blokir'],
                        ],
                    ],
                ],
            ],
            [
                'label' => 'Layanan & Sarpras',
                'items' => [
                    [
                        'key' => 'admissions',
                        'label' => 'PPDB Santri Baru',
                        'icon' => 'user-plus',
                        'feature' => 'admissions',
                        'children' => [
                            ['label' => 'Data Calon Santri', 'icon' => 'users', 'route' => 'admissions.candidates', 'active' => 'admissions.candidates*', 'feature' => 'admissions.candidates', 'can' => 'view-admissions', 'keywords' => 'ppdb calon santri pendaftar seleksi tes wawancara lulus konversi'],
                            ['label' => 'Gelombang PPDB', 'icon' => 'calendar', 'route' => 'admissions.batches', 'active' => 'admissions.batches*', 'feature' => 'admissions.batches', 'can' => 'manage-admissions', 'keywords' => 'gelombang ppdb kuota pendaftaran biaya formulir jadwal buka tutup'],
                        ],
                    ],
                    [
                        'key' => 'library',
                        'label' => 'Perpustakaan & Pustaka',
                        'icon' => 'book-open',
                        'feature' => 'library',
                        'children' => [
                            ['label' => 'Sirkulasi Peminjaman', 'icon' => 'arrow-left-right', 'route' => 'library.circulation', 'active' => 'library.circulation*', 'feature' => 'library.circulation', 'can' => 'view-library', 'keywords' => 'sirkulasi peminjaman buku pengembalian denda perpustakaan santri guru'],
                            ['label' => 'Katalog Buku & Kitab', 'icon' => 'book-open', 'route' => 'library.books', 'active' => 'library.books*', 'feature' => 'library.books', 'can' => 'view-library', 'keywords' => 'katalog buku kitab kuning pelajaran isbn nomor panggil rak eksemplar'],
                        ],
                    ],
                    [
                        'key' => 'inventory',
                        'label' => 'Sarpras & Inventaris',
                        'icon' => 'package',
                        'feature' => 'inventory',
                        'children' => [
                            ['label' => 'Inventaris Barang & Aset', 'icon' => 'package', 'route' => 'inventory.items', 'active' => 'inventory.items*', 'feature' => 'inventory.items', 'can' => 'view-inventory', 'keywords' => 'inventaris sarpras aset barang kondisi meja kursi lab komputer qr label stiker'],
                            ['label' => 'Ruangan & Lokasi Aset', 'icon' => 'building-2', 'route' => 'inventory.rooms', 'active' => 'inventory.rooms*', 'feature' => 'inventory.rooms', 'can' => 'view-inventory', 'keywords' => 'ruangan sarpras lokasi aset gedung pic penanggung jawab fasilitas'],
                        ],
                    ],
                ],
            ],
            [
                'label' => 'Laporan & Rekapitulasi',
                'items' => [
                    [
                        'key' => 'reports',
                        'label' => 'Pusat Laporan',
                        'icon' => 'bar-chart-3',
                        'feature' => 'reports',
                        'children' => [
                            ['label' => 'Pusat Laporan Terpadu', 'icon' => 'file-bar-chart', 'route' => 'reports.center', 'active' => 'reports.center*', 'feature' => 'reports.center', 'can' => 'view-reports', 'mobile' => true, 'mobile_label' => 'Laporan', 'keywords' => 'laporan terpadu rekapitulasi keuangan spp presensi tahfidz asrama eksekutif cetak export pdf excel ringkasan'],
                            ['label' => 'Log Aktivitas Audit', 'icon' => 'scroll-text', 'route' => 'reports.activity-log', 'feature' => 'reports.activity-log', 'can' => ['viewAny', Activity::class], 'keywords' => 'audit trail jejak riwayat login audit log aktivitas user'],
                        ],
                    ],
                ],
            ],
            [
                'label' => 'Portal & Pengaturan',
                'items' => [
                    ['label' => 'Portal Wali Murid', 'icon' => 'home', 'route' => 'portal.guardian', 'active' => 'portal.guardian*', 'feature' => 'portal.guardian-portal', 'can' => 'view-guardian-portal', 'mobile' => true, 'mobile_label' => 'Portal Wali', 'keywords' => 'portal wali santri orang tua anak kehadiran absensi tahfidz spp tagihan rapor'],
                    [
                        'key' => 'settings',
                        'label' => 'Pengaturan Sistem',
                        'icon' => 'settings',
                        'children' => [
                            ['label' => 'Profil Lembaga', 'icon' => 'building-2', 'route' => 'settings.company-profile', 'feature' => 'settings.company-profile', 'can' => ['update', Setting::class], 'keywords' => 'profil lembaga pesantren sekolah setting kop surat logo branding'],
                            ['label' => 'CMS Landing Page', 'icon' => 'layout-template', 'route' => 'settings.landing-page', 'active' => 'settings.landing-page*', 'feature' => 'settings.landing-page', 'can' => ['update', Setting::class], 'keywords' => 'cms landing page website beranda hero sambutan faq agenda pengumuman'],
                            ['label' => 'Peran & Perizinan', 'icon' => 'shield-alert', 'route' => 'settings.roles-and-permissions', 'active' => 'settings.roles-and-permissions*', 'feature' => 'settings.roles-and-permissions', 'can' => ['viewAny', Role::class], 'keywords' => 'role permission peran perizinan hak akses user pengguna'],
                            ['label' => 'Pengaturan Fitur', 'icon' => 'toggle-right', 'route' => 'settings.features', 'superadmin' => true, 'keywords' => 'fitur modul aktif nonaktif toggle'],
                            ['label' => 'Backup & Restore', 'icon' => 'database-backup', 'route' => 'settings.backups', 'active' => 'settings.backups*', 'superadmin' => true, 'keywords' => 'backup cadangan restore pulihkan database'],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Menu yang boleh dilihat user: tautan yang gagal izin/fiturnya mati dibuang, grup tanpa
     * anak dan bagian kosong ikut hilang.
     *
     * @return list<array{label: ?string, items: list<array<string, mixed>>}>
     */
    public static function forUser(User $user): array
    {
        $sections = [];

        foreach (self::items() as $section) {
            $items = [];

            foreach ($section['items'] as $item) {
                if (isset($item['children'])) {
                    if (isset($item['feature']) && ! Features::enabled($item['feature'])) {
                        continue;
                    }

                    $item['children'] = array_values(array_filter($item['children'], fn (array $link) => self::allows($user, $link)));

                    if ($item['children'] !== []) {
                        $items[] = $item;
                    }
                } elseif (self::allows($user, $item)) {
                    $items[] = $item;
                }
            }

            if ($items !== []) {
                $sections[] = ['label' => $section['label'], 'items' => $items];
            }
        }

        return $sections;
    }

    /**
     * Semua tautan (tanpa grup) yang boleh dibuka user, beserta nama bagian induknya.
     *
     * @return list<array<string, mixed>>
     */
    public static function linksForUser(User $user): array
    {
        $links = [];

        foreach (self::forUser($user) as $section) {
            foreach ($section['items'] as $item) {
                foreach ($item['children'] ?? [$item] as $link) {
                    $links[] = $link + ['section' => isset($item['children']) ? $item['label'] : ($section['label'] ?? 'Beranda')];
                }
            }
        }

        return $links;
    }

    /**
     * @param  array<string, mixed>  $link
     */
    public static function isActive(array $link): bool
    {
        return request()->routeIs($link['active'] ?? $link['route']);
    }

    /**
     * @param  array<string, mixed>  $group
     */
    public static function isGroupActive(array $group): bool
    {
        foreach ($group['children'] ?? [] as $link) {
            if (self::isActive($link)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $link
     */
    private static function allows(User $user, array $link): bool
    {
        if (($link['route'] ?? null) === 'dashboard' && $user->hasRole('wali_murid')) {
            return false;
        }

        if (($link['superadmin'] ?? false) && ! $user->isSuperAdmin()) {
            return false;
        }

        if (isset($link['feature']) && ! Features::enabled($link['feature'])) {
            return false;
        }

        if (isset($link['can'])) {
            $ability = (array) $link['can'];

            if (! $user->can($ability[0], $ability[1] ?? [])) {
                return false;
            }
        }

        return true;
    }
}
