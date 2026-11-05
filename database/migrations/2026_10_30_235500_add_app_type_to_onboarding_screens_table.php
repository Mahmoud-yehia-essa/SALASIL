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
        Schema::table('onboarding_screens', function (Blueprint $table) {
            if (!Schema::hasColumn('onboarding_screens', 'app_type')) {
                $table->enum('app_type', ['customer', 'driver', 'both'])
                      ->default('customer')
                      ->after('image');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('onboarding_screens', function (Blueprint $table) {
            if (Schema::hasColumn('onboarding_screens', 'app_type')) {
                $table->dropColumn('app_type');
            }
        });
    }
};

