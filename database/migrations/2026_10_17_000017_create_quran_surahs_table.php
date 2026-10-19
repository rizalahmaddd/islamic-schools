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
        Schema::create('quran_surahs', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('number')->unique();
            $table->string('name_arabic');
            $table->string('name_latin');
            $table->unsignedSmallInteger('total_verses');
            $table->unsignedTinyInteger('juz_start');
            $table->string('revelation_type', 20)->default('meccan'); // meccan, medinan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quran_surahs');
    }
};
