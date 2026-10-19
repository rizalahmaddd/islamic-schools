<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->foreignId('student_bill_id')->constrained('student_bills')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('payer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('payment_channel');
            $table->string('payment_channel_name')->nullable();
            $table->decimal('amount', 12, 2);
            $table->decimal('admin_fee', 12, 2)->default(0);
            $table->decimal('gross_amount', 12, 2);
            $table->string('provider')->default('tripay');
            $table->string('reference')->nullable()->index();
            $table->text('checkout_url')->nullable();
            $table->text('qr_url')->nullable();
            $table->text('qr_string')->nullable();
            $table->string('va_number')->nullable();
            $table->string('status')->default('pending')->index(); // pending, settlement, expired, failed
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('payment_transaction_id')->nullable()->constrained('payment_transactions')->nullOnDelete();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_payment_transactions');
    }
};
