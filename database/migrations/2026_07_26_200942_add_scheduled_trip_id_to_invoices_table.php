<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // إضافة معرف الرحلة المجدولة وربطه بالجدول الخاص بها
            $table->foreignId('scheduled_trip_id')
                  ->nullable()
                  ->after('shipment_id') // وضعه بجانب معرف الشحنة العادية لترتيب الجدول
                  ->constrained('scheduled_trips')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['scheduled_trip_id']);
            $table->dropColumn('scheduled_trip_id');
        });
    }
};