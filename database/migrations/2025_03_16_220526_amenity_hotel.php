<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create("amenity_hotel", function (Blueprint $table) {
            $table->foreignId('amenity_id')->nullable()->constrained('amenities')->cascadeOnDelete();
            $table->foreignId('hotel_id')->nullable()->constrained('hotels')->cascadeOnDelete();
        });

        // 1. Try to drop the foreign key and column with error handling
        try {
            Schema::table("amenities", function (Blueprint $table) {
                $table->text('icon')->nullable()->change();
                $table->dropForeign('amenities_hotel_id_foreign');
                $table->dropColumn("hotel_id");
            });
        } catch (\Exception $e) {
            // If foreign key doesn't exist, just try to drop the column
            try {
                Schema::table("amenities", function (Blueprint $table) {
                    $table->dropColumn("hotel_id");
                });
            } catch (\Exception $e) {
                // Column might not exist either, continue
            }
        }

        // 2. Truncate the amenities table
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('amenities')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 3. Copy data with explicit column names
        // Specify the exact column names that exist in both tables
        $columns = ['id', 'name', 'icon', 'created_at', 'updated_at'];

        // Create the SQL statement with exact column names
        $sql = "INSERT INTO amenities (id, name, icon, created_at, updated_at)
                SELECT id, name,icon, created_at, updated_at
                FROM general_amenities";

        DB::statement($sql);
    }

    public function down(): void
    {
        // If you need to rollback, truncate the amenities table
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('amenities')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};