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
                if (!Schema::hasColumn('scheduled_trips', 'customs_clearance')) {
                    $table->boolean('customs_clearance')->default(true)->after('status');
                }
                if (!Schema::hasColumn('scheduled_trips', 'notes')) {
                    $table->text('notes')->nullable()->after('customs_clearance');
                }
            });
        }

        if (Schema::hasTable('scheduled_trip_orders')) {
            Schema::table('scheduled_trip_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('scheduled_trip_orders', 'customs_clearance')) {
                    $table->boolean('customs_clearance')->default(true)->after('status');
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
                if (Schema::hasColumn('scheduled_trips', 'customs_clearance')) {
                    $table->dropColumn('customs_clearance');
                }
                if (Schema::hasColumn('scheduled_trips', 'notes')) {
                    $table->dropColumn('notes');
                }
            });
        }

        if (Schema::hasTable('scheduled_trip_orders')) {
            Schema::table('scheduled_trip_orders', function (Blueprint $table) {
                if (Schema::hasColumn('scheduled_trip_orders', 'customs_clearance')) {
                    $table->dropColumn('customs_clearance');
                }
            });
        }
    }
};
