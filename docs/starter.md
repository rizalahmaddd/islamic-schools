# Catatan Fondasi & Ekstensi Modul

SIM Pesantren & Sekolah Islam dibangun di atas fondasi standar JagoDev Laravel Starter, dengan arsitektur yang modular, terdokumentasi, dan mudah diperluas.

---

## Fondasi Bawaan Aplikasi

- **Autentikasi Ganda**: Login dengan username/email/nomor HP, password konvensional, atau OTP WhatsApp bebas sandi via Fonnte gateway.
- **RBAC (Role-Based Access Control)**: Ditenagai `spatie/laravel-permission` dengan matriks peran & perizinan visual. Superadmin melewati semua cek izin melalui `Gate::before`.
- **Sakelar Fitur (Feature Toggles)**: Superadmin dapat mematikan modul mana pun secara dinamis melalui dashboard tanpa menghapus data maupun merusak relasi database.
- **Audit Trail & Log Aktivitas**: Setiap perubahan data pada model `Auditable` tercatat otomatis beserta snapshot *before/after*, aktor pengubah, alamat IP, dan user-agent.
- **REST API v1 & Scalar OpenAPI**: Endpoint RESTful dengan Laravel Sanctum, FormRequest + Resource, dan dokumentasi OpenAPI otomatis di `/docs/api`.
- **Realtime Broadcasting**: Laravel Reverb + Laravel Echo untuk auto-refresh tabel dan badge lonceng notifikasi secara instan tanpa reload halaman.
- **Pencadangan (Backup & Restore)**: Backup terjadwal database SQLite/MySQL dan file storage secara manual atau otomatis via cron scheduler.
- **UI Kit & Aksesibilitas**: Blade components siap pakai dengan standar konsistensi tinggi, dukungan tema gelap/terang, dan tata letak mobile-responsive yang rapi.

---

## Menambah Modul Baru

Untuk menambahkan modul baru di sistem ini, ikuti pola modul acuan **Siswa (Students)**. Berkas yang terlibat:

```
app/Models/Student.php                                   # Model (Auditable, event realtime, notifikasi)
database/migrations/*_create_students_table.php
database/factories/StudentFactory.php
database/seeders/StudentSeeder.php                      # Data dummy pengujian
app/Livewire/MasterData/Students.php                    # Daftar + form modal (WithCrudActions)
app/Livewire/MasterData/StudentShow.php                 # Halaman detail & riwayat perubahan
resources/views/livewire/master-data/students.blade.php
resources/views/livewire/master-data/student-show.blade.php
resources/views/print/student-card.blade.php            # Dokumen cetak PDF
routes/master-data.php                                  # Route web
app/Http/Controllers/Api/V1/MasterData/StudentController.php
app/Http/Requests/Api/V1/MasterData/StudentRequest.php
app/Http/Resources/V1/MasterData/StudentResource.php
routes/api/v1/master-data.php                           # Route API (middleware feature:...)
app/Events/StudentChanged.php                           # Broadcast realtime Reverb
tests/Feature/StudentsTest.php                          # Pest Feature test
```

### Checklist Registrasi Modul Baru

1. **Perizinan (`PermissionSeeder`)**:
   Daftarkan grup izin baru di `PermissionSeeder::PERMISSION_GROUPS` dan tentukan hak akses default di `DEFAULT_ROLE_PERMISSIONS`. Halaman matriks izin akan membacanya secara otomatis.

2. **Sakelar Fitur (`Features::MODULES`)**:
   Daftarkan modul baru di `App\Support\Features::MODULES` beserta pola route URL-nya.

3. **Menu Navigasi (`Navigation::items()`)**:
   Tambahkan entri di `App\Support\Navigation::items()` lengkap dengan `route`, `icon`, `feature`, `can`, serta `keywords` untuk pencarian cepat.

4. **Pencarian Global (`Ctrl+K`)**:
   Tambahkan sub-bagian baru pada array `$sections` di `resources/views/livewire/layout/global-search.blade.php`.

5. **Audit Trail**:
   Gunakan trait `Auditable` di Model Anda dan tambahkan pemetaan label di `App\Support\Audit\AuditTrail::SUBJECT_LABELS`.

6. **Pengujian (Pest)**:
   Buat feature test di `tests/Feature/` dan pastikan seluruh test suite lolos (`php artisan test --compact`).
