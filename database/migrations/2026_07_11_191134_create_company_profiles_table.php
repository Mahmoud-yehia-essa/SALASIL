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
    Schema::create('company_profiles', function (Blueprint $table) {
        $table->id();
        
        // الربط بجدول المستخدمين وحذف الحساب المرتبط تلقائياً في حال حذف المستخدم
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        
        // بيانات الشركة
        $table->string('company_name');
        $table->string('commercial_register', 100);
        $table->string('commercial_register_doc')->nullable();
        $table->string('civil_id', 50);
        $table->string('tax_number', 100)->nullable();
        
        // بيانات ممثل الشركة
        $table->string('representative_name')->nullable();
        $table->string('representative_position')->nullable();
        $table->string('representative_phone', 100)->nullable();
        
        // حالة التوثيق من الإدارة
        $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
        $table->text('rejection_reason')->nullable();
        
        $table->timestamps();
    });
}




    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_profiles');
    }
};
