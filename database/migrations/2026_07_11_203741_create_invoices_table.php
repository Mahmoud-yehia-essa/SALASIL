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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id(); // BIGINT, PK, AUTO_INCREMENT
            
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade'); // BIGINT, FK, NOT NULL
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // BIGINT, FK, NOT NULL
            
            $table->string('invoice_number', 50)->unique(); // UNIQUE, NOT NULL
            
            $table->decimal('base_amount', 10, 2); // NOT NULL
            $table->decimal('tax_amount', 10, 2)->default('0.00'); 
            $table->decimal('discount', 10, 2)->default('0.00');
            $table->decimal('total_amount', 10, 2); // NOT NULL
            
            $table->enum('status', ['unpaid', 'partially_paid', 'paid', 'canceled'])->default('unpaid');
            
            $table->timestamp('issued_at')->nullable();
            $table->date('due_date')->nullable();
            
            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
