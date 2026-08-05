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
       Schema::create('truck_sub_types', function (Blueprint $table) {
            $table->id();
            
            // الربط بالجدول الرئيسي
            $table->foreignId('truck_type_id')->constrained('truck_types')->onDelete('cascade');
            
            $table->string('name_ar', 100); // الاسم بالعربية (مثل: سطحة، مبردة)
            $table->string('name_en', 100); // الاسم بالإنجليزية (مثل: Flatbed, Reefer)
            
            // يمكنك إضافة وزن مخصص للنوع الفرعي في حال كان يختلف عن الرئيسي
            $table->decimal('max_payload', 10, 2)->nullable(); 
            
            $table->boolean('is_active')->default(1);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('truck_sub_types');
    }
};
