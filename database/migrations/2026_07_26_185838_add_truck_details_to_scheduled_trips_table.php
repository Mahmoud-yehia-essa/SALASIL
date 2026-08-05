<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scheduled_trips', function (Blueprint $table) {
            // 1. إضافة النوع الفرعي للشاحنة (يكون اختياري nullable في حال لم يكن للنوع الرئيسي تصنيفات فرعية)
            $table->foreignId('truck_sub_type_id')->nullable()->after('truck_type_id')->constrained('truck_sub_types')->onDelete('set null');
            
            // 2. إضافة عدد الشاحنات (الافتراضي 1)
            $table->integer('number_of_trucks')->default(1)->after('truck_sub_type_id');
            
            // 3. إضافة الوزن الإجمالي التقريبي بالطن
            $table->decimal('total_weight_ton', 10, 2)->nullable()->comment('Total Weight (approx.) Ton')->after('number_of_trucks');
        });
    }

    public function down(): void
    {
        Schema::table('scheduled_trips', function (Blueprint $table) {
            $table->dropForeign(['truck_sub_type_id']);
            
            $table->dropColumn([
                'truck_sub_type_id',
                'number_of_trucks',
                'total_weight_ton'
            ]);
        });
    }
};