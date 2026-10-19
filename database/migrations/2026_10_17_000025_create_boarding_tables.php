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
        // 1. Gedung Asrama
        Schema::create('dorm_buildings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('gender', ['male', 'female'])->default('male');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Kamar Asrama
        Schema::create('dorm_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dorm_building_id')->constrained('dorm_buildings')->cascadeOnDelete();
            $table->string('room_number');
            $table->unsignedInteger('capacity')->default(8);
            $table->foreignId('musyrif_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['dorm_building_id', 'room_number']);
        });

        // 3. Penempatan Santri di Kamar (Dorm Placement)
        Schema::create('dorm_placements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('dorm_room_id')->constrained('dorm_rooms')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('bed_number', 50)->nullable();
            $table->date('placed_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id']);
        });

        // 4. Perizinan Keluar Santri & Tiket Gate Pass QR
        Schema::create('student_leave_requests', function (Blueprint $table) {
            $table->id();
            $table->string('leave_number', 60)->unique();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->enum('leave_type', ['weekend', 'sick', 'family', 'dispensation'])->default('weekend');
            $table->text('reason');
            $table->dateTime('departure_plan');
            $table->dateTime('return_plan');
            $table->string('pickup_person')->nullable();
            $table->enum('status', [
                'pending',
                'approved_mentor',
                'approved_head',
                'active',
                'returned',
                'late',
                'rejected',
            ])->default('pending');
            $table->string('qr_pass_token', 64)->unique();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('actual_departure_at')->nullable();
            $table->dateTime('actual_returned_at')->nullable();
            $table->foreignId('security_departure_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('security_return_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index('departure_plan');
            $table->index('return_plan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_leave_requests');
        Schema::dropIfExists('dorm_placements');
        Schema::dropIfExists('dorm_rooms');
        Schema::dropIfExists('dorm_buildings');
    }
};
