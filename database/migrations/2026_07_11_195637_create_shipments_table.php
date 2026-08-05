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
    Schema::create('shipments', function (Blueprint $table) {
        $table->id();
        
        // العلاقات الأساسية
        $table->foreignId('customer_id')->constrained('users')->onDelete('cascade'); // العميل طالب الشحنة
        $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null'); // السائق المنفذ
        $table->foreignId('truck_type_id')->nullable()->constrained('truck_types')->onDelete('set null'); // نوع الشاحنة المطلوبة
        
        // الربط بنظام الرحلات المجدولة (الفكرة التي أضفناها)
        // إذا كان NULL فهذا طلب فوري، وإذا كان به رقم فهذا حجز لرحلة مجدولة
        $table->foreignId('scheduled_trip_id')->nullable()->constrained('scheduled_trips')->onDelete('set null');
        
        // العناوين والإحداثيات
        $table->string('pickup_address');
        $table->string('dropoff_address');
        $table->decimal('pickup_lat', 10, 8)->nullable();
        $table->decimal('pickup_lng', 11, 8)->nullable();
        $table->decimal('dropoff_lat', 10, 8)->nullable();
        $table->decimal('dropoff_lng', 11, 8)->nullable();
        
        // تفاصيل الشحنة
        $table->dateTime('scheduled_date')->nullable(); // تاريخ ووقت التحميل
        $table->text('goods_description')->nullable(); // وصف البضاعة
        $table->decimal('weight', 8, 2)->nullable(); // الوزن التقديري
        
        // الأسعار والحالة

        $table->decimal('initial_price', 10, 2)->nullable();

        $table->enum('status', ['new', 'under_review', 'pending_approval', 'approved', 'rejected', 'canceled'])->default('new');
        $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');
        $table->enum('payment_method', ['cash', 'wallet', 'credit_card'])->nullable();
        

        $table->boolean('is_fragile')->default(0);

        $table->string('loading_contact');
        $table->string('arrival_contact');




        $table->integer('packages_count')->default(1);




        $table->timestamp('driver_arrival_at_loading')->nullable();
            $table->timestamp('loading_start_at')->nullable();
            $table->timestamp('loading_end_at')->nullable();
            $table->timestamp('trip_start_at')->nullable();
            $table->text('delay_reason')->nullable();
            $table->timestamp('unloading_start_at')->nullable();
            $table->timestamp('unloading_end_at')->nullable();



        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
