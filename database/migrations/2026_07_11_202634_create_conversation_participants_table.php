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
        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->id(); // BIGINT, PK, AUTO_INCREMENT
            
            $table->foreignId('conversation_id')->constrained('conversations')->onDelete('cascade'); // BIGINT, FK, NOT NULL
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // BIGINT, FK, NOT NULL
            
            $table->timestamp('last_read_at')->nullable(); // TIMESTAMP, NULLABLE
            $table->timestamp('joined_at')->nullable(); // TIMESTAMP, NULLABLE
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_participants');
    }
};
