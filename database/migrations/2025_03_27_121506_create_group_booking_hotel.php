<?php

use App\Models\GroupBooking;
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
        Schema::create('group_booking_hotel', function (Blueprint $table) {
            $table->foreignIdFor(GroupBooking::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Hotel::class)->nullable()->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_booking_hotel');
    }
};