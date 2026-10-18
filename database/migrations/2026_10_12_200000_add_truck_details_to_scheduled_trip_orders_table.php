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
        Schema::table('scheduled_trip_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('scheduled_trip_orders', 'number_of_trucks')) {
                $table->integer('number_of_trucks')->default(1)->after('truck_sub_type_id');
            }
            if (!Schema::hasColumn('scheduled_trip_orders', 'special_equipment')) {
                $table->text('special_equipment')->nullable()->after('number_of_trucks');
            }
            if (!Schema::hasColumn('scheduled_trip_orders', 'truck_notes')) {
                $table->text('truck_notes')->nullable()->after('special_equipment');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scheduled_trip_orders', function (Blueprint $table) {
            if (Schema::hasColumn('scheduled_trip_orders', 'truck_notes')) {
                $table->dropColumn('truck_notes');
            }
            if (Schema::hasColumn('scheduled_trip_orders', 'special_equipment')) {
                $table->dropColumn('special_equipment');
            }
            if (Schema::hasColumn('scheduled_trip_orders', 'number_of_trucks')) {
                $table->dropColumn('number_of_trucks');
            }
        });
    }
};
