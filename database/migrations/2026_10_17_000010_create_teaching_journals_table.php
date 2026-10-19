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
        Schema::create('teaching_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->date('date');
            $table->unsignedSmallInteger('period_start')->default(1);
            $table->unsignedSmallInteger('period_end')->default(2);
            $table->string('topic_taught');
            $table->text('learning_activities')->nullable();
            $table->text('notes')->nullable();
            $table->string('evidence_photo_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['teacher_id', 'date']);
            $table->index(['class_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teaching_journals');
    }
};
