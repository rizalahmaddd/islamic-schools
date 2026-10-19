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
        Schema::create('student_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_assignment_id')->constrained('subject_assignments')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->decimal('formative_average', 5, 2)->default(0.00);
            $table->decimal('mid_term_score', 5, 2)->default(0.00);
            $table->decimal('final_term_score', 5, 2)->default(0.00);
            $table->decimal('final_score', 5, 2)->default(0.00);
            $table->string('predicate', 5)->default('C');
            $table->text('achievement_desc')->nullable();
            $table->text('improvement_desc')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['subject_assignment_id', 'student_id'], 'uq_grade_assignment_student');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_grades');
    }
};
