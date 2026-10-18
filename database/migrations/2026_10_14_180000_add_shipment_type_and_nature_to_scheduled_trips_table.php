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
        if (Schema::hasTable('scheduled_trips')) {
            Schema::table('scheduled_trips', function (Blueprint $table) {
                if (!Schema::hasColumn('scheduled_trips', 'shipment_type_id')) {
                    $table->unsignedBigInteger('shipment_type_id')->nullable()->after('truck_sub_type_id');
                }
                if (!Schema::hasColumn('scheduled_trips', 'shipment_nature_id')) {
                    $table->unsignedBigInteger('shipment_nature_id')->nullable()->after('shipment_type_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('scheduled_trips')) {
            Schema::table('scheduled_trips', function (Blueprint $table) {
                if (Schema::hasColumn('scheduled_trips', 'shipment_nature_id')) {
                    $table->dropColumn('shipment_nature_id');
                }
                if (Schema::hasColumn('scheduled_trips', 'shipment_type_id')) {
                    $table->dropColumn('shipment_type_id');
                }
            });
        }
    }
};
