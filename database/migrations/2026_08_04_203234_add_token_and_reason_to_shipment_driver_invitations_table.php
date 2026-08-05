<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipment_driver_invitations', function (Blueprint $table) {
            // إضافة حقل التوكن المشفر (فريد) لإرساله في روابط الواتساب والرسائل
            $table->string('token', 64)->unique()->nullable()->after('status');
            
            // إضافة حقل سبب الرفض
            $table->text('rejection_reason')->nullable()->after('token');
        });
    }

    public function down(): void
    {
        Schema::table('shipment_driver_invitations', function (Blueprint $table) {
            // حذف الحقول في حال أردت التراجع عن التعديل
            $table->dropColumn(['token', 'rejection_reason']);
        });
    }
};