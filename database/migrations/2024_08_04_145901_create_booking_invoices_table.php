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
        Schema::create('booking_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->integer('amount');
            $table->string('status')->nullable()->default('unpaid');
            $table->string("type")->nullable()->default('online');
            $table->integer("commission_amount")->nullable()->after("amount");
            $table->integer("hotel_payable")->nullable()->after("commission_amount");
            $table->boolean("is_settled")->nullable()->default(false);
            $table->date("date_settled")->nullable();
            $table->date("checked_in")->nullable();
            $table->date("checked_out")->nullable();
            $table->foreignIdFor(Customer::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Hotel::class)->nullable()->constrained()->cascadeOnDelete();
            $table->date('payed_on')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_invoices');
    }
};