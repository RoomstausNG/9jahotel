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
        Schema::create('booking_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hotel_id');
            $table->unsignedBigInteger('booking_id');
            $table->string('guest_name');
            $table->date('check_in');
            $table->date('check_out');
            $table->string('room_type')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->enum('source', ['web_app', 'mobile_app']);
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['hotel_id', 'is_read']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_notifications');
    }
};
