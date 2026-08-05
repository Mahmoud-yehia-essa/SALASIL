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
    Schema::create('driver_profiles', function (Blueprint $table) {
        $table->id();
        
        // العلاقات
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('truck_type_id')->nullable()->constrained('truck_types')->onDelete('set null');
        
        // المستندات والبيانات
        $table->string('license_number')->nullable();
        $table->string('license_photo')->nullable();
        $table->string('truck_registration_photo')->nullable();
        $table->string('civil_id_photo')->nullable();
        
        // البيانات المالية والتقييم
        $table->decimal('wallet_balance', 10, 2)->default(0.00);
        $table->decimal('rating', 3, 2)->default(5.00);
        
        // الحالات
        $table->enum('availability_status', ['available', 'busy', 'offline'])->default('offline');
        $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
        $table->text('rejection_reason')->nullable();
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_profiles');
    }
};
