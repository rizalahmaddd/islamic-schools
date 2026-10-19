<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->date('enrolled_at')->nullable();
            $table->string('status')->default('active'); // active, transferred, completed
            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id'], 'student_academic_year_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};
