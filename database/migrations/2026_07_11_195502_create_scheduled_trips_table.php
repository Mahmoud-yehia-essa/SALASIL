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
    Schema::create('scheduled_trips', function (Blueprint $table) {
        $table->id();
        
        // العلاقات
        $table->foreignId('route_id')->constrained('routes')->onDelete('cascade');
        $table->foreignId('truck_type_id')->constrained('truck_types')->onDelete('cascade');
        $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null'); // ربط بالسائق (من جدول users)
        
        // توقيت الرحلة
        $table->date('trip_date');
        $table->time('trip_time')->nullable();
        
        // السعر والسعة
        $table->decimal('price', 10, 2); // السعر الثابت
        $table->integer('total_capacity')->default(1); // السعة الكلية (1 تعني شاحنة كاملة)
        $table->integer('available_capacity')->default(1); // السعة المتاحة
        
        // حالة الرحلة
        $table->enum('status', ['published', 'boarding', 'in_transit', 'completed', 'canceled'])->default('published');
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_trips');
    }
};
