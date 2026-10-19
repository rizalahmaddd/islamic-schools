# Arsitektur & Struktur Sistem

Dokumen arsitektur, peta modul, alur data proses bisnis, dan rincian teknis **SIM Pesantren & Sekolah Islam**.

---

## Peta Modul & Komponen

Aplikasi menggunakan pendekatan modern **Laravel 13 + Livewire 3 + Volt**. Seluruh modul bisnis didaftarkan dan dikontrol melalui permission-based routing.

| Pilar Modul | Komponen / Controller | Rute URL | Template Tampilan |
|---|---|---|---|
| **Master Santri** | `App\Livewire\MasterData\Students` | `/master-data/siswa` | `resources/views/livewire/master-data/students.blade.php` |
| **Detail Santri** | `App\Livewire\MasterData\StudentShow` | `/master-data/siswa/{student}` | `resources/views/livewire/master-data/student-show.blade.php` |
| **Master Guru** | `App\Livewire\MasterData\Teachers` | `/master-data/guru` | `resources/views/livewire/master-data/teachers.blade.php` |
| **Master Kelas/Rombel** | `App\Livewire\MasterData\SchoolClasses` | `/master-data/kelas` | `resources/views/livewire/master-data/school-classes.blade.php` |
| **Tahun Ajaran** | `App\Livewire\MasterData\AcademicYears` | `/master-data/tahun-ajaran` | `resources/views/livewire/master-data/academic-years.blade.php` |
| **Presensi Rombel** | `App\Livewire\Attendance\StudentAttendanceSheet` | `/presensi/siswa` | `resources/views/livewire/attendance/student-attendance-sheet.blade.php` |
| **Check-in GPS Guru** | `App\Livewire\Attendance\TeacherCheckIn` | `/presensi/guru/check-in` | `resources/views/livewire/attendance/teacher-check-in.blade.php` |
| **Jurnal Mengajar** | `App\Livewire\Academic\TeachingJournals` | `/akademik/jurnal` | `resources/views/livewire/academic/teaching-journals.blade.php` |
| **Buku Nilai Formatif/Sumatif** | `App\Livewire\Academic\GradeSheet` | `/akademik/nilai` | `resources/views/livewire/academic/grade-sheet.blade.php` |
| **Cetak E-Rapor** | `App\Livewire\Academic\ReportCardIndex` | `/akademik/rapor` | `resources/views/livewire/academic/report-card-index.blade.php` |
| **Kasir POS SPP** | `App\Livewire\Finance\CashierPos` | `/keuangan/kasir` | `resources/views/livewire/finance/cashier-pos.blade.php` |
| **Kategori Tarif & Tagihan** | `App\Livewire\Finance\FeeCategories` | `/keuangan/tarif` | `resources/views/livewire/finance/fee-categories.blade.php` |
| **Riwayat Transaksi & Kuitansi** | `App\Livewire\Finance\PaymentTransactions` | `/keuangan/transaksi` | `resources/views/livewire/finance/payment-transactions.blade.php` |
| **Setoran Tahfidz Al-Qur'an** | `App\Livewire\Islamic\TahfidzSheet` | `/kepesantrenan/tahfidz` | `resources/views/livewire/islamic/tahfidz-sheet.blade.php` |
| **Mutaba'ah Ibadah Harian** | `App\Livewire\Islamic\MutabaahWorship` | `/kepesantrenan/mutabaah` | `resources/views/livewire/islamic/mutabaah-worship.blade.php` |
| **Kedisiplinan & Pelanggaran** | `App\Livewire\Islamic\ConductRecords` | `/kepesantrenan/kedisiplinan` | `resources/views/livewire/islamic/conduct-records.blade.php` |
| **Kamar Asrama** | `App\Livewire\Inventory\RoomIndex` | `/keasramaan/kamar` | `resources/views/livewire/inventory/room-index.blade.php` |
| **Perizinan Santri (Leaves)** | `App\Livewire\Boarding\StudentLeaves` | `/keasramaan/perizinan` | `resources/views/livewire/boarding/student-leaves.blade.php` |
| **Scanner Satpam (QR Gate Pass)**| `App\Livewire\Boarding\SecurityGateScanner` | `/keasramaan/gate-scanner` | `resources/views/livewire/boarding/security-gate-scanner.blade.php` |
| **PPDB Formulir Pendaftaran** | `App\Livewire\Admissions\PublicRegistration` | `/ppdb` | `resources/views/livewire/admissions/public-registration.blade.php` |
| **PPDB Verifikasi Panitia** | `App\Livewire\Admissions\RegistrationIndex` | `/ppdb/kelola` | `resources/views/livewire/admissions/registration-index.blade.php` |
| **Inventaris Sarpras** | `App\Livewire\Inventory\InventoryIndex` | `/sarpras/inventaris` | `resources/views/livewire/inventory/inventory-index.blade.php` |
| **Katalog Buku & Sirkulasi** | `App\Livewire\Library\BookIndex` | `/perpustakaan/buku` | `resources/views/livewire/library/book-index.blade.php` |
| **POS Merchant Kantin** | `App\Livewire\Cashless\CanteenPos` | `/kantin/pos` | `resources/views/livewire/cashless/canteen-pos.blade.php` |
| **Manajemen Kartu RFID** | `App\Livewire\Cashless\CardManagement` | `/kartu-rfid` | `resources/views/livewire/cashless/card-management.blade.php` |
| **Portal Mandiri Wali Santri** | `App\Livewire\Portal\GuardianDashboard` | `/portal-wali` | `resources/views/livewire/portal/guardian-dashboard.blade.php` |
| **Sakelar Fitur & Modul** | `App\Livewire\Settings\FeatureToggles` | `/pengaturan/fitur` | `resources/views/livewire/settings/feature-toggles.blade.php` |
| **Branding & Profil Lembaga** | `App\Livewire\Settings\CompanyProfile` | `/pengaturan/profil-sekolah` | `resources/views/livewire/settings/company-profile.blade.php` |
| **Peran & Izin (RBAC)** | `App\Livewire\Settings\RolesAndPermissions` | `/pengaturan/peran-izin` | `resources/views/livewire/settings/roles-and-permissions.blade.php` |
| **Cadangan Database & File** | `App\Livewire\Settings\Backups` | `/pengaturan/cadangan` | `resources/views/livewire/settings/backups.blade.php` |
| **Pusat Rekap Laporan & Audit** | `App\Livewire\Reports\ReportsCenter` | `/laporan` | `resources/views/livewire/reports/reports-center.blade.php` |

