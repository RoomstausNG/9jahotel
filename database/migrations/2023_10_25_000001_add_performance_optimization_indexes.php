<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPerformanceOptimizationIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Optimize rooms table
        Schema::table('rooms', function (Blueprint $table) {
            if (!Schema::hasIndex('rooms', 'rooms_hotel_id_type_id_status_index')) {
                $table->index(['hotel_id', 'type_id', 'status'], 'rooms_hotel_id_type_id_status_index');
            }
        });

        // Optimize room statuses table
        Schema::table('room_statuses', function (Blueprint $table) {
            if (!Schema::hasIndex('room_statuses', 'room_statuses_room_id_date_index')) {
                $table->index(['room_id', 'date'], 'room_statuses_room_id_date_index');
            }
        });

        // Optimize bookings table
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasIndex('bookings', 'bookings_user_id_status_index')) {
                $table->index(['user_id', 'status'], 'bookings_user_id_status_index');
            }
            if (!Schema::hasIndex('bookings', 'bookings_check_in_check_out_index')) {
                $table->index(['check_in', 'check_out'], 'bookings_check_in_check_out_index');
            }
        });

        // Optimize bookings_rooms pivot table (if it exists)
        if (Schema::hasTable('booking_room')) {
            Schema::table('booking_room', function (Blueprint $table) {
                if (!Schema::hasIndex('booking_room', 'booking_room_booking_id_room_id_index')) {
                    $table->index(['booking_id', 'room_id'], 'booking_room_booking_id_room_id_index');
                }
            });
        }

        // Optimize hotels table
        Schema::table('hotels', function (Blueprint $table) {
            if (!Schema::hasIndex('hotels', 'hotels_status_index')) {
                $table->index(['status'], 'hotels_status_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove indexes from rooms table
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasIndex('rooms', 'rooms_hotel_id_type_id_status_index')) {
                $table->dropIndex('rooms_hotel_id_type_id_status_index');
            }
        });

        // Remove indexes from room statuses table
        Schema::table('room_statuses', function (Blueprint $table) {
            if (Schema::hasIndex('room_statuses', 'room_statuses_room_id_date_index')) {
                $table->dropIndex('room_statuses_room_id_date_index');
            }
        });

        // Remove indexes from bookings table
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasIndex('bookings', 'bookings_user_id_status_index')) {
                $table->dropIndex('bookings_user_id_status_index');
            }
            if (Schema::hasIndex('bookings', 'bookings_check_in_check_out_index')) {
                $table->dropIndex('bookings_check_in_check_out_index');
            }
        });

        // Remove indexes from bookings_rooms pivot table (if it exists)
        if (Schema::hasTable('booking_room')) {
            Schema::table('booking_room', function (Blueprint $table) {
                if (Schema::hasIndex('booking_room', 'booking_room_booking_id_room_id_index')) {
                    $table->dropIndex('booking_room_booking_id_room_id_index');
                }
            });
        }

        // Remove indexes from hotels table
        Schema::table('hotels', function (Blueprint $table) {
            if (Schema::hasIndex('hotels', 'hotels_status_index')) {
                $table->dropIndex('hotels_status_index');
            }
        });
    }
}
