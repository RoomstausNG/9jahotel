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
        Schema::table('halls', function (Blueprint $table) {
            $table->bigInteger('deposit')->nullable();
            $table->bigInteger('promo_price')->nullable();
            $table->date('promo_start_day')->nullable();
            $table->date('promo_end_day')->nullable();
            $table->date('reference')->nullable();
            $table->string('number')->nullable();
            $table->string('remarks')->nullable()->default('sellable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropColumns(['deposit', 'reference', 'remarks', 'promo_price', 'promo_start_date', 'promo_end_day']);
    }
};
