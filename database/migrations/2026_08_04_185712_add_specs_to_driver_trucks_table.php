<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_trucks', function (Blueprint $table) {
            // 1. الماركة والموديل
            $table->foreignId('truck_brand_id')->nullable()->after('truck_sub_type_id')->constrained('truck_brands')->onDelete('set null');
            $table->foreignId('truck_model_id')->nullable()->after('truck_brand_id')->constrained('truck_models')->onDelete('set null');
            
            // 2. سنة الصنع
            $table->year('manufacturing_year')->nullable()->after('truck_model_id');
            
            // 3. عدد المحاور (Axle #)
            $table->integer('axles_count')->nullable()->comment('Axle #')->after('manufacturing_year');
        });
    }

    public function down(): void
    {
        Schema::table('driver_trucks', function (Blueprint $table) {
            $table->dropForeign(['truck_brand_id']);
            $table->dropForeign(['truck_model_id']);
            
            $table->dropColumn([
                'truck_brand_id',
                'truck_model_id',
                'manufacturing_year',
                'axles_count'
            ]);
        });
    }
};