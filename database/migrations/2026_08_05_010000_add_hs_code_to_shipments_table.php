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
        Schema::table('shipments', function (Blueprint $table) {
            if (!Schema::hasColumn('shipments', 'hs_code')) {
                $table->string('hs_code', 50)->nullable()->after('goods_description');
            }
            if (!Schema::hasColumn('shipments', 'hs_code_description')) {
                $table->text('hs_code_description')->nullable()->after('hs_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            if (Schema::hasColumn('shipments', 'hs_code')) {
                $table->dropColumn(['hs_code', 'hs_code_description']);
            }
        });
    }
};
