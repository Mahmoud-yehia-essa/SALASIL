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
    Schema::create('truck_types', function (Blueprint $table) {
        $table->id();
        $table->string('name_ar');
        $table->string('name_en');
        $table->decimal('max_weight', 8, 2)->nullable(); // الوزن الأقصى بالطن
        $table->string('photo')->nullable();
        $table->enum('status', ['active', 'inactive'])->default('active');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('truck_types');
    }
};
