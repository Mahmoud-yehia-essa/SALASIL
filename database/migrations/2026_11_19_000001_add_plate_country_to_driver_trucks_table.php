<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_trucks', function (Blueprint $table) {
            if (!Schema::hasColumn('driver_trucks', 'plate_country_id')) {
                $table->foreignId('plate_country_id')
                    ->nullable()
                    ->after('plate_number')
                    ->constrained('countries')
                    ->nullOnDelete();
            }
            if (!Schema::hasColumn('driver_trucks', 'plate_country_code')) {
                $table->string('plate_country_code', 10)
                    ->nullable()
                    ->after('plate_country_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('driver_trucks', function (Blueprint $table) {
            if (Schema::hasColumn('driver_trucks', 'plate_country_id')) {
                $table->dropForeign(['plate_country_id']);
                $table->dropColumn('plate_country_id');
            }
            if (Schema::hasColumn('driver_trucks', 'plate_country_code')) {
                $table->dropColumn('plate_country_code');
            }
        });
    }
};
