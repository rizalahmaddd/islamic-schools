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
        // 1. Merchant / Gerai Kantin & Koperasi
        Schema::create('canteen_merchants', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('pic_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Produk Makanan / Minuman / Barang Kantin
        Schema::create('canteen_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('canteen_merchant_id')->constrained('canteen_merchants')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category')->default('makanan'); // makanan, minuman, snack, alat_tulis, lainnya
            $table->decimal('price', 12, 2)->default(0);
            $table->integer('stock')->default(100);
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        // 3. Kartu Smart Card RFID Santri
        Schema::create('rfid_cards', function (Blueprint $table) {
            $table->id();
            $table->string('card_uid')->unique()->index();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('label')->default('Kartu Utama');
            $table->boolean('is_active')->default(true);
            $table->timestamp('blocked_at')->nullable();
            $table->string('blocked_reason')->nullable();
            $table->timestamps();
        });

        // 4. Dompet Digital Santri (E-Wallet)
        Schema::create('student_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained('students')->cascadeOnDelete();
            $table->decimal('balance', 14, 2)->default(0);
            $table->decimal('daily_limit', 14, 2)->default(50000); // Batas maksimal belanja jajan per hari
            $table->string('pin_hash')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. Riwayat Transaksi Dompet (Topup, Purchase, Refund)
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->foreignId('student_wallet_id')->constrained('student_wallets')->cascadeOnDelete();
            $table->foreignId('rfid_card_id')->nullable()->constrained('rfid_cards')->nullOnDelete();
            $table->string('transaction_type'); // topup, purchase, refund
            $table->decimal('amount', 14, 2);
            $table->decimal('balance_before', 14, 2);
            $table->decimal('balance_after', 14, 2);
            $table->foreignId('canteen_merchant_id')->nullable()->constrained('canteen_merchants')->nullOnDelete();
            $table->string('description');
            $table->foreignId('cashier_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 6. Transaksi Belanja Pesanan Kantin
        Schema::create('canteen_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('wallet_transaction_id')->constrained('wallet_transactions')->cascadeOnDelete();
            $table->foreignId('canteen_merchant_id')->nullable()->constrained('canteen_merchants')->nullOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->decimal('total_amount', 14, 2);
            $table->timestamps();
        });

        // 7. Rincian Item Belanja Pesanan Kantin
        Schema::create('canteen_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('canteen_order_id')->constrained('canteen_orders')->cascadeOnDelete();
            $table->foreignId('canteen_product_id')->nullable()->constrained('canteen_products')->nullOnDelete();
            $table->string('product_name');
            $table->integer('quantity');
            $table->decimal('price', 12, 2);
            $table->decimal('subtotal', 14, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('canteen_order_items');
        Schema::dropIfExists('canteen_orders');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('student_wallets');
        Schema::dropIfExists('rfid_cards');
        Schema::dropIfExists('canteen_products');
        Schema::dropIfExists('canteen_merchants');
    }
};
