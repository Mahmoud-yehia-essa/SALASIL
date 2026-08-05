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
            if (!Schema::hasColumn('shipment_driver_invitations', 'offered_price')) {
                $table->decimal('offered_price', 10, 2)->nullable()->after('channel');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipment_driver_invitations', function (Blueprint $table) {
            if (Schema::hasColumn('shipment_driver_invitations', 'offered_price')) {
                $table->dropColumn('offered_price');
            }
        });
    }
};
