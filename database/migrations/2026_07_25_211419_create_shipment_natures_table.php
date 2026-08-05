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
        Schema::create('shipment_natures', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar', 100); // مثلاً: مواد خطرة
            $table->string('name_en', 100); // مثلاً: Hazardous
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_natures');
    }
};
