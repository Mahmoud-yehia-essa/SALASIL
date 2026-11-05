<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to support Salasil Driver Figma fields.
     */
    public function up(): void
    {
        Schema::table('driver_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('driver_profiles', 'passport_photo')) {
                $table->string('passport_photo')->nullable()->after('civil_id_photo');
            }
            if (!Schema::hasColumn('driver_profiles', 'plate_number')) {
                $table->string('plate_number')->nullable()->after('license_number');
            }
            if (!Schema::hasColumn('driver_profiles', 'model_year')) {
                $table->string('model_year')->nullable()->after('plate_number');
            }
            if (!Schema::hasColumn('driver_profiles', 'administrative_number')) {
                $table->string('administrative_number')->nullable()->after('model_year');
            }
            if (!Schema::hasColumn('driver_profiles', 'is_main_truck')) {
                $table->boolean('is_main_truck')->default(1)->after('administrative_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_profiles', function (Blueprint $table) {
            $cols = ['passport_photo', 'plate_number', 'model_year', 'administrative_number', 'is_main_truck'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('driver_profiles', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

