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
     
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar', 100); // الاسم بالعربية (مثال: الكويت)
            $table->string('name_en', 100); // الاسم بالإنجليزية (مثال: Kuwait)
            $table->string('code', 10)->nullable(); // كود الدولة (مثال: KW)
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
