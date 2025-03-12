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
        Schema::table('hotels', function (Blueprint $table) {
            $table->integer('percentage_charge')->nullable()->default([10, 15][array_rand([10, 15])]);
            $table->integer('online_price')->nullable()->default(10);
            $table->integer('non_refundable_price')->nullable()->default(15);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn(['online_price', 'non_refundable_price', 'percentage_charge']);
        });
    }
};
