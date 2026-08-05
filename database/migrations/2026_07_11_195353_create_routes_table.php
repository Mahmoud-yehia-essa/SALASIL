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
    Schema::create('routes', function (Blueprint $table) {
        $table->id();
        $table->string('origin_country');
        $table->string('origin_city');
        $table->string('destination_country');
        $table->string('destination_city');
        $table->decimal('estimated_distance', 8, 2)->nullable(); // المسافة التقريبية بالكيلومتر
        $table->enum('status', ['active', 'inactive'])->default('active');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
