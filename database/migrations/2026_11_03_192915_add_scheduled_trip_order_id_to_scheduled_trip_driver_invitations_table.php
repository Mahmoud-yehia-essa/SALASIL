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
        Schema::table('scheduled_trip_driver_invitations', function (Blueprint $table) {
            if (!Schema::hasColumn('scheduled_trip_driver_invitations', 'scheduled_trip_order_id')) {
                $table->unsignedBigInteger('scheduled_trip_order_id')->nullable()->after('id');
                $table->foreign('scheduled_trip_order_id', 'st_driver_inv_order_fk')
                      ->references('id')
                      ->on('scheduled_trip_orders')
                      ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scheduled_trip_driver_invitations', function (Blueprint $table) {
            if (Schema::hasColumn('scheduled_trip_driver_invitations', 'scheduled_trip_order_id')) {
                $table->dropForeign('st_driver_inv_order_fk');
                $table->dropColumn('scheduled_trip_order_id');
            }
        });
    }
};
