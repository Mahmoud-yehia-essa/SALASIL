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
        Schema::create('scheduled_trip_driver_invitations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('scheduled_trip_id')->constrained('scheduled_trips')->onDelete('cascade');
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('driver_truck_id')->nullable()->constrained('driver_trucks')->nullOnDelete();

            $table->decimal('offered_price', 10, 2);
            $table->enum('channel', ['in_app', 'whatsapp', 'sms'])->default('in_app');
            $table->enum('status', ['pending', 'accepted', 'rejected', 'canceled'])->default('pending');

            $table->string('token', 64)->unique()->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_trip_driver_invitations');
    }
};

