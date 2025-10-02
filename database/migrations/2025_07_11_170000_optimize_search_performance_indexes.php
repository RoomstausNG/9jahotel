<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OptimizeSearchPerformanceIndexes extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes for search performance on hotels table
        Schema::table("hotels", function (Blueprint $table) {
            if (!Schema::hasIndex("hotels", "hotels_name_index")) {
                $table->index("name");
            }
            if (!Schema::hasIndex("hotels", "hotels_address_index")) {
                $table->index("address");
            }
            if (!Schema::hasIndex("hotels", "hotels_city_id_index")) {
                $table->index("city_id");
            }
            if (!Schema::hasIndex("hotels", "hotels_email_index")) {
                $table->index("email");
            }
            if (!Schema::hasIndex("hotels", "hotels_telephone_index")) {
                $table->index("telephone");
            }
            if (!Schema::hasIndex("hotels", "hotels_code_index")) {
                $table->index("code");
            }
        });

        // Add indexes for search performance on users table
        Schema::table("users", function (Blueprint $table) {
            if (!Schema::hasIndex("users", "users_email_index")) {
                $table->index("email");
            }
            if (!Schema::hasIndex("users", "users_firstname_index")) {
                $table->index("firstname");
            }
            if (!Schema::hasIndex("users", "users_lastname_index")) {
                $table->index("lastname");
            }
        });

        // Add indexes for search performance on customers table
        Schema::table("customers", function (Blueprint $table) {
            if (!Schema::hasIndex("customers", "customers_email_index")) {
                $table->index("email");
            }
            if (!Schema::hasIndex("customers", "customers_phone_index")) {
                $table->index("phone");
            }
            if (!Schema::hasIndex("customers", "customers_id_number_index")) {
                $table->index("id_number");
            }
        });

        // Add indexes for search performance on bookings table
        Schema::table("bookings", function (Blueprint $table) {
            if (!Schema::hasIndex("bookings", "bookings_code_index")) {
                $table->index("code");
            }
            if (!Schema::hasIndex("bookings", "bookings_status_index")) {
                $table->index("status");
            }
            if (!Schema::hasIndex("bookings", "bookings_checked_in_index")) {
                $table->index("checked_in");
            }
            if (!Schema::hasIndex("bookings", "bookings_checked_out_index")) {
                $table->index("checked_out");
            }
        });

        // Add indexes for other tables as needed
        Schema::table("floors", function (Blueprint $table) {
            if (!Schema::hasIndex("floors", "floors_name_index")) {
                $table->index("name");
            }
            if (!Schema::hasIndex("floors", "floors_hotel_id_index")) {
                $table->index("hotel_id");
            }
        });

        Schema::table("rooms", function (Blueprint $table) {
            if (!Schema::hasIndex("rooms", "rooms_name_index")) {
                $table->index("name");
            }
            if (!Schema::hasIndex("rooms", "rooms_hotel_id_index")) {
                $table->index("hotel_id");
            }
            if (!Schema::hasIndex("rooms", "rooms_type_id_index")) {
                $table->index("type_id");
            }
        });

        Schema::table("halls", function (Blueprint $table) {
            if (!Schema::hasIndex("halls", "halls_name_index")) {
                $table->index("name");
            }
            if (!Schema::hasIndex("halls", "halls_hotel_id_index")) {
                $table->index("hotel_id");
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the indexes in reverse order
        Schema::table("hotels", function (Blueprint $table) {
            if (Schema::hasIndex("hotels", "hotels_name_index")) {
                $table->dropIndex(["name"]);
            }
            if (Schema::hasIndex("hotels", "hotels_address_index")) {
                $table->dropIndex(["address"]);
            }
            if (Schema::hasIndex("hotels", "hotels_city_id_index")) {
                $table->dropIndex(["city_id"]);
            }
            if (Schema::hasIndex("hotels", "hotels_email_index")) {
                $table->dropIndex(["email"]);
            }
            if (Schema::hasIndex("hotels", "hotels_telephone_index")) {
                $table->dropIndex(["telephone"]);
            }
            if (Schema::hasIndex("hotels", "hotels_code_index")) {
                $table->dropIndex(["code"]);
            }
        });

        Schema::table("users", function (Blueprint $table) {
            if (Schema::hasIndex("users", "users_email_index")) {
                $table->dropIndex(["email"]);
            }
            if (Schema::hasIndex("users", "users_firstname_index")) {
                $table->dropIndex(["firstname"]);
            }
            if (Schema::hasIndex("users", "users_lastname_index")) {
                $table->dropIndex(["lastname"]);
            }
        });

        Schema::table("customers", function (Blueprint $table) {
            if (Schema::hasIndex("customers", "customers_email_index")) {
                $table->dropIndex(["email"]);
            }
            if (Schema::hasIndex("customers", "customers_phone_index")) {
                $table->dropIndex(["phone"]);
            }
            if (Schema::hasIndex("customers", "customers_id_number_index")) {
                $table->dropIndex(["id_number"]);
            }
        });

        Schema::table("bookings", function (Blueprint $table) {
            if (Schema::hasIndex("bookings", "bookings_code_index")) {
                $table->dropIndex(["code"]);
            }
            if (Schema::hasIndex("bookings", "bookings_status_index")) {
                $table->dropIndex(["status"]);
            }
            if (Schema::hasIndex("bookings", "bookings_checked_in_index")) {
                $table->dropIndex(["checked_in"]);
            }
            if (Schema::hasIndex("bookings", "bookings_checked_out_index")) {
                $table->dropIndex(["checked_out"]);
            }
        });

        Schema::table("floors", function (Blueprint $table) {
            if (Schema::hasIndex("floors", "floors_name_index")) {
                $table->dropIndex(["name"]);
            }
            if (Schema::hasIndex("floors", "floors_hotel_id_index")) {
                $table->dropIndex(["hotel_id"]);
            }
        });

        Schema::table("rooms", function (Blueprint $table) {
            if (Schema::hasIndex("rooms", "rooms_name_index")) {
                $table->dropIndex(["name"]);
            }
            if (Schema::hasIndex("rooms", "rooms_hotel_id_index")) {
                $table->dropIndex(["hotel_id"]);
            }
            if (Schema::hasIndex("rooms", "rooms_type_id_index")) {
                $table->dropIndex(["type_id"]);
            }
        });

        Schema::table("halls", function (Blueprint $table) {
            if (Schema::hasIndex("halls", "halls_name_index")) {
                $table->dropIndex(["name"]);
            }
            if (Schema::hasIndex("halls", "halls_hotel_id_index")) {
                $table->dropIndex(["hotel_id"]);
            }
        });
    }
}
