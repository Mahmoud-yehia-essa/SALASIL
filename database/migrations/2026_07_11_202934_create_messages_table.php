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
        Schema::create('messages', function (Blueprint $table) {
            $table->id(); // BIGINT, PK, AUTO_INCREMENT
            
            $table->foreignId('conversation_id')->constrained('conversations')->onDelete('cascade'); // BIGINT, FK, NOT NULL
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade'); // BIGINT, FK, NOT NULL
            
            $table->enum('message_type', ['text', 'image', 'audio', 'location', 'file', 'system_alert'])->default('text'); // ENUM, DEFAULT 'text'
            $table->text('content')->nullable(); // TEXT, NULLABLE
            $table->json('metadata')->nullable(); // JSON, NULLABLE
            
            // لدعم الردود المتسلسلة (Nested threaded replies)
            $table->foreignId('reply_to_id')->nullable()->constrained('messages')->onDelete('set null'); // BIGINT, FK, NULLABLE
            
            $table->boolean('is_read')->default(0); // BOOLEAN, DEFAULT 0
            
            $table->string('external_message_id', 255)->nullable(); // VARCHAR(255), NULLABLE
            $table->enum('delivery_status', ['sent', 'delivered', 'read', 'failed'])->nullable(); // ENUM, NULLABLE
            
            $table->softDeletes(); // يضيف حقل deleted_at (TIMESTAMP, NULLABLE) المطلوب في المستند لدعم ميزة "حذف للجميع"
            $table->timestamps(); // لإضافة created_at و updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
