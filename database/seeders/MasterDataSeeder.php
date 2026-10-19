<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\AdmissionBatch;
use App\Models\AdmissionCandidate;
use App\Models\CanteenMerchant;
use App\Models\CanteenProduct;
use App\Models\DormBuilding;
use App\Models\DormPlacement;
use App\Models\DormRoom;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\Guardian;
use App\Models\InventoryItem;
use App\Models\InventoryRoom;
use App\Models\LibraryBook;
use App\Models\PaymentTransaction;
use App\Models\PaymentTransactionItem;
use App\Models\QuranSurah;
use App\Models\ReportCardSummary;
use App\Models\RfidCard;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentBill;
use App\Models\StudentConductRecord;
use App\Models\StudentDailyWorship;
use App\Models\StudentEnrollment;
use App\Models\StudentFeeWaiver;
use App\Models\StudentGrade;
use App\Models\StudentWallet;
use App\Models\Subject;
use App\Models\SubjectAssignment;
use App\Models\TahfidzSession;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\TeachingJournal;
use App\Models\User;
use App\Services\DocumentNumberGenerator;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Data demo untuk lingkungan pengembangan, bukan data produksi nyata.
 */
class MasterDataSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // 1. Tahun Ajaran
        $academicYearActive = AcademicYear::firstOrCreate(
            ['name' => '2026/2027', 'semester' => 'ganjil'],
            [
                'start_date' => '2026-07-15',
                'end_date' => '2026-12-20',
                'is_active' => true,
            ]
        );

        AcademicYear::firstOrCreate(
            ['name' => '2025/2026', 'semester' => 'genap'],
            [
                'start_date' => '2026-01-08',
                'end_date' => '2026-06-25',
                'is_active' => false,
            ]
        );

        // 2. Guru & Pendidik
        $teacher1 = Teacher::firstOrCreate(
            ['nip' => '198501012010011001'],
            [
                'name' => 'Ustadz Abdullah Mansyur, S.Pd.I',
                'gender' => 'L',
                'phone' => '6281234567001',
                'email' => 'abdullah@sekolah.id',
                'specialization' => 'Tahfidz Al-Qur\'an',
                'is_active' => true,
            ]
        );

        $teacher2 = Teacher::firstOrCreate(
            ['nip' => '198902022014022002'],
            [
                'name' => 'Ustadzah Fatimah Azzahra, Lc.',
                'gender' => 'P',
                'phone' => '6281234567002',
                'email' => 'fatimah@sekolah.id',
                'specialization' => 'Bahasa Arab & Hadits',
                'is_active' => true,
            ]
        );

        $teacher3 = Teacher::firstOrCreate(
            ['nip' => '198003032005011003'],
            [
                'name' => 'Ustadz Zulkifli Harahap, M.Pd',
                'gender' => 'L',
                'phone' => '6281234567003',
                'email' => 'zulkifli@sekolah.id',
                'specialization' => 'Fiqih & Ushul Fiqih',
                'is_active' => true,
            ]
        );

        $teacher4 = Teacher::firstOrCreate(
            ['nip' => '199104042018011004'],
            [
                'name' => 'Ustadz Ridwan Kamil, S.Si',
                'gender' => 'L',
                'phone' => '6281234567004',
                'email' => 'ridwan@sekolah.id',
                'specialization' => 'Matematika & IPA Terpadu',
                'is_active' => true,
            ]
        );

        // 3. Rombongan Belajar (Kelas)
        $class7A = SchoolClass::firstOrCreate(
            ['academic_year_id' => $academicYearActive->id, 'name' => '7A Tahfidz'],
            ['grade_level' => 7, 'homeroom_teacher_id' => $teacher1->id, 'capacity' => 30]
        );

        $class7B = SchoolClass::firstOrCreate(
            ['academic_year_id' => $academicYearActive->id, 'name' => '7B Reguler'],
            ['grade_level' => 7, 'homeroom_teacher_id' => $teacher4->id, 'capacity' => 32]
        );

        $class8A = SchoolClass::firstOrCreate(
            ['academic_year_id' => $academicYearActive->id, 'name' => '8A Tahfidz'],
            ['grade_level' => 8, 'homeroom_teacher_id' => $teacher2->id, 'capacity' => 28]
        );

        $class8B = SchoolClass::firstOrCreate(
            ['academic_year_id' => $academicYearActive->id, 'name' => '8B Reguler'],
            ['grade_level' => 8, 'homeroom_teacher_id' => $teacher3->id, 'capacity' => 32]
        );

        // 4. Data Wali Murid & Santri Contoh
        $sampleStudents = [
            [
                'nis' => '20260001', 'nisn' => '0089123401', 'name' => 'Muhammad Bilal Al-Fatih', 'nickname' => 'Bilal',
                'gender' => 'L', 'pob' => 'Jakarta', 'dob' => '2013-03-12', 'class' => $class7A,
                'guardian' => ['name' => 'H. Irfan Hakim', 'relationship' => 'Ayah', 'phone' => '6281299887701', 'occupation' => 'Wiraswasta'],
            ],
            [
                'nis' => '20260002', 'nisn' => '0089123402', 'name' => 'Aisyah Humaira', 'nickname' => 'Aisyah',
                'gender' => 'P', 'pob' => 'Bandung', 'dob' => '2013-05-20', 'class' => $class7A,
                'guardian' => ['name' => 'Drs. Bambang Sudirman', 'relationship' => 'Ayah', 'phone' => '6281299887702', 'occupation' => 'PNS'],
            ],
            [
                'nis' => '20260003', 'nisn' => '0089123403', 'name' => 'Thariq Bin Ziyad', 'nickname' => 'Thariq',
                'gender' => 'L', 'pob' => 'Surabaya', 'dob' => '2013-01-15', 'class' => $class7B,
                'guardian' => ['name' => 'Hj. Siti Nurhaliza', 'relationship' => 'Ibu', 'phone' => '6281299887703', 'occupation' => 'Pedagang'],
            ],
            [
                'nis' => '20260004', 'nisn' => '0089123404', 'name' => 'Maryam Khansa', 'nickname' => 'Khansa',
                'gender' => 'P', 'pob' => 'Bogor', 'dob' => '2013-08-10', 'class' => $class7B,
                'guardian' => ['name' => 'Ahmad Subagyo', 'relationship' => 'Ayah', 'phone' => '6281299887704', 'occupation' => 'Karyawan Swasta'],
            ],
            [
                'nis' => '20250005', 'nisn' => '0078123405', 'name' => 'Khalid Al-Walid', 'nickname' => 'Khalid',
                'gender' => 'L', 'pob' => 'Yogyakarta', 'dob' => '2012-06-04', 'class' => $class8A,
                'guardian' => ['name' => 'H. Mulyono Santoso', 'relationship' => 'Ayah', 'phone' => '6281299887705', 'occupation' => 'Dosen'],
            ],
        ];

        foreach ($sampleStudents as $data) {
            $guardian = Guardian::firstOrCreate(
                ['phone' => $data['guardian']['phone']],
                [
                    'name' => $data['guardian']['name'],
                    'relationship' => $data['guardian']['relationship'],
                    'occupation' => $data['guardian']['occupation'],
                    'address' => 'Jl. Pesantren No. '.rand(1, 50),
                ]
            );

            if ($data['nis'] === '20260001') {
                $waliUser = User::where('email', 'walimurid@example.test')->first();
                if ($waliUser) {
                    $guardian->update(['user_id' => $waliUser->id]);
                }
            }

            $student = Student::firstOrCreate(
                ['nis' => $data['nis']],
                [
                    'guardian_id' => $guardian->id,
                    'nisn' => $data['nisn'],
                    'name' => $data['name'],
                    'nickname' => $data['nickname'],
                    'gender' => $data['gender'],
                    'pob' => $data['pob'],
                    'dob' => $data['dob'],
                    'status' => 'active',
                    'address' => 'Jl. Pesantren No. '.rand(1, 50),
                ]
            );

            StudentEnrollment::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'academic_year_id' => $academicYearActive->id,
                ],
                [
                    'class_id' => $data['class']->id,
                    'enrolled_at' => '2026-07-15',
                    'status' => 'active',
                ]
            );
        }

        // 5. Presensi Contoh
        $recordedUser = User::first();
        $studentsIn7A = Student::whereHas('enrollments', fn ($q) => $q->where('class_id', $class7A->id))->get();
        foreach ($studentsIn7A as $idx => $st) {
            $status = match ($idx % 5) {
                1 => 'sick',
                2 => 'permit',
                default => 'present',
            };
            $notes = match ($status) {
                'sick' => 'Demam flu',
                'permit' => 'Izin keluarga',
                default => null,
            };

            StudentAttendance::firstOrCreate(
                [
                    'student_id' => $st->id,
                    'date' => now()->toDateString(),
                ],
                [
                    'class_id' => $class7A->id,
                    'status' => $status,
                    'notes' => $notes,
                    'recorded_by' => $recordedUser?->id,
                ]
            );
        }

        // 6. Data Mata Pelajaran
        $subjectsData = [
            ['code' => 'TFZ', 'name' => "Tahfidz & Tahsin Al-Qur'an", 'category' => 'islamic'],
            ['code' => 'ARB', 'name' => 'Bahasa Arab', 'category' => 'islamic'],
            ['code' => 'FQH', 'name' => 'Fiqih & Ibadah Praktis', 'category' => 'islamic'],
            ['code' => 'AQD', 'name' => 'Aqidah Akhlak', 'category' => 'islamic'],
            ['code' => 'MTK', 'name' => 'Matematika', 'category' => 'general'],
            ['code' => 'IPA', 'name' => 'IPA Terpadu', 'category' => 'general'],
            ['code' => 'IND', 'name' => 'Bahasa Indonesia', 'category' => 'general'],
            ['code' => 'ING', 'name' => 'Bahasa Inggris', 'category' => 'general'],
        ];

        $seededSubjects = [];
        foreach ($subjectsData as $s) {
            $seededSubjects[$s['code']] = Subject::firstOrCreate(['code' => $s['code']], $s);
        }

        // 7. Presensi Guru Contoh
        TeacherAttendance::firstOrCreate(
            ['teacher_id' => $teacher1->id, 'date' => now()->toDateString()],
            [
                'check_in_time' => '07:10:00',
                'check_out_time' => null,
                'method' => 'gps',
                'latitude' => -6.200010,
                'longitude' => 106.816670,
                'distance_meters' => 18,
                'status' => 'present',
                'recorded_by' => $recordedUser?->id,
            ]
        );

        TeacherAttendance::firstOrCreate(
            ['teacher_id' => $teacher2->id, 'date' => now()->toDateString()],
            [
                'check_in_time' => '07:42:00',
                'check_out_time' => null,
                'method' => 'gps',
                'latitude' => -6.200020,
                'longitude' => 106.816680,
                'distance_meters' => 32,
                'status' => 'late',
                'notes' => 'Terkendala hujan deras',
                'recorded_by' => $recordedUser?->id,
            ]
        );

        // 8. Jurnal Mengajar Contoh
        TeachingJournal::firstOrCreate(
            [
                'teacher_id' => $teacher1->id,
                'class_id' => $class7A->id,
                'date' => now()->toDateString(),
                'period_start' => 1,
            ],
            [
                'academic_year_id' => $academicYearActive->id,
                'subject_id' => $seededSubjects['TFZ']->id ?? null,
                'period_end' => 2,
                'topic_taught' => "Tahsin Surat Al-Mulk ayat 1-15 & Kaidah Mad Thobi'i",
                'learning_activities' => 'Murojaah pembuka bersama, dilanjutkan talaqqi dan perbaikan makharijul huruf santri bergantian',
                'notes' => 'Alhamdulillah mayoritas santri lancar, 3 santri perlu murajaah mandiri di asrama',
            ]
        );

        TeachingJournal::firstOrCreate(
            [
                'teacher_id' => $teacher4->id,
                'class_id' => $class7B->id,
                'date' => now()->toDateString(),
                'period_start' => 3,
            ],
            [
                'academic_year_id' => $academicYearActive->id,
                'subject_id' => $seededSubjects['MTK']->id ?? null,
                'period_end' => 4,
                'topic_taught' => 'Operasi Hitung Aljabar & Penerapan Masalah Riil',
                'learning_activities' => 'Pemaparan rumus dasar, latihan soal berpasangan, dan kuis cepat interaktif',
                'notes' => 'Partisipasi santri sangat aktif',
            ]
        );

        // 9. Data Keuangan & SPP Contoh
        $monthlyFee = FeeCategory::firstOrCreate(
            ['name' => 'SPP Bulanan'],
            ['type' => 'monthly', 'is_active' => true]
        );

        $developmentFee = FeeCategory::firstOrCreate(
            ['name' => 'Uang Gedung & Sarpras'],
            ['type' => 'non_monthly', 'is_active' => true]
        );

        $struct7 = FeeStructure::firstOrCreate(
            ['academic_year_id' => $academicYearActive->id, 'fee_category_id' => $monthlyFee->id, 'grade_level' => '7'],
            ['default_amount' => 350000.00]
        );

        $struct8 = FeeStructure::firstOrCreate(
            ['academic_year_id' => $academicYearActive->id, 'fee_category_id' => $monthlyFee->id, 'grade_level' => '8'],
            ['default_amount' => 400000.00]
        );

        $firstStudent = Student::first();
        if ($firstStudent) {
            // Berikan beasiswa 50%
            StudentFeeWaiver::firstOrCreate(
                [
                    'student_id' => $firstStudent->id,
                    'fee_category_id' => $monthlyFee->id,
                    'academic_year_id' => $academicYearActive->id,
                ],
                [
                    'discount_type' => 'percentage',
                    'discount_value' => 50.00,
                    'reason' => 'Beasiswa Prestasi Tahfidz',
                ]
            );

            // Tagihan bulan berjalan
            $bill1 = StudentBill::firstOrCreate(
                [
                    'student_id' => $firstStudent->id,
                    'fee_category_id' => $monthlyFee->id,
                    'academic_year_id' => $academicYearActive->id,
                    'month' => (int) now()->month,
                    'year' => (int) now()->year,
                ],
                [
                    'amount_due' => 175000.00,
                    'amount_paid' => 175000.00,
                    'status' => 'paid',
                    'due_date' => now()->startOfMonth()->addDays(9)->toDateString(),
                    'notes' => 'Tagihan SPP (Diskon 50%)',
                ]
            );

            // Transaksi pembayaran contoh
            // Nomor lewat generator supaya urutan KW berikutnya dari kasir tidak bentrok dengan data contoh.
            $payment = PaymentTransaction::firstWhere(['student_id' => $firstStudent->id, 'notes' => 'Pembayaran SPP Tunai Kasir TU'])
                ?? PaymentTransaction::create([
                    'receipt_number' => app(DocumentNumberGenerator::class)->next('KW', 5),
                    'student_id' => $firstStudent->id,
                    'total_amount' => 175000.00,
                    'payment_method' => 'cash',
                    'received_by' => $recordedUser?->id,
                    'paid_at' => now(),
                    'notes' => 'Pembayaran SPP Tunai Kasir TU',
                ]);

            PaymentTransactionItem::firstOrCreate(
                [
                    'payment_transaction_id' => $payment->id,
                    'student_bill_id' => $bill1->id,
                ],
                [
                    'amount_allocated' => 175000.00,
                ]
            );

            // Tagihan bulan berikutnya (unpaid)
            $nextMonth = now()->month === 12 ? 1 : now()->month + 1;
            $nextYear = now()->month === 12 ? now()->year + 1 : now()->year;
            StudentBill::firstOrCreate(
                [
                    'student_id' => $firstStudent->id,
                    'fee_category_id' => $monthlyFee->id,
                    'academic_year_id' => $academicYearActive->id,
                    'month' => $nextMonth,
                    'year' => $nextYear,
                ],
                [
                    'amount_due' => 175000.00,
                    'amount_paid' => 0.00,
                    'status' => 'unpaid',
                    'due_date' => sprintf('%04d-%02d-10', $nextYear, $nextMonth),
                    'notes' => 'Tagihan SPP (Diskon 50%)',
                ]
            );

            // 11. Data Kepesantrenan, Tahfidz & Adab Contoh
            $surahNaba = QuranSurah::where('number', 78)->first();
            if ($surahNaba) {
                TahfidzSession::firstOrCreate(
                    [
                        'student_id' => $firstStudent->id,
                        'surah_id' => $surahNaba->id,
                        'verse_start' => 1,
                        'verse_end' => 20,
                    ],
                    [
                        'teacher_id' => $teacher1->id,
                        'session_type' => 'ziyadah',
                        'juz' => 30,
                        'grade' => 'A',
                        'tajwid_notes' => 'Makhraj huruf jelas, tartil, dan mutqin',
                        'recorded_at' => now(),
                    ]
                );
            }

            StudentDailyWorship::firstOrCreate(
                [
                    'student_id' => $firstStudent->id,
                    'date' => now()->toDateString(),
                ],
                [
                    'fajr' => 'jamaah',
                    'dhuhr' => 'jamaah',
                    'asr' => 'jamaah',
                    'maghrib' => 'jamaah',
                    'isha' => 'jamaah',
                    'dhuha' => true,
                    'tahajjud' => true,
                    'tilawah_pages' => 4,
                    'fasting_type' => 'none',
                    'notes' => 'Disiplin shalat berjamaah di masjid pesantren',
                    'recorded_by' => $recordedUser?->id,
                ]
            );

            StudentConductRecord::firstOrCreate(
                [
                    'student_id' => $firstStudent->id,
                    'description' => 'Juara 1 Musabaqah Hifzhil Qur\'an (MHQ) Tingkat Kota',
                ],
                [
                    'date' => now()->toDateString(),
                    'type' => 'merit',
                    'points' => 25,
                    'category' => 'Prestasi Akademik / Lomba',
                    'action_taken' => 'Pemberian piagam penghargaan dan bebas SPP 1 bulan',
                    'recorded_by' => $recordedUser?->id,
                ]
            );

            StudentConductRecord::firstOrCreate(
                [
                    'student_id' => $firstStudent->id,
                    'description' => 'Terlambat halaqah tahfidz subuh 10 menit',
                ],
                [
                    'date' => now()->subDays(2)->toDateString(),
                    'type' => 'violation',
                    'points' => 5,
                    'category' => 'Kedisiplinan & Waktu',
                    'action_taken' => 'Teguran lisan dan murajaah mandiri di serambi masjid',
                    'recorded_by' => $recordedUser?->id,
                ]
            );

            // 11. Penugasan Mengajar & Nilai Akademik Santri (Fase 6)
            $assignment1 = SubjectAssignment::firstOrCreate(
                [
                    'academic_year_id' => $academicYearActive->id,
                    'class_id' => $class7A->id,
                    'subject_id' => $seededSubjects['TFZ']->id,
                ],
                [
                    'teacher_id' => $teacher1->id,
                    'passing_grade' => 75.00,
                ]
            );

            $assignment2 = SubjectAssignment::firstOrCreate(
                [
                    'academic_year_id' => $academicYearActive->id,
                    'class_id' => $class7A->id,
                    'subject_id' => $seededSubjects['ARB']->id,
                ],
                [
                    'teacher_id' => $teacher2->id,
                    'passing_grade' => 75.00,
                ]
            );

            StudentGrade::firstOrCreate(
                [
                    'subject_assignment_id' => $assignment1->id,
                    'student_id' => $firstStudent->id,
                ],
                [
                    'formative_average' => 90.00,
                    'mid_term_score' => 92.00,
                    'final_term_score' => 94.00,
                    'final_score' => 91.50,
                    'predicate' => 'A',
                    'achievement_desc' => 'Menunjukkan penguasaan yang sangat istimewa dalam hafalan surat dan makharijul huruf.',
                    'improvement_desc' => 'Pertahankan kefasihan dan konsistensi murajaah mandiri harian.',
                ]
            );

            StudentGrade::firstOrCreate(
                [
                    'subject_assignment_id' => $assignment2->id,
                    'student_id' => $firstStudent->id,
                ],
                [
                    'formative_average' => 84.00,
                    'mid_term_score' => 86.00,
                    'final_term_score' => 88.00,
                    'final_score' => 85.50,
                    'predicate' => 'B',
                    'achievement_desc' => 'Menunjukkan penguasaan yang baik dalam kaidah nahwu sharaf dasar dan kosa kata.',
                    'improvement_desc' => 'Perbanyak latihan percakapan (muhadatsah) dalam keseharian asrama.',
                ]
            );

            ReportCardSummary::firstOrCreate(
                [
                    'student_id' => $firstStudent->id,
                    'class_id' => $class7A->id,
                    'academic_year_id' => $academicYearActive->id,
                ],
                [
                    'sick_count' => 1,
                    'permit_count' => 0,
                    'absent_count' => 0,
                    'extracurricular_notes' => 'Aktif dalam kegiatan Pramuka Penggalang dan Muhadhoroh 3 Bahasa.',
                    'homeroom_notes' => 'Ananda memiliki akhlak mulia dan keteladanan yang baik. Pertahankan terus hafalan dan prestasinya.',
                    'promotion_status' => 'promoted',
                    'is_locked' => false,
                ]
            );

            // 12. Keasramaan & Kamar Asrama (Fase 7)
            $dormBuilding = DormBuilding::firstOrCreate(
                ['name' => 'Gedung Umar bin Khattab'],
                ['gender' => 'male', 'description' => 'Asrama Putra Tingkat MTs/SMP']
            );

            $dormRoom = DormRoom::firstOrCreate(
                ['dorm_building_id' => $dormBuilding->id, 'room_number' => 'U-101'],
                ['capacity' => 8, 'description' => 'Kamar Al-Fatih']
            );

            DormPlacement::firstOrCreate(
                [
                    'student_id' => $firstStudent->id,
                    'academic_year_id' => $academicYearActive->id,
                ],
                [
                    'dorm_room_id' => $dormRoom->id,
                    'bed_number' => 'B-01',
                    'placed_at' => '2026-07-15',
                ]
            );

            // 13. PPDB Santri Baru (Fase 8)
            $batch = AdmissionBatch::firstOrCreate(
                ['academic_year_id' => $academicYearActive->id, 'name' => 'Gelombang 1 Reguler & Tahfidz'],
                [
                    'wave_number' => 1,
                    'start_date' => now()->startOfMonth()->toDateString(),
                    'end_date' => now()->addMonths(3)->endOfMonth()->toDateString(),
                    'quota' => 60,
                    'registration_fee' => 250000,
                    'is_active' => true,
                ]
            );

            AdmissionCandidate::firstWhere(['admission_batch_id' => $batch->id, 'student_name' => 'Fadhil Rahman Al-Ayyubi']) ?? AdmissionCandidate::create(
                [
                    'admission_batch_id' => $batch->id,
                    'student_name' => 'Fadhil Rahman Al-Ayyubi',
                    'registration_number' => app(DocumentNumberGenerator::class)->next('REG'),
                    'gender' => 'male',
                    'pob' => 'Jakarta',
                    'dob' => '2014-02-10',
                    'guardian_name' => 'H. Rahman Santoso',
                    'guardian_relationship' => 'Ayah',
                    'guardian_phone' => '6281299881122',
                    'address' => 'Jl. Kebon Jeruk No. 12',
                    'status' => 'tested',
                    'test_score_academic' => 88.50,
                    'test_score_quran' => 90.00,
                    'test_score_interview' => 85.00,
                ]
            );

            // 14. Sarpras & Inventaris (Fase 11)
            $roomLab = InventoryRoom::firstOrCreate(
                ['code' => 'R-LAB-KOMP'],
                ['name' => 'Laboratorium Komputer', 'building' => 'Gedung Ali Lt. 2', 'pic_name' => 'Ahmad Fauzi', 'pic_phone' => '081234567890']
            );

            InventoryItem::firstOrCreate(
                ['code' => 'AST-KOMP-001'],
                [
                    'inventory_room_id' => $roomLab->id,
                    'name' => 'PC All-in-One Core i5',
                    'category' => 'elektronik',
                    'brand' => 'Lenovo',
                    'condition' => 'baik',
                    'quantity' => 1,
                    'price' => 8500000,
                    'source_of_fund' => 'bos',
                    'purchase_date' => '2026-01-15',
                    'qr_code_token' => 'QR-AST-KOMP-001-TOK',
                ]
            );

            // 15. Perpustakaan & Kitab (Fase 11)
            LibraryBook::firstOrCreate(
                ['code' => 'KTB-FQH-001'],
                [
                    'title' => 'Matan Safinatun Naja fi Ushuliddin wal Fiqh',
                    'author' => 'Salim bin Sumair al-Hadhrami',
                    'publisher' => 'Darul Kutub Islamiyah',
                    'publish_year' => 2020,
                    'category' => 'kitab_kuning',
                    'isbn' => '978-602-1234-01-1',
                    'call_number' => '2X4.1 HAD m',
                    'rack_location' => 'Rak A-01',
                    'total_copies' => 25,
                    'available_copies' => 24,
                    'barcode_token' => 'BC-KTB-FQH-001-TOK',
                ]
            );

            // 16. Kantin Cashless & Smart Card RFID (Fase 12)
            $merchant = CanteenMerchant::firstOrCreate(
                ['code' => 'KTN-01'],
                [
                    'name' => 'Kantin Sehat Pesantren',
                    'pic_name' => 'Ibu Hajjah Aminah',
                    'is_active' => true,
                ]
            );

            CanteenProduct::firstOrCreate(
                ['canteen_merchant_id' => $merchant->id, 'code' => 'PRD-001'],
                [
                    'name' => 'Nasi Ayam Geprek Barakah',
                    'category' => 'makanan',
                    'price' => 12000,
                    'stock' => 50,
                    'is_available' => true,
                ]
            );

            CanteenProduct::firstOrCreate(
                ['canteen_merchant_id' => $merchant->id, 'code' => 'PRD-002'],
                [
                    'name' => 'Susu Kurma Segar 250ml',
                    'category' => 'minuman',
                    'price' => 7000,
                    'stock' => 30,
                    'is_available' => true,
                ]
            );

            StudentWallet::firstOrCreate(
                ['student_id' => $firstStudent->id],
                [
                    'balance' => 45000.00,
                    'daily_limit' => 25000.00,
                    'is_active' => true,
                ]
            );

            RfidCard::firstOrCreate(
                ['card_uid' => 'CARD-BILAL-2026'],
                [
                    'student_id' => $firstStudent->id,
                    'label' => 'Kartu Utama',
                    'is_active' => true,
                ]
            );
        }
    }
}
