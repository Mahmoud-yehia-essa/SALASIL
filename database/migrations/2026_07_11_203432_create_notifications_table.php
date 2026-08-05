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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary(); // CHAR(36), PK
            
            $table->string('notifiable_type'); // VARCHAR(255), NOT NULL
            $table->unsignedBigInteger('notifiable_id'); // BIGINT, NOT NULL
            
            $table->string('type'); // VARCHAR(255), NOT NULL
            $table->json('data'); // JSON, NOT NULL
            
            $table->timestamp('read_at')->nullable(); // TIMESTAMP, NULLABLE
            $table->timestamps(); // Standard audit timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
