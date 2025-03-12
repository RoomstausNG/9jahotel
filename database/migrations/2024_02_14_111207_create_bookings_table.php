<?php

use App\Models\Customer;
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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->integer('bookable_id');
            $table->string('bookable_type');
            $table->bigInteger('due')->nullable();
            $table->bigInteger('discount')->nullable();
            $table->bigInteger('amount_paid')->nullable()->default(0);
            $table->date('checked_in');
            $table->date('checked_out');
            $table->string('status')->nullable()->default('booked');
            $table->string('type')->nullable()->default('room');
            $table->foreignId('agent_id')->nullable()->default(auth()->id())->constrained('users')->nullOnDelete();
            $table->foreignIdFor(Customer::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Hotel::class)->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
