<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Modul contoh Pelanggan dari starter sudah dihapus; tabelnya hanya ada di database lama.
     */
    public function up(): void
    {
        Schema::dropIfExists('customers');
    }

    public function down(): void
    {
        // Tabel contoh tidak dibuat ulang.
    }
};
