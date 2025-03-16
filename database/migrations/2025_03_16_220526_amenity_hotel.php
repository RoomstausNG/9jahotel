<?php

use Illuminate\Support\Facades\DB;
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
        Schema::create("amenity_hotel", function (Blueprint $table) {
            $table->foreignId('amenity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
        });
        try {
            Schema::table("amenities", function (Blueprint $table) {
                $table->dropForeign('amenities_hotel_id_foreign');
                $table->dropColumn("hotel_id");
            });
        } catch (\Exception $e) {
            // If foreign key doesn't exist, just drop the column
            try {
                Schema::table("amenities", function (Blueprint $table) {
                    $table->dropColumn("hotel_id");
                });
            } catch (\Exception $e) {
                // Column might not exist either, continue with next steps
            }
        }

        // 2. Truncate the amenities table
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('amenities')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 3. Copy all data from general_amenities to amenities
        // First, get the structure of amenities table to ensure column compatibility
        $amenitiesColumns = Schema::getColumnListing('amenities');
        $generalAmenitiesColumns = Schema::getColumnListing('general_amenities');

        // Find common columns between the two tables
        $commonColumns = array_intersect($generalAmenitiesColumns, $amenitiesColumns);

        // Build a comma-separated list of columns for the SQL query
        $columnsList = implode(', ', $commonColumns);

        // Copy the data
        DB::statement("INSERT INTO amenities ($columnsList) SELECT $columnsList FROM general_amenities");
    }

    public function down(): void
    {
        // If you need to rollback, you could truncate the amenities table
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('amenities')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
