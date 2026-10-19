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
        Schema::create('student_daily_worships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('fajr', 20)->default('jamaah'); // jamaah, munfarid, missed, excused
            $table->string('dhuhr', 20)->default('jamaah');
            $table->string('asr', 20)->default('jamaah');
            $table->string('maghrib', 20)->default('jamaah');
            $table->string('isha', 20)->default('jamaah');
            $table->boolean('dhuha')->default(false);
            $table->boolean('tahajjud')->default(false);
            $table->unsignedSmallInteger('tilawah_pages')->default(0);
            $table->string('fasting_type', 30)->default('none'); // none, sunnah_monday_thursday, sunnah_ayyamul_bidh, ramadhan, other
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['student_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_daily_worships');
    }
};
