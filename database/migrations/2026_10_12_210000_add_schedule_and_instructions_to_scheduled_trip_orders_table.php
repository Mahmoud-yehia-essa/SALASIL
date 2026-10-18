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
            // Step 4 — Pickup Schedule & Instructions
            if (!Schema::hasColumn('scheduled_trip_orders', 'loading_date')) {
                $table->date('loading_date')->nullable()->after('pickup_lng');
            }
            if (!Schema::hasColumn('scheduled_trip_orders', 'loading_time')) {
                $table->string('loading_time', 20)->nullable()->after('loading_date');
            }
            if (!Schema::hasColumn('scheduled_trip_orders', 'loading_time_slot')) {
                $table->string('loading_time_slot', 100)->nullable()->after('loading_time');
            }
            if (!Schema::hasColumn('scheduled_trip_orders', 'pickup_instructions')) {
                $table->text('pickup_instructions')->nullable()->after('loading_time_slot');
            }

            // Step 5 — Delivery Schedule & Instructions
            if (!Schema::hasColumn('scheduled_trip_orders', 'receiving_date')) {
                $table->date('receiving_date')->nullable()->after('dropoff_lng');
            }
            if (!Schema::hasColumn('scheduled_trip_orders', 'receiving_time')) {
                $table->string('receiving_time', 20)->nullable()->after('receiving_date');
            }
            if (!Schema::hasColumn('scheduled_trip_orders', 'receiving_time_slot')) {
                $table->string('receiving_time_slot', 100)->nullable()->after('receiving_time');
            }
            if (!Schema::hasColumn('scheduled_trip_orders', 'delivery_instructions')) {
                $table->text('delivery_instructions')->nullable()->after('receiving_time_slot');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scheduled_trip_orders', function (Blueprint $table) {
            $cols = [
                'loading_date',
                'loading_time',
                'loading_time_slot',
                'pickup_instructions',
                'receiving_date',
                'receiving_time',
                'receiving_time_slot',
                'delivery_instructions',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('scheduled_trip_orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
