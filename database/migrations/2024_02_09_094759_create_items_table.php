<?php

use App\Models\Hotel;
use App\Models\Revenue;
use App\Models\GeneralItem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->bigInteger('cost_price')->nullable();
            $table->bigInteger('price');
            $table->bigInteger('quantity');
            $table->bigInteger('discount')->nullable();
            $table->integer('limit')->nullable();
            $table->string('image')->nullable();
            $table->foreignIdFor(GeneralItem::class)->nullable()->constrained('general_items')->nullOnDelete();
            $table->foreignIdFor(Revenue::class)->nullable()->constrained('revenues')->nullOnDelete();
            $table->foreignIdFor(Hotel::class)->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};