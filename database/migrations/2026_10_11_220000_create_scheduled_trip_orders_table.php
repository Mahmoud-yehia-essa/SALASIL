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
        // 1. جدول طلبات الرحلات المجدولة منفصل تماماً عن جدول الشحنات العادية
        Schema::create('scheduled_trip_orders', function (Blueprint $table) {
            $table->id();

            // رقم الطلب التعريفي الفريد
            $table->string('order_number')->unique()->nullable();

            // الربط بالرحلة المجدولة الأساسية والعميل
            $table->foreignId('scheduled_trip_id')->constrained('scheduled_trips')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null');

            // الخطوة 2: تفاصيل الشحنة والبضاعة
            $table->string('shipment_name')->nullable();
            $table->text('shipment_description')->nullable();
            $table->foreignId('shipment_type_id')->nullable()->constrained('shipment_types')->onDelete('set null');
            $table->foreignId('shipment_nature_id')->nullable()->constrained('shipment_natures')->onDelete('set null');
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->integer('packages_count')->default(1);
            $table->text('goods_description')->nullable();
            $table->string('hs_code', 50)->nullable();
            $table->text('hs_code_description')->nullable();
            $table->boolean('is_fragile')->default(0);

            // الخطوة 3: مواصفات الشاحنة (مأخوذة من الرحلة المجدولة أو مخصصة)
            $table->foreignId('truck_type_id')->nullable()->constrained('truck_types')->onDelete('set null');
            $table->foreignId('truck_sub_type_id')->nullable()->constrained('truck_sub_types')->onDelete('set null');

            // الخطوة 4: موقع التحميل (Pickup)
            $table->foreignId('pickup_country_id')->nullable()->constrained('countries')->onDelete('set null');
            $table->foreignId('pickup_city_id')->nullable()->constrained('cities')->onDelete('set null');
            $table->string('pickup_area')->nullable();
            $table->text('pickup_address')->nullable();
            $table->decimal('pickup_lat', 10, 8)->nullable();
            $table->decimal('pickup_lng', 11, 8)->nullable();

            // الخطوة 5: موقع التنزيل (Dropoff / Delivery)
            $table->foreignId('dropoff_country_id')->nullable()->constrained('countries')->onDelete('set null');
            $table->foreignId('dropoff_city_id')->nullable()->constrained('cities')->onDelete('set null');
            $table->string('dropoff_area')->nullable();
            $table->text('dropoff_address')->nullable();
            $table->decimal('dropoff_lat', 10, 8)->nullable();
            $table->decimal('dropoff_lng', 11, 8)->nullable();

            // الخطوة 6: جهات الاتصال عند التحميل والتسليم
            $table->string('loading_contact_name')->nullable();
            $table->string('loading_contact_phone')->nullable();
            $table->string('arrival_contact_name')->nullable();
            $table->string('arrival_contact_phone')->nullable();
            $table->string('loading_contact')->nullable();
            $table->string('arrival_contact')->nullable();

            // الخطوة 7: التسعير والكمية/السعة المحجوزة
            $table->decimal('trip_price', 10, 2)->default(0.00); // سعر الوحدة في الرحلة
            $table->integer('booked_capacity')->default(1);        // عدد الوحدات أو الشاحنات المحجوزة
            $table->decimal('initial_price', 10, 2)->nullable();   // السعر الابتدائي / المتفق عليه
            $table->decimal('total_price', 10, 2)->default(0.00);  // السعر الإجمالي النهائي

            // حالات الطلب والدفع
            $table->enum('status', ['new', 'confirmed', 'loaded', 'in_transit', 'delivered', 'canceled'])->default('new');
            $table->enum('payment_status', ['unpaid', 'paid', 'partially_paid'])->default('unpaid');
            $table->string('payment_method')->nullable();

            // ملاحظات
            $table->text('notes')->nullable();

            $table->timestamps();
        });

        // 2. إضافة scheduled_trip_order_id إلى جدول الفواتير لربط الفاتورة بطلب الرحلة المجدولة مباشرة
        if (Schema::hasTable('invoices') && !Schema::hasColumn('invoices', 'scheduled_trip_order_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreignId('scheduled_trip_order_id')
                      ->nullable()
                      ->after('scheduled_trip_id')
                      ->constrained('scheduled_trip_orders')
                      ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'scheduled_trip_order_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropForeign(['scheduled_trip_order_id']);
                $table->dropColumn('scheduled_trip_order_id');
            });
        }

        Schema::dropIfExists('scheduled_trip_orders');
    }
};
