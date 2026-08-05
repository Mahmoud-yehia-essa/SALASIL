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
        Schema::table('shipments', function (Blueprint $table) {
            // 1. تفاصيل الشحنة الأساسية
            if (!Schema::hasColumn('shipments', 'shipment_name')) {
                $table->string('shipment_name')->nullable();
            }
            if (!Schema::hasColumn('shipments', 'shipment_description')) {
                $table->text('shipment_description')->nullable();
            }
            if (!Schema::hasColumn('shipments', 'shipment_type_id')) {
                $table->foreignId('shipment_type_id')->nullable()->constrained('shipment_types')->onDelete('set null');
            }
            if (!Schema::hasColumn('shipments', 'shipment_nature_id')) {
                $table->foreignId('shipment_nature_id')->nullable()->constrained('shipment_natures')->onDelete('set null');
            }

            // 2. الأبعاد (الطول، العرض، الارتفاع)
            if (!Schema::hasColumn('shipments', 'length')) {
                $table->integer('length')->nullable()->comment('L');
            }
            if (!Schema::hasColumn('shipments', 'width')) {
                $table->integer('width')->nullable()->comment('W');
            }
            if (!Schema::hasColumn('shipments', 'height')) {
                $table->integer('height')->nullable()->comment('H');
            }

            // 3. بيانات موقع التحميل (Pickup)
            if (!Schema::hasColumn('shipments', 'pickup_country_id')) {
                $table->foreignId('pickup_country_id')->nullable()->constrained('countries')->onDelete('set null');
            }
            if (!Schema::hasColumn('shipments', 'pickup_city_id')) {
                $table->foreignId('pickup_city_id')->nullable()->constrained('cities')->onDelete('set null');
            }
            if (!Schema::hasColumn('shipments', 'pickup_area')) {
                $table->string('pickup_area')->nullable();
            }
            if (!Schema::hasColumn('shipments', 'pickup_address')) {
                $table->text('pickup_address')->nullable();
            }

            // 4. بيانات موقع التنزيل (Dropoff)
            if (!Schema::hasColumn('shipments', 'dropoff_country_id')) {
                $table->foreignId('dropoff_country_id')->nullable()->constrained('countries')->onDelete('set null');
            }
            if (!Schema::hasColumn('shipments', 'dropoff_city_id')) {
                $table->foreignId('dropoff_city_id')->nullable()->constrained('cities')->onDelete('set null');
            }
            if (!Schema::hasColumn('shipments', 'dropoff_area')) {
                $table->string('dropoff_area')->nullable();
            }
            if (!Schema::hasColumn('shipments', 'dropoff_address')) {
                $table->text('dropoff_address')->nullable();
            }

            // 5. جهات الاتصال
            if (!Schema::hasColumn('shipments', 'loading_contact_name')) {
                $table->string('loading_contact_name')->nullable();
            }
            if (!Schema::hasColumn('shipments', 'loading_contact_phone')) {
                $table->string('loading_contact_phone')->nullable();
            }
            if (!Schema::hasColumn('shipments', 'arrival_contact_name')) {
                $table->string('arrival_contact_name')->nullable();
            }
            if (!Schema::hasColumn('shipments', 'arrival_contact_phone')) {
                $table->string('arrival_contact_phone')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $columnsToDrop = [];

            if (Schema::hasColumn('shipments', 'shipment_type_id')) {
                $table->dropForeign(['shipment_type_id']);
                $columnsToDrop[] = 'shipment_type_id';
            }
            if (Schema::hasColumn('shipments', 'shipment_nature_id')) {
                $table->dropForeign(['shipment_nature_id']);
                $columnsToDrop[] = 'shipment_nature_id';
            }
            if (Schema::hasColumn('shipments', 'pickup_country_id')) {
                $table->dropForeign(['pickup_country_id']);
                $columnsToDrop[] = 'pickup_country_id';
            }
            if (Schema::hasColumn('shipments', 'pickup_city_id')) {
                $table->dropForeign(['pickup_city_id']);
                $columnsToDrop[] = 'pickup_city_id';
            }
            if (Schema::hasColumn('shipments', 'dropoff_country_id')) {
                $table->dropForeign(['dropoff_country_id']);
                $columnsToDrop[] = 'dropoff_country_id';
            }
            if (Schema::hasColumn('shipments', 'dropoff_city_id')) {
                $table->dropForeign(['dropoff_city_id']);
                $columnsToDrop[] = 'dropoff_city_id';
            }

            foreach ([
                'shipment_name',
                'shipment_description',
                'length',
                'width',
                'height',
                'pickup_area',
                'dropoff_area',
                'loading_contact_name',
                'loading_contact_phone',
                'arrival_contact_name',
                'arrival_contact_phone',
            ] as $col) {
                if (Schema::hasColumn('shipments', $col)) {
                    $columnsToDrop[] = $col;
                }
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};