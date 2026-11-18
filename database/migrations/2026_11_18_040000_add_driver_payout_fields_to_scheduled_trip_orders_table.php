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
        if (Schema::hasTable('scheduled_trip_orders')) {
            Schema::table('scheduled_trip_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('scheduled_trip_orders', 'driver_price')) {
                    $table->decimal('driver_price', 10, 2)->nullable()->after('trip_price');
                }
                if (!Schema::hasColumn('scheduled_trip_orders', 'driver_payment_type')) {
                    $table->enum('driver_payment_type', ['full_after', 'split_50_50'])
                        ->nullable()
                        ->default('full_after')
                        ->after('driver_price');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('scheduled_trip_orders')) {
            Schema::table('scheduled_trip_orders', function (Blueprint $table) {
                foreach (['driver_payment_type', 'driver_price'] as $col) {
                    if (Schema::hasColumn('scheduled_trip_orders', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
