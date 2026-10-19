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
        // 1. Gelombang Pendaftaran PPDB
        Schema::create('admission_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('wave_number')->default(1);
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('quota')->default(100);
            $table->decimal('registration_fee', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Data Calon Santri & Seleksi
        Schema::create('admission_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_batch_id')->constrained('admission_batches')->cascadeOnDelete();
            $table->string('registration_number', 50)->unique();
            $table->string('student_name');
            $table->enum('gender', ['male', 'female'])->default('male');
            $table->string('nisn', 20)->nullable();
            $table->string('nik', 20)->nullable();
            $table->string('pob')->nullable();
            $table->date('dob')->nullable();
            $table->string('previous_school')->nullable();
            $table->string('guardian_name');
            $table->string('guardian_relationship', 50)->default('Ayah');
            $table->string('guardian_phone', 30);
            $table->string('guardian_email')->nullable();
            $table->text('address')->nullable();
            $table->enum('status', [
                'registered',
                'verified',
                'tested',
                'passed',
                'rejected',
                'enrolled',
            ])->default('registered');
            $table->decimal('test_score_academic', 5, 2)->nullable();
            $table->decimal('test_score_quran', 5, 2)->nullable();
            $table->decimal('test_score_interview', 5, 2)->nullable();
            $table->text('test_notes')->nullable();
            $table->foreignId('converted_student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->dateTime('converted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['admission_batch_id', 'status']);
            $table->index('guardian_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admission_candidates');
        Schema::dropIfExists('admission_batches');
    }
};
