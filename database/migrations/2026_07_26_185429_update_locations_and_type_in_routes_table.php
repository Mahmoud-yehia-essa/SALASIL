<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            // 1. إضافة نوع التسعير (Local / International)
            $table->enum('quote_type', ['local', 'international'])->default('local')->after('id');

            // 2. إضافة المعرفات الجديدة (Foreign Keys)
            $table->foreignId('origin_country_id')->nullable()->after('quote_type')->constrained('countries')->onDelete('cascade');
            $table->foreignId('origin_city_id')->nullable()->after('origin_country_id')->constrained('cities')->onDelete('cascade');
            
            $table->foreignId('destination_country_id')->nullable()->after('origin_city_id')->constrained('countries')->onDelete('cascade');
            $table->foreignId('destination_city_id')->nullable()->after('destination_country_id')->constrained('cities')->onDelete('cascade');

            // 3. حذف الحقول النصية القديمة
            $table->dropColumn([
                'origin_country',
                'origin_city',
                'destination_country',
                'destination_city'
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            // استرجاع الحقول النصية في حال أردت التراجع عن التحديث
            $table->string('origin_country', 100)->nullable();
            $table->string('origin_city', 100)->nullable();
            $table->string('destination_country', 100)->nullable();
            $table->string('destination_city', 100)->nullable();

            // حذف العلاقات والحقول الجديدة
            $table->dropForeign(['origin_country_id']);
            $table->dropForeign(['origin_city_id']);
            $table->dropForeign(['destination_country_id']);
            $table->dropForeign(['destination_city_id']);
            
            $table->dropColumn([
                'quote_type',
                'origin_country_id',
                'origin_city_id',
                'destination_country_id',
                'destination_city_id'
            ]);
        });
    }
};