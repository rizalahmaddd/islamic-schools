<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Sakelar fitur per modul yang diatur Superadmin di Pengaturan Fitur. Fitur yang dimatikan
 * tertutup untuk semua akun (termasuk Superadmin), apa pun peran dan izinnya; data dan izin
 * tetap tersimpan sehingga menyalakannya lagi langsung memulihkan akses.
 */
class Features
{
    public const DISABLED_KEY = 'features.disabled';

    public const MODULE_CATEGORIES = [
        'data' => 'Data & Operasional',
        'system' => 'Laporan & Sistem',
    ];

    /**
     * Pola nama route per fitur. Route yang tidak tercakup di sini (dashboard, profil, halaman
     * Pengaturan Fitur & Backup) selalu terbuka. Modul baru cukup didaftarkan di sini supaya
     * muncul di halaman Pengaturan Fitur.
     *
     * @var array<string, array{label: string, icon: string, category: string, description: string, features: array<string, array{label: string, description: string, routes: list<string>}>}>
     */
    public const MODULES = [
        'master-data' => [
            'label' => 'Master Data',
            'category' => 'data',
            'icon' => 'database',
            'description' => 'Data referensi sekolah: siswa, guru, kelas, dan tahun ajaran.',
            'features' => [
                'students' => [
                    'label' => 'Siswa & Santri',
                    'description' => 'Daftar santri/siswa, data wali murid, dan penempatan rombel.',
                    'routes' => ['master-data.students*'],
                ],
                'teachers' => [
                    'label' => 'Guru & Pendidik',
                    'description' => 'Daftar guru pengampu, ustadz, dan tenaga kependidikan.',
                    'routes' => ['master-data.teachers*'],
                ],
                'classes' => [
                    'label' => 'Kelas & Rombel',
                    'description' => 'Rombongan belajar, jenjang tingkat, dan alokasi wali kelas.',
                    'routes' => ['master-data.classes*'],
                ],
                'academic-years' => [
                    'label' => 'Tahun Ajaran',
                    'description' => 'Kalender tahun ajaran dan semester aktif sekolah.',
                    'routes' => ['master-data.academic-years*'],
                ],
            ],
        ],
        'attendance' => [
            'label' => 'Presensi Harian',
            'category' => 'data',
            'icon' => 'calendar-check',
            'description' => 'Presensi harian siswa/santri, notifikasi WhatsApp wali murid, dan kehadiran guru.',
            'features' => [
                'student-attendance' => [
                    'label' => 'Presensi Siswa',
                    'description' => 'Lembar absensi cepat harian per rombel dan notifikasi WA wali santri.',
                    'routes' => ['attendance.students*', 'attendance.reports*'],
                ],
                'teacher-attendance' => [
                    'label' => 'Presensi Guru & Staf',
                    'description' => 'Presensi mandiri guru via GPS geofencing dan monitoring kehadiran staf.',
                    'routes' => ['attendance.teachers*'],
                ],
            ],
        ],
        'academic' => [
            'label' => 'Akademik & Pembelajaran',
            'category' => 'data',
            'icon' => 'book-open',
            'description' => 'Jurnal mengajar harian, ledger nilai, dan pencatatan aktivitas kelas.',
            'features' => [
                'teaching-journal' => [
                    'label' => 'Jurnal Mengajar',
                    'description' => 'Pencatatan materi ajar harian guru dan dokumentasi aktivitas kelas.',
                    'routes' => ['academic.teaching-journals*'],
                ],
                'assignments' => [
                    'label' => 'Pembagian Tugas Mengajar',
                    'description' => 'Penetapan guru pengampu mata pelajaran per rombel dan KKM.',
                    'routes' => ['academic.subject-assignments*'],
                ],
                'grading' => [
                    'label' => 'Buku Nilai Mata Pelajaran',
                    'description' => 'Input nilai formatif, UTS, UAS, dan perumusan capaian kompetensi.',
                    'routes' => ['academic.grade-sheet*'],
                ],
                'report-cards' => [
                    'label' => 'E-Rapor Santri',
                    'description' => 'Pengolahan ledger rapor, catatan wali kelas, dan cetak dokumen resmi.',
                    'routes' => ['academic.report-cards*'],
                ],
            ],
        ],
        'finance' => [
            'label' => 'Keuangan & SPP',
            'category' => 'data',
            'icon' => 'wallet',
            'description' => 'Tagihan SPP santri, kasir POS kuitansi TU, dan buku kas penerimaan.',
            'features' => [
                'cashier' => [
                    'label' => 'Kasir POS TU',
                    'description' => 'Pencatatan pembayaran kasir fisik, cetak kuitansi resmi, dan notifikasi WA.',
                    'routes' => ['finance.cashier*', 'finance.receipts*'],
                ],
                'transactions' => [
                    'label' => 'Buku Kas & Transaksi',
                    'description' => 'Monitoring transaksi pembayaran, buku kas penerimaan, dan ekspor data.',
                    'routes' => ['finance.transactions*', 'finance.cashbook*'],
                ],
                'fees' => [
                    'label' => 'Pengaturan Biaya & SPP',
                    'description' => 'Kelola pos tarif SPP, alokasi beasiswa, dan generate tagihan massal.',
                    'routes' => ['finance.fees*'],
                ],
            ],
        ],
        'islamic' => [
            'label' => 'Kepesantrenan & Tahfidz',
            'category' => 'data',
            'icon' => 'book-open',
            'description' => 'Buku setoran tahfidz 30 juz, mutabaah ibadah harian, dan poin adab santri.',
            'features' => [
                'tahfidz' => [
                    'label' => 'Buku Tahfidz Al-Qur\'an',
                    'description' => 'Pencatatan setoran ziyadah, murajaah, dan visualisasi capaian 30 juz.',
                    'routes' => ['islamic.tahfidz*'],
                ],
                'mutabaah' => [
                    'label' => 'Mutaba\'ah Ibadah Santri',
                    'description' => 'Monitoring shalat fardhu berjamaah, dhuha, tahajjud, dan tilawah.',
                    'routes' => ['islamic.mutabaah*'],
                ],
                'conduct' => [
                    'label' => 'Poin Adab & Disiplin',
                    'description' => 'Buku rekam poin pelanggaran disiplin dan prestasi santri.',
                    'routes' => ['islamic.conduct*'],
                ],
            ],
        ],
        'boarding' => [
            'label' => 'Keasramaan & Asrama',
            'category' => 'data',
            'icon' => 'home',
            'description' => 'Kamar asrama santri, musyrif pembina, dan perizinan keluar gerbang (gate pass QR).',
            'features' => [
                'rooms' => [
                    'label' => 'Kamar Asrama & Penempatan',
                    'description' => 'Kelola gedung asrama, kapasitas kamar, dan penempatan santri per kamar.',
                    'routes' => ['boarding.rooms*'],
                ],
                'leaves' => [
                    'label' => 'Perizinan Keluar Santri',
                    'description' => 'Pengajuan izin keluar/pulang, persetujuan musyrif, dan cetak tiket QR.',
                    'routes' => ['boarding.leaves*'],
                ],
                'gate-pass' => [
                    'label' => 'Scanner Gerbang Satpam',
                    'description' => 'Validasi tiket QR gate pass santri keluar/kembali di pos gerbang keamanan.',
                    'routes' => ['boarding.scanner*'],
                ],
            ],
        ],
        'admissions' => [
            'label' => 'PPDB Santri Baru',
            'category' => 'data',
            'icon' => 'user-plus',
            'description' => 'Pendaftaran santri baru online, gelombang seleksi, tes masuk, dan 1-klik konversi santri aktif.',
            'features' => [
                'candidates' => [
                    'label' => 'Data Calon Santri',
                    'description' => 'Verifikasi berkas, input nilai tes, seleksi kelulusan, dan konversi ke santri aktif.',
                    'routes' => ['admissions.candidates*', 'ppdb*'],
                ],
                'batches' => [
                    'label' => 'Gelombang PPDB',
                    'description' => 'Pengaturan jadwal gelombang, kuota pendaftaran, dan biaya formulir.',
                    'routes' => ['admissions.batches*'],
                ],
            ],
        ],
        'inventory' => [
            'label' => 'Sarpras & Inventaris',
            'category' => 'data',
            'icon' => 'package',
            'description' => 'Aset sarana prasarana sekolah, master ruangan, kondisi fisik, dan stiker label QR barang.',
            'features' => [
                'items' => [
                    'label' => 'Inventaris Barang & Aset',
                    'description' => 'Pencatatan aset sarpras, nilai valuasi, kondisi fisik, dan cetak stiker QR.',
                    'routes' => ['inventory.items*', 'inventory.print-item-label*'],
                ],
                'rooms' => [
                    'label' => 'Ruangan & Lokasi Aset',
                    'description' => 'Master ruangan sarpras, gedung lokasi, dan cetak seluruh label aset per ruangan.',
                    'routes' => ['inventory.rooms*', 'inventory.print-room-labels*'],
                ],
            ],
        ],
        'library' => [
            'label' => 'Perpustakaan & Pustaka',
            'category' => 'data',
            'icon' => 'book-open',
            'description' => 'Katalog kitab kuning, buku teks pelajaran, dan meja sirkulasi peminjaman siswa/guru.',
            'features' => [
                'circulation' => [
                    'label' => 'Sirkulasi Peminjaman',
                    'description' => 'Peminjaman dan pengembalian buku, batas waktu pinjam, dan hitung denda otomatis.',
                    'routes' => ['library.circulation*'],
                ],
                'books' => [
                    'label' => 'Katalog Buku & Kitab',
                    'description' => 'Katalog pustaka, kategori kitab kuning, ISBN, nomor panggil, dan kuota stok eksemplar.',
                    'routes' => ['library.books*'],
                ],
            ],
        ],
        'cashless' => [
            'label' => 'Kantin & Kartu RFID',
            'category' => 'data',
            'icon' => 'credit-card',
            'description' => 'Kasir kantin cashless, tap kartu RFID santri, top-up saldo dompet, dan proteksi limit harian.',
            'features' => [
                'canteen-pos' => [
                    'label' => 'Kasir Kantin (POS)',
                    'description' => 'Penjualan makanan & minuman santri dengan pemindaian kilat tap RFID.',
                    'routes' => ['cashless.pos*'],
                ],
                'cards' => [
                    'label' => 'Kartu RFID & Saldo',
                    'description' => 'Penerbitan kartu RFID, blokir kartu hilang, top-up saldo, dan batasan limit jajan harian.',
                    'routes' => ['cashless.cards*'],
                ],
            ],
        ],
        'portal' => [
            'label' => 'Portal Wali Murid',
            'category' => 'data',
            'icon' => 'home',
            'description' => 'Akses mandiri wali santri: absensi, tahfidz, nilai, dan tagihan SPP.',
            'features' => [
                'guardian-portal' => [
                    'label' => 'Portal Mandiri Wali Murid',
                    'description' => 'Dashboard ringkasan santri, presensi, hafalan, dan tagihan.',
                    'routes' => ['portal*'],
                ],
            ],
        ],
        'reports' => [
            'label' => 'Laporan & Rekapitulasi',
            'category' => 'system',
            'icon' => 'bar-chart-3',
            'description' => 'Pusat laporan operasional sekolah: keuangan, presensi, tahfidz, akademik, dan jejak audit.',
            'features' => [
                'center' => [
                    'label' => 'Pusat Laporan Terpadu',
                    'description' => 'Rekapitulasi lengkap kas/SPP, presensi santri/guru, tahfidz Al-Qur\'an, dan keasramaan.',
                    'routes' => ['reports.center*'],
                ],
                'activity-log' => [
                    'label' => 'Log Aktivitas',
                    'description' => 'Audit trail aktivitas pengguna, riwayat login, dan perubahan data.',
                    'routes' => ['reports.activity-log'],
                ],
            ],
        ],
        'settings' => [
            'label' => 'Pengaturan',
            'category' => 'system',
            'icon' => 'settings',
            'description' => 'Identitas lembaga/sekolah dan hak akses pengguna berbasis peran (RBAC).',
            'features' => [
                'company-profile' => [
                    'label' => 'Profil Lembaga & Sekolah',
                    'description' => 'Nama aplikasi, identitas pesantren/sekolah, logo, dan kop surat dokumen cetak.',
                    'routes' => ['settings.company-profile'],
                ],
                'landing-page' => [
                    'label' => 'CMS Landing Page',
                    'description' => 'Kelola teks, banner PPDB, sambutan mudir, statistik, dan agenda halaman publik.',
                    'routes' => ['settings.landing-page*'],
                ],
                'roles-and-permissions' => [
                    'label' => 'Peran & Perizinan',
                    'description' => 'Manajemen peran (roles), izin akses (permissions), dan penugasan peran ke pengguna.',
                    'routes' => ['settings.roles-and-permissions*'],
                ],
            ],
        ],
    ];

