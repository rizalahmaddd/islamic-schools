# Instalasi & Konfigurasi

Panduan lengkap instalasi, kebutuhan sistem, konfigurasi variabel lingkungan, akun demo, dan perintah pengembangan untuk **SIM Pesantren & Sekolah Islam**.

---

## Kebutuhan Sistem

- **PHP**: >= 8.3 (direkomendasikan PHP 8.4 atau 8.5)
  - Ekstensi yang dibutuhkan: `pdo`, `pdo_sqlite` (atau `pdo_mysql`), `mbstring`, `openssl`, `gd`, `zip`, `bcmath`, `intl`
- **Composer**: >= 2.x
- **Node.js**: >= 20.x dan npm
- **Database**: SQLite (default lokal) atau MySQL 8.0+ / MariaDB 10.4+
- **Queue Worker**: `php artisan queue:work` untuk pengiriman pesan WhatsApp dan notifikasi
- **Scheduler**: Cron job `php artisan schedule:run` tiap menit untuk pencadangan otomatis dan reset limit belanja harian

---

## Mulai Cepat (Local Development)

### 1. Clone & Setup Otomatis

```bash
git clone https://github.com/rizalahmaddd/islamic-schools.git
cd islamic-schools

# Satu perintah untuk semua dependensi, .env, database lokal, dan aset:
composer run setup
```

Perintah `composer run setup` secara otomatis menjalankan:
1. `composer install`
2. Pembuatan file `.env` dari `.env.example`
3. Generate `APP_KEY`
4. Pembuatan database SQLite lokal (`database/database.sqlite`)
5. Migrasi struktur database & inisialisasi permission, role, setting default, dan master 114 surah Al-Qur'an
6. Pembuatan akun Superadmin pertama
7. `npm install` dan `npm run build`

### 2. Menjalankan Server Development

```bash
composer run dev
```

Perintah di atas akan menjalankan secara serentak:
- Server web Laravel (`http://localhost:8000`)
- Queue worker untuk memproses antrean pesan & notifikasi
- Scheduler runner
- Vite dev server dengan HMR
- Laravel Reverb websocket server untuk fitur realtime

---

## Akun Demo untuk Pengujian

Jika Anda ingin mencoba aplikasi dengan data sampel santri, guru, transaksi kasir, kamar asrama, dan hafalan tahfidz, jalankan:

```bash
php artisan migrate:fresh --seed
```

Seluruh akun demo menggunakan kata sandi default: **`password`**

| Peran | Alamat Email | Username | Akses Utama |
|---|---|---|---|
| **Superadmin** | `superadmin@example.test` | `superadmin` | Akses penuh seluruh modul & konfigurasi sistem |
| **Kepala Sekolah / Mudir** | `kepalasekolah@example.test` | `kepalasekolah` | Monitoring eksekutif, akademik, e-rapor |
| **Tata Usaha** | `tu@example.test` | `tu` | Master data santri & guru, rombel, PPDB |
| **Bendahara** | `bendahara@example.test` | `bendahara` | Kasir POS SPP, master tarif tagihan, buku kas |
| **Guru / Asatidz** | `guru@example.test` | `guru` | Presensi rombel, jurnal mengajar, buku nilai |
| **Wali Kelas** | `walikelas@example.test` | `walikelas` | Rekapitulasi absensi kelas, cetak e-rapor |
| **Musyrif Asrama** | `musyrif@example.test` | `musyrif` | Kamar santri, setoran tahfidz, izin asrama |
| **Satpam** | `satpam@example.test` | `satpam` | Scanner QR gate pass satpam gerbang |
| **Wali Murid** | `walimurid@example.test` | `walimurid` | Portal mandiri wali santri (PWA) |
| **Santri** | `santri@example.test` | `santri` | Smart card digital & info saku |

> [!WARNING]
> Jangan jalankan seeder demo (`--seed`) di lingkungan produksi. Untuk instalasi produksi baru, gunakan `php artisan app:install`.

---

## Konfigurasi Layanan Eksternal (`.env`)

### 1. WhatsApp Gateway (Fonnte)

Digunakan untuk pengiriman OTP bebas sandi wali santri dan notifikasi absensi kehadiran santri otomatis ke WhatsApp orang tua:

```env
FONNTE_TOKEN=your_fonnte_token_here
FONNTE_URL=https://api.fonnte.com/send
```

### 2. Payment Gateway Online (Tripay)

Digunakan untuk pembayaran tagihan SPP online via Virtual Account (BCA, Mandiri, BRI, BNI), QRIS, dan retail (Indomaret/Alfamart):

```env
TRIPAY_API_KEY=your_tripay_api_key
TRIPAY_PRIVATE_KEY=your_tripay_private_key
TRIPAY_MERCHANT_CODE=your_merchant_code
TRIPAY_MODE=sandbox # atau 'production'
```

Atur URL Webhook di dashboard merchant Tripay ke:
```
https://domain-anda.com/api/v1/payments/tripay/webhook
```

### 3. Geofencing GPS Presensi Guru

Titik koordinat gerbang sekolah dan radius toleransi check-in guru dapat diatur melalui dashboard **Pengaturan Profil Perusahaan** atau variabel database:

- `school_lat`: Titik garis lintang latitude sekolah (contoh: `-7.982600`)
- `school_lng`: Titik garis bujur longitude sekolah (contoh: `112.630800`)
- `school_radius`: Radius toleransi dalam satuan meter (contoh: `100`)

---

## Perintah Pengembangan & Testing

```bash
# Menjalankan seluruh test suite (Pest)
php artisan test --compact

# Menjalankan test modul spesifik
php artisan test --filter=GuardianPortalTest
php artisan test --filter=IslamicCoreTest
php artisan test --filter=FinanceCashierTest

# Analisis statis PHP (Larastan)
vendor/bin/phpstan analyse --no-progress --memory-limit=1G

# Memformat kode sesuai standar Laravel (Pint)
vendor/bin/pint --format agent

# Deploy & instalasi awal produksi
php artisan app:install
```
