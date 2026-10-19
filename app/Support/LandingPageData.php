<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Data provider & helper untuk halaman publik (Landing Page) dan CMS Pengaturan.
 * Mengelola seluruh konten dinamis mulai dari Hero, Mudir, Statistik, Pilar Keunggulan,
 * Program Pendidikan, Roadmap Tahfidz, Jadwal Keasramaan, Fasilitas, Prestasi, Testimoni, Agenda, FAQ, dan Kontak.
 */
class LandingPageData
{
    /**
     * Data Hero & Banner PPDB
     *
     * @return array<string, mixed>
     */
    public static function hero(): array
    {
        return [
            'badge' => Setting::get('landing_hero_badge', 'Penerimaan Santri Baru (PPDB) 2026/2027 Dibuka'),
            'title' => Setting::get('landing_hero_title', 'Mencetak Generasi Hafidz Qur\'an, Beradab Mulia & Unggul di Era Sains'),
            'subtitle' => Setting::get('landing_hero_subtitle', 'Pondok Pesantren Modern Terpadu yang mengintegrasikan kurikulum Tahfidzul Qur\'an 30 Juz Bersanad, pendalaman Kitab Turats Salaf, kecakapan Bahasa Arab & Inggris, serta penguasaan sains teknologi modern yang dipantau real-time oleh wali santri.'),
            'announcement' => Setting::get('landing_announcement_text', 'Penerimaan Santri Baru Gelombang 1 Dibuka · Dapatkan Keringanan Registrasi Awal.'),
            'cta_primary' => Setting::get('landing_hero_cta_primary', 'Daftar Calon Santri Sekarang'),
            'cta_secondary' => Setting::get('landing_hero_cta_secondary', 'Pelajari Program Unggulan'),
            'video_url' => Setting::get('landing_video_url', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'),
            'batch_name' => Setting::get('landing_batch_name', 'Gelombang 1 Reguler & Beasiswa Tahfidz'),
            'batch_quota' => Setting::get('landing_batch_quota', 'Tersisa 35 Kursi'),
            'batch_progress' => (int) Setting::get('landing_batch_progress', '72'),
            'quran_arabic' => Setting::get('landing_hero_quran_arabic', 'يَرْفَعِ اللَّهُ الَّذِينَ آمَنُوا مِنكُمْ وَالَّذِينَ أُوتُوا الْعِلْمَ دَرَجَاتٍ'),
            'quran_translation' => Setting::get('landing_hero_quran_translation', '"Niscaya Allah akan meninggikan orang-orang yang beriman di antaramu dan orang-orang yang diberi ilmu pengetahuan beberapa derajat."'),
            'quran_surah' => Setting::get('landing_hero_quran_surah', '(QS. Al-Mujadilah: 11)'),
            'hero_image' => Setting::get('landing_hero_image', 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=1200&q=80'),
            'hero_caption_title' => Setting::get('landing_hero_caption_title', 'Pondok Pesantren Modern Terpadu'),
            'hero_caption_sub' => Setting::get('landing_hero_caption_sub', 'Kombinasi Sempurna Tahfidz Al-Qur\'an, Adab Salaf & Sains Global'),
            'badge_1_label' => Setting::get('landing_hero_badge_1_label', 'Akreditasi BAN-SM'),
            'badge_1_value' => Setting::get('landing_hero_badge_1_value', 'Terakreditasi "A" Unggul'),
            'badge_2_label' => Setting::get('landing_hero_badge_2_label', 'Metode Syathibiyyah'),
            'badge_2_value' => Setting::get('landing_hero_badge_2_value', 'Sanad Tahfidz 30 Juz'),
        ];
    }

    /**
     * Profil & Pesan Mudirul Ma'had
     *
     * @return array<string, string>
     */
    public static function mudir(): array
    {
        return [
            'name' => Setting::get('landing_mudir_name', 'KH. Dr. Ahmad Abdullah, M.A.'),
            'title' => Setting::get('landing_mudir_title', 'Mudirul Ma\'had / Pengasuh Pondok Pesantren'),
            'credentials' => Setting::get('landing_mudir_credentials', 'Al-Azhar Kairo & LIPIA Jakarta · Sanad Tahfidz Syathibiyyah'),
            'quote' => Setting::get('landing_mudir_quote', 'Kami tidak hanya mendidik santri menghafal huruf demi huruf dalam Al-Qur\'an, tetapi menanamkan adab, kemuliaan karakter salaf, serta keberanian berpikir kritis agar santri menjadi rahmat bagi peradaban semesta.'),
            'headline' => Setting::get('landing_mudir_headline', 'Menyatukan Adab Qur\'ani, Tradisi Turats Salaf, dan Nalar Sains Modern'),
            'badge' => Setting::get('landing_mudir_badge', 'Sanad Keilmuan Bersambung (Mu\'tabar)'),
            'photo_url' => Setting::get('landing_mudir_photo_url', '/images/mudir-portrait.jpg'),
        ];
    }

    /**
     * 4 Statistik Utama
     *
     * @return array<int, array<string, string>>
     */
    public static function stats(): array
    {
        $custom = Setting::get('landing_stats_json');
        if ($custom && ($decoded = json_decode($custom, true)) && is_array($decoded)) {
            return $decoded;
        }

        return [
            [
                'number' => Setting::get('landing_stat_students', '1.250+'),
                'label' => 'Santri & Siswa Aktif',
                'sub' => 'Dari 28 Provinsi di Indonesia',
                'icon' => 'users',
            ],
            [
                'number' => Setting::get('landing_stat_tahfidz', '30 Juz'),
                'label' => 'Kurikulum Tahfidz Bersanad',
                'sub' => 'Mutqin & Ujian Bil Ghaib',
                'icon' => 'book-open',
            ],
            [
                'number' => Setting::get('landing_stat_teachers', '85+'),
                'label' => 'Dewan Asatidz & Guru Ahli',
                'sub' => 'Lulusan Al-Azhar, LIPIA & PTN',
                'icon' => 'award',
            ],
            [
                'number' => Setting::get('landing_stat_ptn', '100%'),
                'label' => 'Lulusan Lolos PTN / Luar Negeri',
                'sub' => 'Beasiswa Kemenag & Mandiri',
                'icon' => 'graduation-cap',
            ],
        ];
    }

    /**
     * 4 Pilar Keunggulan Utama
     *
     * @return array<int, array<string, mixed>>
     */
    public static function pillars(): array
    {
        $custom = Setting::get('landing_pillars_json');
        if ($custom && ($decoded = json_decode($custom, true)) && is_array($decoded)) {
            return $decoded;
        }

        return [
            [
                'title' => 'Tahfidz Bersanad & Mutqin 30 Juz',
                'badge' => 'Sanad Qur\'ani',
                'desc' => 'Metode talaqqi intensif dengan muhaffidz bersanad qira\'at Syathibiyyah hingga tuntas tasmi\' bil ghaib sekali duduk.',
                'icon' => 'book-open',
                'points' => ['Talaqqi Masyayikh Bersanad', 'Tasmi\' Terbuka Bil Ghaib', 'Karantina Tahfidz Intensif'],
            ],
            [
                'title' => 'Sains Terpadu & Riset Berwawasan Global',
                'badge' => 'Sains & Riset',
                'desc' => 'Penguasaan riset sains modern, matematika terapan, wawasan internasional, dan laboratorium komputasi terkini.',
                'icon' => 'microscope',
                'points' => ['Kurikulum Riset Terpadu', 'Olimpiade Sains & Robotika', 'Persiapan Masuk PTN Favorit'],
            ],
            [
                'title' => 'Lingkungan Bahasa Aktif 24 Jam (Arab & Inggris)',
                'badge' => 'Bahasa Aktif',
                'desc' => 'Praktik percakapan bahasa Arab fusha dan bahasa Inggris aktif setiap hari di lingkungan madrasah dan asrama.',
                'icon' => 'languages',
                'points' => ['Muhadtsah & Speech Club', 'Lingkungan Bahasa Asrama', 'Debat Bahasa Arab/Inggris'],
            ],
            [
                'title' => 'Adab Salaf & Karakter Mandiri',
                'badge' => 'Karakter Nabawi',
                'desc' => 'Pembinaan akhlak karimah, kepemimpinan Islam (qiyadah), kedisiplinan, kemandirian berwirausaha dan adab bermuamalah.',
                'icon' => 'award',
                'points' => ['Pembiasaan Shalat Berjamaah', 'Disiplin Sunnah Nabawiyyah', 'Leadership & Muamalah'],
            ],
        ];
    }

    /**
     * Program Jenjang Pendidikan
     *
     * @return array<int, array<string, mixed>>
     */
    public static function programs(): array
    {
        $custom = Setting::get('landing_programs_json');
        if ($custom && ($decoded = json_decode($custom, true)) && is_array($decoded)) {
            return $decoded;
        }

        return [
            [
                'id' => 'mts',
                'name' => 'MTs Unggulan Sains & Tahfidz',
                'badge' => 'Akreditasi A Unggul',
                'level' => 'Tingkat Menengah Pertama',
                'age' => 'Usia 12 - 15 Tahun (Lulusan SD/MI)',
                'desc' => 'Pendidikan tingkat menengah pertama yang mengintegrasikan kurikulum nasional, penguatan dasar tahfidz 15 juz, dan olimpiade sains terpadu.',
                'features' => [
                    'Target Tahfidz 15 Juz Al-Qur\'an',
                    'Laboratorium IPA & Robotika Dasar',
                    'Bilingual Arabic & English Foundation',
                    'Pembinaan Adab dan Ibadah Yaumiyyah',
                ],
                'quote' => 'Mempersiapkan santri usia awal dengan pondasi hafalan Al-Qur\'an yang kokoh dan nalar sains yang tajam.',
            ],
            [
                'id' => 'ma',
                'name' => 'Madrasah Aliyah (MA) Plus Riset',
                'badge' => 'Kemenag & Riset',
                'level' => 'Tingkat Menengah Atas',
                'age' => 'Usia 15 - 18 Tahun (Lulusan MTs/SMP)',
                'desc' => 'Jenjang menengah atas berfokus pada riset sains, penguasaan kitab turats lanjutan, dan persiapan intensif lolos PTN favorit serta kampus Timur Tengah.',
                'features' => [
                    'Target Tuntas Tahfidz 30 Juz Bersanad',
                    'Karya Tulis Ilmiah (KTI) & Riset Laboratorium',
                    'Bimbingan UTBK-SNBT & Tes Beasiswa Luar Negeri',
                    'Kajian Fiqih Muamalah & Ushul Fiqh',
                ],
                'quote' => 'Mencetak calon sarjana muslim, peneliti, dan pemimpin yang berwawasan luas serta berkarakter salaf.',
            ],
            [
                'id' => 'takhassus',
                'name' => 'Takhassus Tahfidzul Qur\'an 30 Juz',
                'badge' => 'Program Khusus Mutqin',
                'level' => 'Program Akselerasi Hifdzil Qur\'an',
                'age' => 'Santri Terpilih & Beasiswa',
                'desc' => 'Program intensif akselerasi menghafal Al-Qur\'an 30 juz mutqin dengan fokus penuh pada talaqqi, tajwid riwayah, dan tasmi\' akbar.',
                'features' => [
                    'Talaqqi Langsung Dewan Masyayikh Bersanad',
                    'Karantina Khusus & Lingkungan Asri yang Kondusif',
                    'Ujian Tasmi\' 5, 10, 20, dan 30 Juz Bil Ghaib',
                    'Pemberian Syahadah Sanad Syathibiyyah',
                ],
                'quote' => 'Jalur khusus bagi santri dengan tekad baja menghafalkan kalamullah secara mutqin dalam bimbingan para huffadz ahli.',
            ],
            [
                'id' => 'turats',
                'name' => 'Dirasah Islamiyyah & Kitab Turats',
                'badge' => 'Tradisi Salafush Shalih',
                'level' => 'Pendalaman Ulum Syar\'iyyah',
                'age' => 'Pendalaman Khusus Santri MA',
                'desc' => 'Kajian mendalam khazanah kitab kuning salaf (Nahwu, Shorof, Tafsir Ibnu Katsir, Fathul Qorib, Ihya\' Ulumiddin) dengan sanad keilmuan bersambung.',
                'features' => [
                    'Penguasaan Alfiyyah Ibnu Malik & Fathul Mu\'in',
                    'Metode Sorogan & Bandongan Interaktif',
                    'Sanad Kitab Bersambung ke Pengarang (Mu\'allif)',
                    'Kajian Ushul Fiqih & Fatwa Kontemporer',
                ],
                'quote' => 'Menjaga mata rantai keilmuan Islam klasik agar tetap relevan menjawab persoalan peradaban modern.',
            ],
        ];
    }

    /**
     * Roadmap 5 Tahap Tahfidz Mutqin
     *
     * @return array<int, array<string, string>>
     */
    public static function tahfidzRoadmap(): array
    {
        $custom = Setting::get('landing_tahfidz_json');
        if ($custom && ($decoded = json_decode($custom, true)) && is_array($decoded)) {
            return $decoded;
        }

        return [
            [
                'step' => '01',
                'title' => 'Tahsin & Makharijul Huruf',
                'desc' => 'Penyempurnaan pelafalan huruf hijaiyah, tajwid teoritis dan praktis sesuai kaidah jazariyyah.',
                'target' => 'Standarisasi Bacaan',
            ],
            [
                'step' => '02',
                'title' => 'Ziyadah (Penambahan Hafalan)',
                'desc' => 'Penyetoran hafalan baru setiap ba\'da Subuh dan Ashar dengan sistem talaqqi langsung ke ustadz pembimbing.',
                'target' => '1 - 2 Kaca per Hari',
            ],
            [
                'step' => '03',
                'title' => 'Sabaq & Sabqi (Muraja\'ah Harian)',
                'desc' => 'Pengulangan hafalan baru dan hafalan lama secara rutin agar tidak mudah lupa dan saling menguatkan.',
                'target' => 'Minimal 1/2 Juz per Hari',
            ],
            [
                'step' => '04',
                'title' => 'Tasmi\' Bil Ghaib Terbuka',
                'desc' => 'Ujian memperdengarkan hafalan Al-Qur\'an tanpa melihat mushaf sekali duduk di hadapan dewan penguji dan santri.',
                'target' => '5, 10, 20 & 30 Juz',
            ],
            [
                'step' => '05',
                'title' => 'Khotmil Qur\'an & Sanad Riwayah',
                'desc' => 'Pengambilan sanad qira\'at Hafsh \'an \'Ashim dari masyayikh yang tersambung sampai ke Baginda Rasulullah SAW.',
                'target' => 'Syahadah Sanad Resmi',
            ],
        ];
    }

    /**
     * Jadwal Keseharian Keasramaan 24 Jam
     *
     * @return array<int, array<string, string>>
     */
    public static function dailySchedule(): array
    {
        $custom = Setting::get('landing_schedule_json');
        if ($custom && ($decoded = json_decode($custom, true)) && is_array($decoded)) {
            return $decoded;
        }

        return [
            [
                'period' => 'Dini Hari & Subuh',
                'time' => '03.30 - 06.00 WIB',
                'title' => 'Spiritual Awakening & Tahfidz Fajar',
                'desc' => 'Bangun qiyamul lail, shalat tahajjud berjamaah, munajat, subuh berjamaah, wirid ma\'tsurat, dan halaqah setoran hafalan baru (ziyadah) saat pikiran paling jernih.',
            ],
            [
                'period' => 'Pagi & Siang',
                'time' => '06.30 - 15.00 WIB',
                'title' => 'Madrasah Formal, Sains & Riset',
                'desc' => 'Sarapan pagi bersama bergizi seimbang, apel pagi berbahasa asing, pembelajaran kurikulum terpadu di kelas interaktif, praktikum laboratorium IPA dan komputer.',
            ],
            [
                'period' => 'Sore & Keasramaan',
                'time' => '15.30 - 17.30 WIB',
                'title' => 'Olahraga Sunnah, Bahasa & Minat Bakat',
                'desc' => 'Shalat Ashar berjamaah, pengembangan bahasa asing (Arabic & English Club), olahraga sunnah (panahan, futsal, renang, bela diri Tapak Suci), istirahat dan mandi sore.',
            ],
            [
                'period' => 'Malam & Istirahat',
                'time' => '18.00 - 21.30 WIB',
                'title' => 'Maghrib, Turats & Muthola\'ah Terbimbing',
                'desc' => 'Shalat Maghrib berjamaah, halaqah muraja\'ah akbar, kajian kitab kuning bersama pengasuh, makan malam asrama, belajar malam terbimbing (muthola\'ah), dan tidur teratur pukul 22.00.',
            ],
        ];
    }

    /**
     * Fasilitas Kampus Unggulan
     *
     * @return array<int, array<string, string>>
     */
    public static function facilities(): array
    {
        $custom = Setting::get('landing_facilities_json');
        if ($custom && ($decoded = json_decode($custom, true)) && is_array($decoded)) {
            return $decoded;
        }

        return [
            [
                'name' => 'Masjid Jami\' Ar-Rahman',
                'category' => 'Pusat Ibadah & Tahfidz',
                'desc' => 'Masjid dua lantai berkapasitas 2.000 jamaah dengan tata suara akustik modern dan sejuk untuk halaqah Al-Qur\'an.',
                'image' => 'https://images.unsplash.com/photo-1584551246679-0daf3d275d0f?auto=format&fit=crop&w=800&q=80',
                'tag' => 'Kapasitas 2.000 Jamaah',
            ],
            [
                'name' => 'Laboratorium Sains & Robotika',
                'category' => 'Akademik & Riset',
                'desc' => 'Fasilitas praktikum fisika, kimia, biologi, serta lab komputer berbasis AI dan perlengkapan robotika kompetisi.',
                'image' => 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?auto=format&fit=crop&w=800&q=80',
                'tag' => 'Alat Riset Mutakhir',
            ],
            [
                'name' => 'Asrama Nyaman & Ber-AC',
                'category' => 'Hunian Santri',
                'desc' => 'Kamar tidur ergonomis dilengkapi pendingin udara (AC), loker pribadi, kamar mandi bersih di setiap lantai, dan pendamping musyrif.',
                'image' => 'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?auto=format&fit=crop&w=800&q=80',
                'tag' => 'Musyrif 24 Jam',
            ],
            [
                'name' => 'Gelanggang Olahraga & Lapangan Terpadu',
                'category' => 'Kebugaran & Olahraga',
                'desc' => 'Lapangan futsal standar, basket, voli, arena panahan sunnah, dan aula serbaguna untuk olahraga indoor santri.',
                'image' => 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=800&q=80',
                'tag' => 'Fasilitas Standar Nasional',
            ],
            [
                'name' => 'Maktabah Digital & Perpustakaan Turats',
                'category' => 'Literasi & Khazanah',
                'desc' => 'Ribuan koleksi kitab kuning salaf, ensiklopedia Islam, buku sains modern, dan akses perpustakaan digital Maktabah Syamilah.',
                'image' => 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=800&q=80',
                'tag' => 'Ribuan Judul Kitab',
            ],
            [
                'name' => 'Kantin Sehat & Transaksi Cashless RFID',
                'category' => 'Layanan Santri',
                'desc' => 'Penyediaan makanan higienis 3x sehari bersertifikat halal dengan sistem pembayaran kartu santri pintar RFID tanpa uang tunai fisik.',
                'image' => 'https://images.unsplash.com/photo-1567521464027-f127ff144326?auto=format&fit=crop&w=800&q=80',
                'tag' => 'Menu Higienis & Halal',
            ],
            [
                'name' => 'Poskestren & Layanan Medis 24 Jam',
                'category' => 'Kesehatan Santri',
                'desc' => 'Klinik kesehatan santri dengan dokter dan perawat jaga, ruang isolasi medis, serta fasilitas penanganan gawat darurat pertama.',
                'image' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=800&q=80',
                'tag' => 'Tenaga Medis Siaga',
            ],
            [
                'name' => 'Studio Kaligrafi & Multimedia Santri',
                'category' => 'Kreativitas & Seni',
                'desc' => 'Ruang kreatif untuk seni kaligrafi Islam (khat), podcast islami, desain grafis dakwah, dan produksi media kreatif santri.',
                'image' => 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?auto=format&fit=crop&w=800&q=80',
                'tag' => 'Eksplorasi Bakat Seni',
            ],
        ];
    }

    /**
     * Prestasi & Kejuaraan Santri
     *
     * @return array<int, array<string, string>>
     */
    public static function achievements(): array
    {
        $custom = Setting::get('landing_achievements_json');
        if ($custom && ($decoded = json_decode($custom, true)) && is_array($decoded)) {
            return $decoded;
        }

        return [
            [
                'year' => '2026',
                'title' => 'Medali Emas Olimpiade Sains Nasional (OSN)',
                'event' => 'Bidang Astronomi & Fisika Terapan',
                'student' => 'Ahmad Zaki Fauzan (Santri MA Plus)',
                'category' => 'Sains Nasional',
            ],
            [
                'year' => '2026',
                'title' => 'Juara 1 Musabaqah Hifdzil Qur\'an (MHQ) 30 Juz',
                'event' => 'Tingkat Provinsi & Nasional Kemenag RI',
                'student' => 'Muhammad Faris Al-Hafidz',
                'category' => 'Tahfidz Al-Qur\'an',
            ],
            [
                'year' => '2025',
                'title' => 'Juara 1 International Islamic Robotic Olympiad',
                'event' => 'Kategori Autonomous Maze Solving Malaysia',
                'student' => 'Tim Robotika Santri MTs',
                'category' => 'Robotika Internasional',
            ],
            [
                'year' => '2025',
                'title' => '100% Lulusan Diterima di PTN & Al-Azhar Kairo',
                'event' => 'SNBP, SNBT, dan Beasiswa Al-Azhar Mesir',
                'student' => 'Seluruh Santri Angkatan VII',
                'category' => 'Kelulusan PTN & Luar Negeri',
            ],
            [
                'year' => '2025',
                'title' => 'Juara Umum Lomba Debat Bahasa Arab (Munazarah)',
                'event' => 'Festival Bahasa Arab Tingkat Nasional',
                'student' => 'Delegasi Qismul Lughah Santri',
                'category' => 'Bahasa Arab',
            ],
            [
                'year' => '2024',
                'title' => 'Penghargaan Sekolah Adiwiyata Berwawasan Lingkungan',
                'event' => 'Kementerian Lingkungan Hidup & Kehutanan',
                'student' => 'Civitas Akademika Pondok',
                'category' => 'Kampus Hijau',
            ],
        ];
    }

    /**
     * Testimoni Wali Santri & Alumni
     *
     * @return array<int, array<string, mixed>>
     */
    public static function testimonials(): array
    {
        $custom = Setting::get('landing_testimonials_json');
        if ($custom && ($decoded = json_decode($custom, true)) && is_array($decoded)) {
            return $decoded;
        }

        return [
            [
                'name' => 'Ir. H. Hendra Gunawan, M.T.',
                'role' => 'Wali Santri MA Plus Riset',
                'child' => 'Ayahanda dari M. Zaki (Hafidz 30 Juz & Juara OSN)',
                'quote' => 'Alhamdulillah, perpaduan antara pembinaan tahfidz yang mutqin dan dukungan riset sains di sini luar biasa. Putra kami tidak hanya hafal 30 juz Al-Qur\'an, tapi juga percaya diri menjuarai kompetisi sains tingkat nasional. Sistem pemantauan wali santri online sangat transparan.',
                'rating' => 5,
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=200&q=80',
            ],
            [
                'name' => 'dr. Hj. Siti Nurjanah, Sp.A.',
                'role' => 'Wali Santri MTs Sains',
                'child' => 'Ibunda dari Aisyah Humaira (Kelas 8 MTs)',
                'quote' => 'Sebagai dokter, saya sangat memperhatikan gizi, kebersihan asrama, dan kesehatan santri. Pondok ini memiliki sanitasi yang sangat bersih, poskestren siaga 24 jam, dan makanan bergizi seimbang. Anak saya menjadi sangat mandiri, beradab, dan tilawahnya fasih.',
                'rating' => 5,
                'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=200&q=80',
            ],
            [
                'name' => 'Ust. H. Fauzi Ridwan, Lc.',
                'role' => 'Alumni & Mahasiswa Al-Azhar Kairo',
                'child' => 'Alumni Angkatan IV (Fakultas Syari\'ah Islamiyyah)',
                'quote' => 'Pondok ini memberikan pondasi bahasa Arab aktif dan kitab turats yang sangat kuat. Ketika saya mengikuti ujian masuk Universitas Al-Azhar Kairo, saya lulus tanpa kendala bahasa. Terima kasih kepada seluruh asatidz yang telah mengikhlaskan ilmunya.',
                'rating' => 5,
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=200&q=80',
            ],
            [
                'name' => 'Drs. H. Bambang Sutrisno, M.Pd.',
                'role' => 'Wali Santri Takhassus 30 Juz',
                'child' => 'Ayahanda dari Farhan Abdullah (Lulus Tasmi\' Sekali Duduk)',
                'quote' => 'Melihat anak saya berhasil tasmi\' 30 juz bil ghaib di hadapan ratusan jamaah adalah momen paling membahagiakan dalam hidup saya sebagai orang tua. Bimbingan para muhaffidz sangat telaten dan penuh keteladanan akhlak.',
                'rating' => 5,
                'avatar' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=200&q=80',
            ],
        ];
    }

    /**
     * Agenda Terkini
     *
     * @return array<int, array<string, string>>
     */
    public static function agenda(): array
    {
        $custom = Setting::get('landing_agenda_json');
        if ($custom && ($decoded = json_decode($custom, true)) && is_array($decoded)) {
            return $decoded;
        }

        return [
            [
                'title' => Setting::get('landing_event_1_title', 'Ujian Observasi & Wawancara PPDB Gelombang 1'),
                'date' => Setting::get('landing_event_1_date', '15 November 2026'),
                'location' => 'Kampus Utama & Online via Zoom',
                'desc' => Setting::get('landing_event_1_desc', 'Tes pemetaan baca Al-Qur\'an, wawancara kepribadian calon santri serta keselarasan visi pendidikan bersama wali santri.'),
                'badge' => 'PPDB 2026',
            ],
            [
                'title' => Setting::get('landing_event_2_title', 'Tasmi\' Akbar 30 Juz Bil Ghaib Sekali Duduk'),
                'date' => Setting::get('landing_event_2_date', '28 Desember 2026'),
                'location' => 'Masjid Jami\' Ar-Rahman',
                'desc' => Setting::get('landing_event_2_desc', 'Ujian terbuka hafalan Al-Qur\'an 30 juz sekali duduk tanpa jeda di hadapan dewan asatidz, masyayikh dan tamu kehormatan.'),
                'badge' => 'Halaqah Tahfidz',
            ],
            [
                'title' => Setting::get('landing_event_3_title', 'Haflah Akhirussanah & Wisuda Khotmil Qur\'an'),
                'date' => Setting::get('landing_event_3_date', '20 Mei 2027'),
                'location' => 'Grand Ballroom Kampus Utama',
                'desc' => Setting::get('landing_event_3_desc', 'Prosesi wisuda kelulusan santri jenjang MTs & MA, penganugerahan syahadah sanad tahfidz Syathibiyyah, dan pelepasan santri ke jenjang perguruan tinggi.'),
                'badge' => 'Wisuda Akbar',
            ],
        ];
    }

    /**
     * FAQ (Tanya Jawab)
     *
     * @return array<int, array<string, string>>
     */
    public static function faqs(): array
    {
        $custom = Setting::get('landing_faqs_json');
        if ($custom && ($decoded = json_decode($custom, true)) && is_array($decoded)) {
            return $decoded;
        }

        return [
            [
                'q' => 'Bagaimana alur pendaftaran santri baru (PPDB)?',
                'a' => 'Pendaftaran dapat dilakukan secara online melalui website ini dengan mengisi formulir biodata santri dan wali, mengunggah berkas administrasi (kartu keluarga, akta lahir, rapor), serta memilih jalur pendaftaran (Reguler, Prestasi Sains, atau Beasiswa Tahfidz). Setelah itu calon santri akan dijadwalkan mengikuti tes observasi.',
                'category' => 'PPDB',
            ],
            [
                'q' => 'Apakah santri yang belum hafal Al-Qur\'an sama sekali bisa mendaftar?',
                'a' => 'Bisa! Untuk jenjang MTs Reguler, syarat utama adalah sudah mampu membaca Al-Qur\'an dengan baik dan benar. Di awal semester, santri akan mengikuti program matrikulasi tahsin makharijul huruf sebelum masuk ke halaqah ziyadah hafalan baru. Untuk program Beasiswa Tahfidz, minimal memiliki hafalan 5-10 juz mutqin saat tes.',
                'category' => 'Tahfidz',
            ],
            [
                'q' => 'Bagaimana sistem pembiasaan bahasa asing (Arab & Inggris) di asrama?',
                'a' => 'Pesantren menerapkan pembiasaan bahasa Arab fusha dan bahasa Inggris secara aktif dan bertahap. Tiga bulan pertama santri dibekali kosakata harian (mufradat). Setelah itu, santri dibiasakan menggunakan bahasa Arab fusha dan bahasa Inggris secara bergantian di lingkungan madrasah dan asrama dengan bimbingan bagian bahasa (Qismul Lughah).',
                'category' => 'Kurikulum',
            ],
            [
                'q' => 'Apakah wali santri bisa memantau perkembangan anak secara berkala?',
                'a' => 'Sangat bisa. Pesantren menyediakan Portal Mandiri Wali Santri dan aplikasi mobile. Wali santri dapat memantau capaian hafalan Al-Qur\'an harian (e-Tahfidz), absensi shalat berjamaah, nilai rapor akademik, catatan konseling kedisiplinan, hingga saldo uang jajan digital santri secara real-time dari mana saja.',
                'category' => 'Layanan',
            ],
            [
                'q' => 'Kapan waktu jenguk (kunjungan wali) dan perizinan pulang santri?',
                'a' => 'Waktu kunjungan wali santri dibuka pada pekan kedua dan keempat setiap bulan (hari Ahad pukul 08.00 - 17.00 WIB). Perizinan pulang santri terjadwal saat libur semester ganjil dan genap, serta hari raya Idul Fitri. Perizinan darurat dapat diajukan melalui portal wali santri secara digital.',
                'category' => 'Asrama',
            ],
            [
                'q' => 'Apakah tersedia beasiswa bagi santri berprestasi atau dhuafa?',
                'a' => 'Ya, pesantren menyediakan Beasiswa Penuh (100% bebas biaya masuk & SPP) untuk santri hafidz 30 juz mutqin, serta beasiswa prestasi sains/olimpiade dan beasiswa kader ummat bagi keluarga yatim/dhuafa berprestasi yang lolos seleksi khusus dewan mudir.',
                'category' => 'Biaya',
            ],
        ];
    }

    /**
     * Kontak, Lokasi & Sosial Media
     *
     * @return array<string, string>
     */
    public static function contact(): array
    {
        $phone = Setting::get('landing_whatsapp_hotline') ?: (Setting::get('company_phone') ?: '0812-3456-7890');

        return [
            'phone' => $phone,
            'whatsapp' => Setting::get('landing_whatsapp_hotline', $phone),
            'clean_phone' => preg_replace('/[^0-9]/', '', $phone),
            'email' => Setting::get('company_email', 'info@pesantren-islamic.sch.id'),
            'address' => Setting::get('company_address', 'Jl. Pesantren Luhur No. 99, Cibubur, Jakarta Timur 13720'),
            'office_hours' => Setting::get('landing_office_hours', 'Senin - Sabtu (07.30 - 16.00 WIB)'),
            'maps_embed' => Setting::get('landing_maps_embed', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126915.22851624683!2d106.8407421!3d-6.3159989!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69ed1e8f3a3c21%3A0x6b30f7b07047f631!2sJakarta!5e0!3m2!1sen!2sid!4v1700000000000!5m2!1sen!2sid'),
            'instagram' => Setting::get('landing_instagram_url', 'https://instagram.com'),
            'youtube' => Setting::get('landing_youtube_url', 'https://youtube.com'),
            'facebook' => Setting::get('landing_facebook_url', 'https://facebook.com'),
            'tiktok' => Setting::get('landing_tiktok_url', 'https://tiktok.com'),
        ];
    }

    /**
     * 3 Jalur Masuk Seleksi & Beasiswa PPDB
     *
     * @return array<int, array<string, mixed>>
     */
    public static function tracks(): array
    {
        $custom = Setting::get('landing_tracks_json');
        if ($custom && ($decoded = json_decode($custom, true)) && is_array($decoded)) {
            return $decoded;
        }

        return [
            [
                'id' => 'tahfidz',
                'name' => 'Jalur Beasiswa Tahfidz',
                'badge' => 'Tahfidz Mutqin',
                'highlight' => 'Beasiswa Penuh 100%',
                'quota' => 'Kuota: 15 Santri',
                'icon' => 'book-open',
                'color' => 'emerald',
                'desc' => 'Bebas uang pangkal & SPP bulanan bagi calon santri yang telah memiliki hafalan Al-Qur\'an mutqin 10 - 30 Juz dan lulus tes tasmi\'.',
                'benefits' => [
                    'Bebas Biaya SPP & Asrama',
                    'Pembinaan Sanad Syathibiyyah',
                    'Kuota Terbatas 15 Santri',
                ],
                'cta_text' => 'Pilih Jalur Tahfidz',
                'featured' => true,
            ],
            [
                'id' => 'prestasi',
                'name' => 'Jalur Prestasi Sains & OSN',
                'badge' => 'Olimpiade & Riset',
                'highlight' => 'Keringanan Biaya Masuk 50%',
                'quota' => 'Kuota: 25 Santri',
                'icon' => 'microscope',
                'color' => 'amber',
                'desc' => 'Khusus calon santri peraih medali OSN, KSM, Robotika, atau olimpiade sains minimal tingkat kota/kabupaten.',
                'benefits' => [
                    'Keringanan Biaya Pembangunan 50%',
                    'Masuk Tim Riset Olimpiade',
                    'Bimbingan Dosen Ahli PTN',
                ],
                'cta_text' => 'Pilih Jalur Prestasi',
                'featured' => false,
            ],
            [
                'id' => 'reguler',
                'name' => 'Jalur Reguler Berasrama',
                'badge' => 'Reguler',
                'highlight' => 'Kemudahan Pembayaran Bertahap',
                'quota' => 'Kuota: 60 Santri',
                'icon' => 'users',
                'color' => 'teal',
                'desc' => 'Terbuka bagi seluruh calon santri dengan tes pemetaan bacaan Al-Qur\'an dasar dan wawancara kepribadian santri/wali.',
                'benefits' => [
                    'Matrikulasi Tahsin Pemula',
                    'Pilihan Pembayaran Bertahap',
                    'Pemantauan Aplikasi Mobile',
                ],
                'cta_text' => 'Pilih Jalur Reguler',
                'featured' => false,
            ],
        ];
    }

    /**
     * Closing CTA Banner Section
     *
     * @return array<string, string>
     */
    public static function cta(): array
    {
        return [
            'badge' => Setting::get('landing_cta_badge', 'Penerimaan Santri Baru Tahun Ajaran 2026/2027'),
            'title' => Setting::get('landing_cta_title', 'Bina Generasi Qur\'ani, Cendekia, dan Berakhlak Mulia Bersama Kami'),
            'desc' => Setting::get('landing_cta_desc', 'Daftarkan ananda sebelum kuota gelombang pertama terpenuhi. Konsultasikan jalur beasiswa tahfidz atau rencana pendidikan ananda bersama panitia penerimaan santri baru.'),
            'primary_text' => Setting::get('landing_cta_btn_primary', 'Daftar Calon Santri Sekarang'),
            'whatsapp_text' => Setting::get('landing_cta_btn_whatsapp', 'Konsultasi Panitia via WhatsApp'),
        ];
    }

    /**
     * Judul & Subjudul Tiap Seksi Landing Page
     *
     * @return array<string, array<string, string>>
     */
    public static function sectionHeadings(): array
    {
        $custom = Setting::get('landing_section_headings_json');
        if ($custom && ($decoded = json_decode($custom, true)) && is_array($decoded)) {
            foreach ($decoded as $k => $item) {
                if (! isset($decoded[$k]['sub']) && isset($item['desc'])) {
                    $decoded[$k]['sub'] = $item['desc'];
                }
                if (! isset($decoded[$k]['desc']) && isset($item['sub'])) {
                    $decoded[$k]['desc'] = $item['sub'];
                }
            }

            return $decoded;
        }

        return [
            'pillars' => [
                'badge' => Setting::get('landing_sec_pillars_badge', 'Pilar Pendidikan Pesantren'),
                'title' => Setting::get('landing_sec_pillars_title', 'Empat Pilar Pembinaan Generasi Qur\'ani Berprestasi'),
                'sub' => Setting::get('landing_sec_pillars_desc', 'Kurikulum terpadu yang memadukan kedalaman ilmu syar\'i, keluhuran adab, penguasaan bahasa, dan ketajaman nalar sains modern.'),
                'desc' => Setting::get('landing_sec_pillars_desc', 'Kurikulum terpadu yang memadukan kedalaman ilmu syar\'i, keluhuran adab, penguasaan bahasa, dan ketajaman nalar sains modern.'),
            ],
            'programs' => [
                'badge' => Setting::get('landing_sec_programs_badge', 'Jenjang & Kurikulum Pendidikan'),
                'title' => Setting::get('landing_sec_programs_title', 'Jenjang Pendidikan Berkelanjutan Sesuai Tahapan Usia Santri'),
                'sub' => Setting::get('landing_sec_programs_desc', 'Kurikulum terpadu berkesinambungan yang membekali santri dari jenjang dasar hingga siap melanjutkan studi ke perguruan tinggi unggulan dunia.'),
                'desc' => Setting::get('landing_sec_programs_desc', 'Kurikulum terpadu berkesinambungan yang membekali santri dari jenjang dasar hingga siap melanjutkan studi ke perguruan tinggi unggulan dunia.'),
            ],
            'tracks' => [
                'badge' => Setting::get('landing_sec_tracks_badge', 'Seleksi Santri Baru'),
                'title' => Setting::get('landing_sec_tracks_title', 'Pilih Jalur Pendaftaran Sesuai Potensi Ananda'),
                'sub' => Setting::get('landing_sec_tracks_desc', 'Kami menyediakan berbagai jalur masuk dengan skema beasiswa penuh maupun apresiasi prestasi.'),
                'desc' => Setting::get('landing_sec_tracks_desc', 'Kami menyediakan berbagai jalur masuk dengan skema beasiswa penuh maupun apresiasi prestasi.'),
            ],
            'tahfidz' => [
                'badge' => Setting::get('landing_sec_tahfidz_badge', 'Tahapan Hifdzil Qur\'an'),
                'title' => Setting::get('landing_sec_tahfidz_title', '5 Tahapan Terstruktur Menuju Tahfidz Mutqin Bersanad'),
                'sub' => Setting::get('landing_sec_tahfidz_desc', 'Metode berjenjang yang terbukti membimbing santri meraih hafalan 30 juz mutqin dengan bacaan yang fasih, tartil, dan hafalan yang melekat kuat seumur hidup.'),
                'desc' => Setting::get('landing_sec_tahfidz_desc', 'Metode berjenjang yang terbukti membimbing santri meraih hafalan 30 juz mutqin dengan bacaan yang fasih, tartil, dan hafalan yang melekat kuat seumur hidup.'),
            ],
            'schedule' => [
                'badge' => Setting::get('landing_sec_schedule_badge', 'Kehidupan Asrama'),
                'title' => Setting::get('landing_sec_schedule_title', '24 Jam Ritme Kehidupan Asrama: Menanamkan Disiplin, Ibadah & Kemandirian'),
                'sub' => Setting::get('landing_sec_schedule_desc', 'Keseimbangan ritme antara ibadah fardhu & sunnah, pembelajaran madrasah, pendalaman turats, riset sains, olahraga, serta istirahat yang teratur.'),
                'desc' => Setting::get('landing_sec_schedule_desc', 'Keseimbangan ritme antara ibadah fardhu & sunnah, pembelajaran madrasah, pendalaman turats, riset sains, olahraga, serta istirahat yang teratur.'),
            ],
            'facilities' => [
                'badge' => Setting::get('landing_sec_facilities_badge', 'Sarana & Prasarana'),
                'title' => Setting::get('landing_sec_facilities_title', 'Fasilitas Lengkap Penunjang Ibadah, Pembelajaran & Asrama'),
                'sub' => Setting::get('landing_sec_facilities_desc', 'Kawasan asri, bersih, dan representatif yang dirancang untuk kekhusyukan ibadah, kenyamanan menghafal Al-Qur\'an, dan eksplorasi sains modern.'),
                'desc' => Setting::get('landing_sec_facilities_desc', 'Kawasan asri, bersih, dan representatif yang dirancang untuk kekhusyukan ibadah, kenyamanan menghafal Al-Qur\'an, dan eksplorasi sains modern.'),
            ],
            'achievements' => [
                'badge' => Setting::get('landing_sec_achievements_badge', 'Prestasi Santri'),
                'title' => Setting::get('landing_sec_achievements_title', 'Capaian Prestasi Santri Tingkat Nasional & Internasional'),
                'sub' => Setting::get('landing_sec_achievements_desc', 'Bukti nyata ikhtiar dan kesungguhan pembinaan terpadu di bidang hifdzil Qur\'an, sains, teknologi robotika, dan bahasa asing.'),
                'desc' => Setting::get('landing_sec_achievements_desc', 'Bukti nyata ikhtiar dan kesungguhan pembinaan terpadu di bidang hifdzil Qur\'an, sains, teknologi robotika, dan bahasa asing.'),
            ],
            'testimonials' => [
                'badge' => Setting::get('landing_sec_testimonials_badge', 'Kesan & Amanah Wali'),
                'title' => Setting::get('landing_sec_testimonials_title', 'Kesan & Pengalaman Para Wali Santri serta Alumni'),
                'sub' => Setting::get('landing_sec_testimonials_desc', 'Kepercayaan dan amanah ratusan keluarga muslim yang mengikhtiarkan pendidikan putra-putrinya di ma\'had kami.'),
                'desc' => Setting::get('landing_sec_testimonials_desc', 'Kepercayaan dan amanah ratusan keluarga muslim yang mengikhtiarkan pendidikan putra-putrinya di ma\'had kami.'),
            ],
            'agenda' => [
                'badge' => Setting::get('landing_sec_agenda_badge', 'Agenda & Syiar Ma\'had'),
                'title' => Setting::get('landing_sec_agenda_title', 'Agenda Kegiatan & Acara Terdekat'),
                'sub' => '',
                'desc' => '',
            ],
            'faq' => [
                'badge' => Setting::get('landing_sec_faq_badge', 'Pertanyaan Umum'),
                'title' => Setting::get('landing_sec_faq_title', 'Pertanyaan yang Sering Diajukan Seputar Pesantren & PPDB'),
                'sub' => Setting::get('landing_sec_faq_desc', 'Informasi seputar pendaftaran santri baru, kurikulum pesantren, kehidupan asrama, dan pembiayaan pendidikan.'),
                'desc' => Setting::get('landing_sec_faq_desc', 'Informasi seputar pendaftaran santri baru, kurikulum pesantren, kehidupan asrama, dan pembiayaan pendidikan.'),
            ],
        ];
    }

    /**
     * Helper untuk mengubah URL video menjadi embed URL YouTube
     */
    public static function youtubeEmbedUrl(?string $url): string
    {
        if (empty($url)) {
            return 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?autoplay=0';
        }

        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return 'https://www.youtube-nocookie.com/embed/'.$matches[1].'?autoplay=0';
        }

        return 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?autoplay=0';
    }
}
