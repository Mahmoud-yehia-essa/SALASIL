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
        if (Schema::hasTable('company_profiles') && !Schema::hasColumn('company_profiles', 'required_documents')) {
            Schema::table('company_profiles', function (Blueprint $table) {
                $table->json('required_documents')->nullable()->after('rejection_reason');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('company_profiles') && Schema::hasColumn('company_profiles', 'required_documents')) {
            Schema::table('company_profiles', function (Blueprint $table) {
                $table->dropColumn('required_documents');
            });
        }
    }
};
