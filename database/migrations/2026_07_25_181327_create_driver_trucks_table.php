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
       

        Schema::create('driver_trucks', function (Blueprint $table) {
            $table->id();
            
            // ربط الشاحنة بالسائق
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            
            // النوع الرئيسي (إلزامي)
            $table->foreignId('truck_type_id')->constrained('truck_types')->onDelete('cascade');
            
            // النوع الفرعي (اختياري - لأنه كما ذكرت قد يكتفي السائق بالنوع الرئيسي)
            $table->foreignId('truck_sub_type_id')->nullable()->constrained('truck_sub_types')->onDelete('cascade');
            
            // حقول إضافية مقترحة بقوة لإدارة أسطول السائق
            $table->string('plate_number')->nullable(); // رقم لوحة الشاحنة لتمييزها إذا كان يمتلك شاحنتين من نفس النوع
            $table->boolean('is_default')->default(0); // لتحديد الشاحنة التي يقودها السائق حالياً لاستقبال الطلبات عليها
            $table->boolean('is_verified')->default(0); // هل قامت الإدارة بمراجعة أوراق هذه الشاحنة؟
            
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_trucks');
    }
};