---

## Alur Proses Bisnis Utama

### 1. Siklus PPDB ke Santri Aktif
Calon santri mendaftar di `/ppdb` $\rightarrow$ Panitia menyeleksi berkas, tes wawancara, dan setoran hafalan $\rightarrow$ Panitia menyetujui $\rightarrow$ Sistem menjalankan `AdmissionConversionService`:
1. Menerbitkan nomor induk santri (NIS) resmi.
2. Memindahkan calon santri ke tabel `students` dengan status aktif.
3. Mengalokasikan rombel kelas dan kamar asrama.
4. Membuat akun pengguna (`User`) untuk wali santri dan menghubungkannya secara otomatis ke data anak.

### 2. Notifikasi Kehadiran Otomatis
Guru membuka lembar absensi di `/presensi/siswa` $\rightarrow$ Memilih status hadir/sakit/izin/alpa $\rightarrow$ Saat status santri disimpan:
1. Catatan presensi tersimpan di tabel `student_attendances`.
2. Job `SendAttendanceWhatsAppNotification` didispatch ke antrean queue.
3. Service `FonnteService` mengirim pesan notifikasi resmi ke nomor WhatsApp orang tua santri.

### 3. Penagihan & Kasir POS
1. Modul keuangan membuat tagihan berkala via `BillingService::generateMonthlyBills()`.
2. Siswa yang memiliki beasiswa/keringanan otomatis dipotong melalui `StudentFeeWaiver`.
3. Pembayaran tunai: Petugas loket kasir TU memilih tagihan tertunggak santri $\rightarrow$ Sistem mengkalkulasi nominal dan kembalian $\rightarrow$ Mencetak kuitansi resmi ber-kop surat dengan teks terbilang rupiah.
4. Pembayaran online: Wali santri di portal membuat kode pembayaran Tripay $\rightarrow$ Setelah dibayar, webhook Tripay memverifikasi tanda tangan HMAC-SHA256 $\rightarrow$ Tagihan otomatis lunas secara instan.

