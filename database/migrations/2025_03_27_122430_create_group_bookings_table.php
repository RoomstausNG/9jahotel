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
        Schema::create('group_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('organisation')->nullable();
            $table->unsignedInteger('expected_guest');
            $table->foreignId('city_id')->constrained('cities');
            $table->unsignedInteger('room_needed')->nullable();
            $table->string('room_type')->nullable();
            $table->date('room_from')->nullable();
            $table->date('room_to')->nullable();
            $table->decimal('room_budget', 15, 2)->nullable();
            $table->boolean('need_hall')->default(false);
            $table->unsignedInteger('hall_capacity')->nullable();
            $table->decimal('hall_budget', 15, 2)->nullable();
            $table->date('hall_from')->nullable();
            $table->date('hall_to')->nullable();
            $table->boolean('need_meal')->default(false);
            $table->unsignedInteger('meal_attendees')->nullable();
            $table->decimal('meal_budget', 15, 2)->nullable();
            $table->json('meal_options')->nullable();
            $table->date('meal_from')->nullable();
            $table->date('meal_to')->nullable();
            $table->json('hotels')->nullable();
            $table->boolean('allow_external_bids')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_bookings');
    }
};
