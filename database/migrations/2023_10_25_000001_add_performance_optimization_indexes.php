<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPerformanceOptimizationIndexes extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes for performance optimization on hotels table
        Schema::table('hotels', function (Blueprint $table) {
            $table->index('name');
            $table->index('city_id');
            $table->index('email');
            $table->index('status');
            $table->index('created_at');
        });

        // Add indexes for performance optimization on bookings table
        Schema::table('bookings', function (Blueprint $table) {
            $table->index('hotel_id');
            $table->index('customer_id');
            $table->index('checkin');
            $table->index('checkout');
            $table->index('status');
        });

        // Add indexes for performance optimization on users table
        Schema::table('users', function (Blueprint $table) {
            $table->index('email');
            $table->index('firstname');
            $table->index('lastname');
        });

        // Add indexes for performance optimization on customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->index('email');
            $table->index('phone');
        });

        // Add any other commonly queried fields as needed
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the indexes in reverse order
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['city_id']);
            $table->dropIndex(['email']);
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['hotel_id']);
            $table->dropIndex(['customer_id']);
            $table->dropIndex(['checkin']);
            $table->dropIndex(['checkout']);
            $table->dropIndex(['status']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['firstname']);
            $table->dropIndex(['lastname']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['phone']);
        });
    }
}
