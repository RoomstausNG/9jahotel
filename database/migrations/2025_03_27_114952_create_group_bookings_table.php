<?php

use App\Models\Hotel;
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
            $table->string("name");
            $table->string("phone");
            $table->string("email")->nullable();
            $table->string("organisation")->nullable();
            $table->integer("expected_guest");
            $table->integer("room_needed")->nullable();
            $table->string("room_type")->nullable();
            $table->date("room_from")->nullable();
            $table->date("room_to")->nullable();
            $table->integer("room_budget")->nullable();
            $table->boolean("need_hall")->default(false);
            $table->integer("hall_capacity")->nullable();
            $table->integer("hall_budget")->nullable();
            $table->boolean("need_meal")->default(false);
            $table->integer("meal_budget")->nullable();
            $table->integer("meal_attendees")->nullable();
            $table->json("meal_options")->nullable();
            $table->date("hall_from")->nullable();
            $table->date("hall_to")->nullable();
            $table->date("meal_from")->nullable();
            $table->date("meal_to")->nullable();
            $table->json('hotels');
            $table->boolean('allow_external_bids');
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