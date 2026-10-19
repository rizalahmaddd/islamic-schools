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
        Schema::create('report_card_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->unsignedInteger('sick_count')->default(0);
            $table->unsignedInteger('permit_count')->default(0);
            $table->unsignedInteger('absent_count')->default(0);
            $table->text('extracurricular_notes')->nullable();
            $table->text('homeroom_notes')->nullable();
            $table->string('promotion_status', 50)->nullable();
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['student_id', 'class_id', 'academic_year_id'], 'uq_report_student_class_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_card_summaries');
    }
};
