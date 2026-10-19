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
        Schema::create('tahfidz_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_type', 20); // ziyadah, murajaah
            $table->foreignId('surah_id')->constrained('quran_surahs')->cascadeOnDelete();
            $table->unsignedSmallInteger('verse_start');
            $table->unsignedSmallInteger('verse_end');
            $table->unsignedTinyInteger('juz');
            $table->string('grade', 10)->default('A'); // A, B, C, D
            $table->text('tajwid_notes')->nullable();
            $table->dateTime('recorded_at');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tahfidz_sessions');
    }
};
