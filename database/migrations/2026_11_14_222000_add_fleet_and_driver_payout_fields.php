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
        Schema::table('scheduled_trips', function (Blueprint $table) {
            if (!Schema::hasColumn('scheduled_trips', 'driver_price')) {
                $table->decimal('driver_price', 10, 2)->nullable()->after('price');
            }
            if (!Schema::hasColumn('scheduled_trips', 'driver_payment_type')) {
                $table->enum('driver_payment_type', ['full_after', 'split_50_50'])
                    ->default('full_after')
                    ->after('driver_price');
            }
            if (!Schema::hasColumn('scheduled_trips', 'fleet_override')) {
                $table->boolean('fleet_override')->default(false)->after('driver_payment_type');
            }
        });

        Schema::table('scheduled_trip_driver_invitations', function (Blueprint $table) {
            if (!Schema::hasColumn('scheduled_trip_driver_invitations', 'driver_stage')) {
                $table->string('driver_stage', 40)->nullable()->after('status');
            }
            if (!Schema::hasColumn('scheduled_trip_driver_invitations', 'accepted_at')) {
                $table->timestamp('accepted_at')->nullable()->after('driver_stage');
            }
            if (!Schema::hasColumn('scheduled_trip_driver_invitations', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('accepted_at');
            }
            if (!Schema::hasColumn('scheduled_trip_driver_invitations', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('started_at');
            }
            if (!Schema::hasColumn('scheduled_trip_driver_invitations', 'upfront_paid_at')) {
                $table->timestamp('upfront_paid_at')->nullable()->after('completed_at');
            }
            if (!Schema::hasColumn('scheduled_trip_driver_invitations', 'final_paid_at')) {
                $table->timestamp('final_paid_at')->nullable()->after('upfront_paid_at');
            }
        });

        Schema::table('proof_of_deliveries', function (Blueprint $table) {
            if (!Schema::hasColumn('proof_of_deliveries', 'scheduled_trip_invitation_id')) {
                $table->unsignedBigInteger('scheduled_trip_invitation_id')
                    ->nullable()
                    ->after('scheduled_trip_order_id')
                    ->index();
            }
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('wallet_transactions', 'scheduled_trip_order_id')) {
                $table->unsignedBigInteger('scheduled_trip_order_id')
                    ->nullable()
                    ->after('scheduled_trip_id')
                    ->index();
            }
            if (!Schema::hasColumn('wallet_transactions', 'scheduled_trip_invitation_id')) {
                $table->unsignedBigInteger('scheduled_trip_invitation_id')
                    ->nullable()
                    ->after('scheduled_trip_order_id')
                    ->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            foreach (['scheduled_trip_invitation_id', 'scheduled_trip_order_id'] as $col) {
                if (Schema::hasColumn('wallet_transactions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('proof_of_deliveries', function (Blueprint $table) {
            if (Schema::hasColumn('proof_of_deliveries', 'scheduled_trip_invitation_id')) {
                $table->dropColumn('scheduled_trip_invitation_id');
            }
        });

        Schema::table('scheduled_trip_driver_invitations', function (Blueprint $table) {
            foreach (['final_paid_at', 'upfront_paid_at', 'completed_at', 'started_at', 'accepted_at', 'driver_stage'] as $col) {
                if (Schema::hasColumn('scheduled_trip_driver_invitations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('scheduled_trips', function (Blueprint $table) {
            foreach (['fleet_override', 'driver_payment_type', 'driver_price'] as $col) {
                if (Schema::hasColumn('scheduled_trips', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
