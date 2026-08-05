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
        Schema::table('shipment_tracking_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('shipment_tracking_logs', 'shipment_id')) {
                $table->foreignId('shipment_id')->after('id')->nullable()->constrained('shipments')->onDelete('cascade');
            }
            if (!Schema::hasColumn('shipment_tracking_logs', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('shipment_id');
            }
            if (!Schema::hasColumn('shipment_tracking_logs', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('shipment_tracking_logs', 'is_stop_point')) {
                $table->boolean('is_stop_point')->default(false)->after('longitude');
            }
            if (!Schema::hasColumn('shipment_tracking_logs', 'speed')) {
                $table->decimal('speed', 5, 2)->nullable()->after('is_stop_point');
            }
            if (!Schema::hasColumn('shipment_tracking_logs', 'heading')) {
                $table->integer('heading')->nullable()->after('speed');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipment_tracking_logs', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('shipment_tracking_logs', 'heading')) $columns[] = 'heading';
            if (Schema::hasColumn('shipment_tracking_logs', 'speed')) $columns[] = 'speed';
            if (Schema::hasColumn('shipment_tracking_logs', 'is_stop_point')) $columns[] = 'is_stop_point';
            if (Schema::hasColumn('shipment_tracking_logs', 'longitude')) $columns[] = 'longitude';
            if (Schema::hasColumn('shipment_tracking_logs', 'latitude')) $columns[] = 'latitude';
            if (Schema::hasColumn('shipment_tracking_logs', 'shipment_id')) {
                $table->dropForeign(['shipment_id']);
                $columns[] = 'shipment_id';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
