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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id(); // BIGINT, PK, AUTO_INCREMENT
            
            // يمكن أن تكون المحادثة مرتبطة بشحنة أو فارغة (مثلاً للدعم الفني العام)
            $table->foreignId('shipment_id')->nullable()->constrained('shipments')->onDelete('cascade'); // BIGINT, FK, NULLABLE
            
            $table->enum('type', ['direct', 'support'])->default('direct'); // ENUM, DEFAULT 'direct'
            $table->enum('channel', ['in_app', 'whatsapp', 'sms'])->default('in_app'); // ENUM, DEFAULT 'in_app'
            $table->enum('status', ['open', 'closed'])->default('open'); // ENUM, DEFAULT 'open'
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
