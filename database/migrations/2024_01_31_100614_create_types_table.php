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
        Schema::create('types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->bigInteger('price1')->nullable();
            $table->bigInteger('price2')->nullable();
            $table->bigInteger('deposit1')->nullable();
            $table->bigInteger('deposit2')->nullable();
            $table->bigInteger('weekend_price')->nullable();
            $table->string('weekend_start_day')->nullable();
            $table->string('weekend_end_day')->nullable();
            $table->bigInteger('promo_price')->nullable();
            $table->date('promo_start_day')->nullable();
            $table->date('promo_end_day')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignIdFor(Hotel::class)->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('types');
    }
};