    /** @var array<string, list<string>> */
    private static array $urlCache = [];

    /**
     * Kunci modul ("master-data") atau fitur ("master-data.students"). Modul dianggap aktif selama
     * modulnya tidak dimatikan dan masih ada minimal satu fitur di dalamnya yang menyala.
     */
    public static function enabled(string $key): bool
    {
        if (! self::isKnown($key)) {
            throw new InvalidArgumentException("Fitur [{$key}] tidak terdaftar di Features::MODULES.");
        }

        $disabled = self::disabledKeys();

        if (str_contains($key, '.')) {
            return ! in_array(Str::before($key, '.'), $disabled, true) && ! in_array($key, $disabled, true);
        }

        if (in_array($key, $disabled, true)) {
            return false;
        }

        foreach (array_keys(self::MODULES[$key]['features']) as $feature) {
            if (! in_array("{$key}.{$feature}", $disabled, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Satu route bisa dimiliki beberapa fitur, dan semuanya harus menyala.
     *
     * @return list<string>
     */
    public static function featuresForRoute(?string $routeName): array
    {
        if ($routeName === null) {
            return [];
        }

        $features = [];

        foreach (self::MODULES as $module => $definition) {
            foreach ($definition['features'] as $feature => $featureDefinition) {
                if (Str::is($featureDefinition['routes'], $routeName)) {
                    $features[] = "{$module}.{$feature}";
                }
            }
        }

        return $features;
    }

    public static function allowsRoute(?string $routeName): bool
    {
        return self::allEnabled(self::featuresForRoute($routeName));
    }

    /**
     * Untuk tautan yang hanya tersedia sebagai URL (komponen dashboard, notifikasi, x-feature-link).
     * URL di luar aplikasi atau yang tidak cocok dengan route mana pun dianggap boleh.
     */
    public static function allowsUrl(?string $url): bool
    {
        if ($url === null || $url === '') {
            return true;
        }

        $path = (string) parse_url($url, PHP_URL_PATH);

        if (! array_key_exists($path, self::$urlCache)) {
            try {
                $routeName = Route::getRoutes()->match(Request::create($path))->getName();
            } catch (HttpException) {
                $routeName = null;
            }

            self::$urlCache[$path] = self::featuresForRoute($routeName);
        }

        return self::allEnabled(self::$urlCache[$path]);
    }

    /**
     * @param  list<string>  $features
     */
    private static function allEnabled(array $features): bool
    {
        foreach ($features as $feature) {
            if (! self::enabled($feature)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return list<string>
     */
    public static function disabledKeys(): array
    {
        // Sidebar memanggil ini puluhan kali per halaman dan cache Setting ada di database,
        // jadi hasilnya ditahan per request (scoped: ikut dibuang di queue worker).
        if (! app()->bound(self::DISABLED_KEY)) {
            app()->scoped(self::DISABLED_KEY, function (): array {
                $decoded = json_decode(Setting::get(self::DISABLED_KEY) ?? '[]', true);

                return is_array($decoded) ? array_values(array_filter($decoded, 'is_string')) : [];
            });
        }

        return app(self::DISABLED_KEY);
    }

    /**
     * @param  list<string>  $keys
     */
    public static function setDisabled(array $keys): void
    {
        $valid = array_values(array_filter(array_unique($keys), fn (string $key) => self::isKnown($key)));
        sort($valid);

        Setting::put(self::DISABLED_KEY, json_encode($valid));
        app()->forgetInstance(self::DISABLED_KEY);
    }

    public static function isKnown(string $key): bool
    {
        if (! str_contains($key, '.')) {
            return isset(self::MODULES[$key]);
        }

        [$module, $feature] = explode('.', $key, 2);

        return isset(self::MODULES[$module]['features'][$feature]);
    }
}
