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
       
        Schema::create('shipment_types', function (Blueprint $table) {
            $table->id();
            
            // اسم النوع باللغتين لدعم تعدد اللغات في التطبيق
            $table->string('name_ar', 100); 
            $table->string('name_en', 100); 
            
            // حالة النوع (لتتمكن من إخفاء نوع معين مستقبلاً دون حذفه)
            $table->boolean('is_active')->default(1); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_types');
    }
};
