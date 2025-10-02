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
            if (!Schema::hasIndex('hotels', 'hotels_name_index')) {
                $table->index('name');
            }
            if (!Schema::hasIndex('hotels', 'hotels_city_id_index')) {
                $table->index('city_id');
            }
            if (!Schema::hasIndex('hotels', 'hotels_email_index')) {
                $table->index('email');
            }
            if (!Schema::hasIndex('hotels', 'hotels_status_index')) {
                $table->index('status');
            }
            if (!Schema::hasIndex('hotels', 'hotels_created_at_index')) {
                $table->index('created_at');
            }
        });

        // Add indexes for performance optimization on bookings table
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasIndex('bookings', 'bookings_hotel_id_index')) {
                $table->index('hotel_id');
            }
            if (!Schema::hasIndex('bookings', 'bookings_customer_id_index')) {
                $table->index('customer_id');
            }
            if (!Schema::hasIndex('bookings', 'bookings_checked_in_index')) {
                $table->index('checked_in');
            }
            if (!Schema::hasIndex('bookings', 'bookings_checked_out_index')) {
                $table->index('checked_out');
            }
            if (!Schema::hasIndex('bookings', 'bookings_status_index')) {
                $table->index('status');
            }
        });

        // Add indexes for performance optimization on users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasIndex('users', 'users_email_index')) {
                $table->index('email');
            }
            if (!Schema::hasIndex('users', 'users_firstname_index')) {
                $table->index('firstname');
            }
            if (!Schema::hasIndex('users', 'users_lastname_index')) {
                $table->index('lastname');
            }
        });

        // Add indexes for performance optimization on customers table
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasIndex('customers', 'customers_email_index')) {
                $table->index('email');
            }
            if (!Schema::hasIndex('customers', 'customers_phone_index')) {
                $table->index('phone');
            }
        });

        // Add any other commonly queried fields as needed
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the indexes in reverse order, checking if they exist
        Schema::table('hotels', function (Blueprint $table) {
            if (Schema::hasIndex('hotels', 'hotels_name_index')) {
                $table->dropIndex(['name']);
            }
            if (Schema::hasIndex('hotels', 'hotels_city_id_index')) {
                $table->dropIndex(['city_id']);
            }
            if (Schema::hasIndex('hotels', 'hotels_email_index')) {
                $table->dropIndex(['email']);
            }
            if (Schema::hasIndex('hotels', 'hotels_status_index')) {
                $table->dropIndex(['status']);
            }
            if (Schema::hasIndex('hotels', 'hotels_created_at_index')) {
                $table->dropIndex(['created_at']);
            }
        });

        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasIndex('bookings', 'bookings_hotel_id_index')) {
                $table->dropIndex(['hotel_id']);
            }
            if (Schema::hasIndex('bookings', 'bookings_customer_id_index')) {
                $table->dropIndex(['customer_id']);
            }
            if (Schema::hasIndex('bookings', 'bookings_checked_in_index')) {
                $table->dropIndex(['checked_in']);
            }
            if (Schema::hasIndex('bookings', 'bookings_checked_out_index')) {
                $table->dropIndex(['checked_out']);
            }
            if (Schema::hasIndex('bookings', 'bookings_status_index')) {
                $table->dropIndex(['status']);
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasIndex('users', 'users_email_index')) {
                $table->dropIndex(['email']);
            }
            if (Schema::hasIndex('users', 'users_firstname_index')) {
                $table->dropIndex(['firstname']);
            }
            if (Schema::hasIndex('users', 'users_lastname_index')) {
                $table->dropIndex(['lastname']);
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasIndex('customers', 'customers_email_index')) {
                $table->dropIndex(['email']);
            }
            if (Schema::hasIndex('customers', 'customers_phone_index')) {
                $table->dropIndex(['phone']);
            }
        });
    }
}
