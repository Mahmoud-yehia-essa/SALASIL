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
        Schema::create('proof_of_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scheduled_trip_order_id')->nullable()->index();
            $table->unsignedBigInteger('scheduled_trip_id')->nullable()->index();
            $table->unsignedBigInteger('driver_id')->nullable()->index();
            $table->string('receiver_name')->nullable();
            $table->string('receiver_phone')->nullable();
            $table->string('receiver_civil_id')->nullable();
            $table->json('photos')->nullable();
            $table->string('signature_image')->nullable();
            $table->text('notes')->nullable();
            $table->string('delivery_lat')->nullable();
            $table->string('delivery_lng')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->foreign('scheduled_trip_order_id')->references('id')->on('scheduled_trip_orders')->onDelete('cascade');
            $table->foreign('scheduled_trip_id')->references('id')->on('scheduled_trips')->onDelete('set null');
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proof_of_deliveries');
    }
};

