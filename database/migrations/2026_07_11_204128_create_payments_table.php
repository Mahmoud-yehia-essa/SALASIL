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
        Schema::create('payments', function (Blueprint $table) {
            $table->id(); // BIGINT, PK, AUTO_INCREMENT
            
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade'); // BIGINT, FK, NOT NULL
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // BIGINT, FK, NOT NULL
            
            $table->decimal('amount', 10, 2); // NOT NULL
            $table->enum('payment_method', ['knet', 'credit_card', 'apple_pay', 'bank_transfer', 'cash']); // NOT NULL
            
            $table->string('transaction_id', 255)->nullable(); // Gateway reference number
            $table->string('receipt_url', 255)->nullable(); // Link to PDF receipt
            
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->timestamp('paid_at')->nullable(); // Exact timestamp of payment
            
            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
