<?php

use App\Models\Coupon;
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
        Schema::table('booking_invoices', function (Blueprint $table) {
            $table->foreignIdFor(Coupon::class)->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_invoices', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropColumn('coupon_id');
        });
    }
};
