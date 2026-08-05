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
        Schema::table('shipment_driver_invitations', function (Blueprint $table) {
            if (!Schema::hasColumn('shipment_driver_invitations', 'driver_truck_id')) {
                $table->foreignId('driver_truck_id')->nullable()->after('driver_id')->constrained('driver_trucks')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipment_driver_invitations', function (Blueprint $table) {
            if (Schema::hasColumn('shipment_driver_invitations', 'driver_truck_id')) {
                $table->dropForeign(['driver_truck_id']);
                $table->dropColumn('driver_truck_id');
            }
        });
    }
};
