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
        if (Schema::hasTable('scheduled_trips') && !Schema::hasColumn('scheduled_trips', 'pricing_additions')) {
            Schema::table('scheduled_trips', function (Blueprint $table) {
                $table->json('pricing_additions')->nullable()->after('price');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('scheduled_trips') && Schema::hasColumn('scheduled_trips', 'pricing_additions')) {
            Schema::table('scheduled_trips', function (Blueprint $table) {
                $table->dropColumn('pricing_additions');
            });
        }
    }
};
