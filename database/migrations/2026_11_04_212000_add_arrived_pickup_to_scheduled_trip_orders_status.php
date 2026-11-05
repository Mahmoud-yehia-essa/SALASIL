<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE scheduled_trip_orders MODIFY COLUMN status ENUM('new','confirmed','arrived_pickup','loaded','in_transit','arrived_dropoff','delivered','canceled') DEFAULT 'new'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE scheduled_trip_orders MODIFY COLUMN status ENUM('new','confirmed','loaded','in_transit','arrived_dropoff','delivered','canceled') DEFAULT 'new'");
    }
};

