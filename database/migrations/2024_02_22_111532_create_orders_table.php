<?php

use App\Models\Customer;
use App\Models\Hotel;
use App\Models\Item;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable()->unique();
            $table->string('mode_of_payment')->default('cash');
            $table->string('type')->default('customer');
            $table->integer('total_price')->nullable();
            $table->integer('discount')->nullable();
            $table->integer('quantity')->defult(1);
            $table->foreignIdFor(Customer::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Item::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->default(auth()->id())->constrained('users')->nullOnDelete();
            $table->foreignIdFor(Hotel::class)->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