### 4. Smart Card RFID & Kantin Cashless
Santri menempelkan kartu fisik RFID/NFC di mesin pembaca USB $\rightarrow$ Modul `/kartu-rfid` menghubungkan UID kartu ke santri $\rightarrow$ Di kasir kantin (`/kantin/pos`), scan kartu langsung membaca saldo dompet digital santri $\rightarrow$ Saldo dipotong setelah memeriksa batas belanja harian (*daily limit*) yang ditentukan oleh orang tua.

---

## Struktur Hak Akses (12 Peran Hierarkis)

Dikelola menggunakan `spatie/laravel-permission`:

1. **Superadmin**: Akses menyeluruh tanpa batasan (`Gate::before`).
2. **Kepala Sekolah / Mudir**: Akses monitoring eksekutif, rekapitulasi, persetujuan rapor dan jurnal guru.
3. **Tata Usaha (TU)**: Pengelolaan administrasi santri, guru, rombel kelas, surat, dan PPDB.
4. **Bendahara**: Pengelolaan master tarif SPP, loket pembayaran kasir, pencatatan kas, dan laporan keuangan.
5. **Guru / Asatidz**: Pengisian presensi kelas, pencatatan jurnal mengajar harian, dan penginputan nilai formatif/sumatif.
6. **Wali Kelas**: Pengesahan kehadiran rombel binaan, catatan kepribadian santri, dan pencetakan e-rapor.
7. **Musyrif Asrama**: Pemantauan kamar santri, setoran tahfidz, mutaba'ah ibadah, dan rekomendasi izin keluar asrama.
8. **Satpam / Security**: Pemindai gate pass QR untuk validasi santri keluar dan masuk kembali ke pesantren.
9. **Wali Murid**: Akses portal mandiri (PWA) untuk monitoring perkembangan hafalan, nilai, kehadiran, dan pembayaran SPP anak.
10. **Santri**: Akses kartu digital dan riwayat transaksi saku.
11. **Staf Sarpras**: Pengelolaan ruang, aset inventaris, dan perpustakaan.
12. **Panitia PPDB**: Verifikasi berkas, penilaian seleksi masuk, dan konversi santri baru.

---

## Sakelar Fitur (Feature Toggles)

Superadmin dapat menonaktifkan modul tertentu yang tidak relevan dengan kebutuhan institusi (misalnya menonaktifkan asrama untuk sekolah umum atau menonaktifkan smart card RFID) melalui `/pengaturan/fitur` via `App\Support\Features`. Saat modul dinonaktifkan:
- Rute web dan API otomatis diblokir dengan respon `404 Not Found`.
- Item menu navigasi dan pencarian global (`Ctrl+K`) otomatis disembunyikan.
- Integritas data historis tetap aman dan tersimpan utuh di database.

---

## REST API & Dokumentasi OpenAPI

- **Base Endpoint**: `/api/v1`
- **Autentikasi**: Laravel Sanctum Personal Access Token
- **Dokumentasi Interaktif**: Tersedia di `/docs/api` ditenagai oleh Scalar OpenAPI UI.
