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
    Schema::create('wallet_transactions', function (Blueprint $table) {
        $table->id();
        
        // ربط العملية بالمستخدم (سواء كان سائقاً أو عميلاً)
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        
        // تفاصيل العملية
        $table->decimal('amount', 10, 2); // قيمة العملية المالي (موجب أو سالب يعتمد على النوع)
        $table->enum('type', ['deposit', 'withdrawal', 'trip_earnings', 'commission_deduction', 'refund']); // نوع العملية
        
        // الحقل السحري الذي تحدثنا عنه! (الرصيد التراكمي بعد العملية)
        $table->decimal('balance_after', 10, 2);
        
        $table->string('description')->nullable(); // وصف للعملية (مثال: أرباح رحلة رقم 15)
        
        // ربط العملية بشحنة معينة (إذا كانت العملية بسبب رحلة)
        $table->foreignId('shipment_id')->nullable()->constrained('shipments')->onDelete('set null');
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
