<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            // إضافة معرف الرحلة المجدولة وربطه بجدول الرحلات
            $table->foreignId('scheduled_trip_id')
                  ->nullable()
                  ->after('shipment_id') // وضعه بجوار shipment_id للترتيب
                  ->constrained('scheduled_trips')
                  ->onDelete('set null'); // للحفاظ على السجل المالي في حال حذف الرحلة
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropForeign(['scheduled_trip_id']);
            $table->dropColumn('scheduled_trip_id');
        });
    }
};