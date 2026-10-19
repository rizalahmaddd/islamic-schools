<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ruangan Sarpras / Lokasi Aset
        Schema::create('inventory_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('building')->nullable();
            $table->string('pic_name')->nullable();
            $table->string('pic_phone')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Barang / Aset Inventaris
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_room_id')->constrained('inventory_rooms')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category')->default('mebel'); // mebel, elektronik, alat_tulis, buku_referensi, peralatan_lab, kendaraan, lainnya
            $table->string('brand')->nullable();
            $table->string('condition')->default('baik'); // baik, rusak_ringan, rusak_berat, hilang
            $table->string('source_of_fund')->nullable(); // bos, yayasan, hibah, komite, lainnya
            $table->date('purchase_date')->nullable();
            $table->decimal('price', 14, 2)->default(0);
            $table->integer('quantity')->default(1);
            $table->string('unit')->default('unit');
            $table->string('qr_code_token')->unique();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Buku / Kitab Perpustakaan
        Schema::create('library_books', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('isbn')->nullable()->index();
            $table->string('title');
            $table->string('author')->nullable();
            $table->string('publisher')->nullable();
            $table->integer('publish_year')->nullable();
            $table->string('category')->default('umum'); // umum, kitab_kuning, pelajaran, tahfidz, kamus, fiksi, lainnya
            $table->string('call_number')->nullable();
            $table->string('rack_location')->nullable();
            $table->integer('total_copies')->default(1);
            $table->integer('available_copies')->default(1);
            $table->string('barcode_token')->unique();
            $table->timestamps();
        });

        // 4. Sirkulasi Peminjaman Buku Perpustakaan
        Schema::create('library_loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_code')->unique();
            $table->foreignId('library_book_id')->constrained('library_books')->cascadeOnDelete();
            $table->string('borrower_type'); // student, teacher
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->date('borrow_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->decimal('fine_amount', 12, 2)->default(0);
            $table->string('status')->default('active'); // active, returned, overdue, lost
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_loans');
        Schema::dropIfExists('library_books');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('inventory_rooms');
    }
};
