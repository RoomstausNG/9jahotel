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
        // Add indexes to hotels table for search optimization
        Schema::table('hotels', function (Blueprint $table) {
            // Index for city-based searches (most common filter)
            $table->index('city_id', 'hotels_city_id_index');
            
            // Index for price range filtering
            $table->index('room_lowest_price', 'hotels_price_index');
            
            // Index for star rating sorting
            $table->index('star_rating', 'hotels_star_rating_index');
            
            // Composite index for city + price filters (very common combination)
            $table->index(['city_id', 'room_lowest_price'], 'hotels_city_price_index');
            
            // Composite index for city + star rating
            $table->index(['city_id', 'star_rating'], 'hotels_city_rating_index');
            
            // Index for name searches (LIKE queries)
            $table->index('name', 'hotels_name_index');
            
            // Index for created_at (default sorting)
            $table->index('created_at', 'hotels_created_at_index');
        });

        // Create amenity_hotel pivot table if it doesn't exist and add indexes
        if (!Schema::hasTable('amenity_hotel')) {
            Schema::create('amenity_hotel', function (Blueprint $table) {
                $table->id();
                $table->foreignId('amenity_id')->constrained()->onDelete('cascade');
                $table->foreignId('hotel_id')->constrained()->onDelete('cascade');
                $table->timestamps();
                
                // Unique constraint to prevent duplicates
                $table->unique(['amenity_id', 'hotel_id'], 'amenity_hotel_unique');
                
                // Indexes for efficient lookups
                $table->index('amenity_id', 'amenity_hotel_amenity_index');
                $table->index('hotel_id', 'amenity_hotel_hotel_index');
            });
        } else {
            // Add indexes to existing amenity_hotel table
            Schema::table('amenity_hotel', function (Blueprint $table) {
                // Check if indexes don't exist before adding
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexNames = array_keys($sm->listTableIndexes('amenity_hotel'));
                
                if (!in_array('amenity_hotel_amenity_index', $indexNames)) {
                    $table->index('amenity_id', 'amenity_hotel_amenity_index');
                }
                
                if (!in_array('amenity_hotel_hotel_index', $indexNames)) {
                    $table->index('hotel_id', 'amenity_hotel_hotel_index');
                }
                
                if (!in_array('amenity_hotel_unique', $indexNames)) {
                    $table->unique(['amenity_id', 'hotel_id'], 'amenity_hotel_unique');
                }
            });
        }

        // Add indexes to ratings table for average rating calculations
        Schema::table('ratings', function (Blueprint $table) {
            // Index for hotel-based rating queries
            $table->index('hotel_id', 'ratings_hotel_id_index');
            
            // Composite index for hotel + rating number (for sorting)
            $table->index(['hotel_id', 'number'], 'ratings_hotel_number_index');
        });

        // Add indexes to types (room categories) table
        if (Schema::hasTable('types')) {
            Schema::table('types', function (Blueprint $table) {
                // Index for hotel relationship
                $table->index('hotel_id', 'types_hotel_id_index');
                
                // Index for price sorting if price1 column exists
                if (Schema::hasColumn('types', 'price1')) {
                    $table->index('price1', 'types_price1_index');
                }
            });
        }

        // Add indexes to amenities table
        Schema::table('amenities', function (Blueprint $table) {
            // Index for name searches
            $table->index('name', 'amenities_name_index');
            
            // Index for hotel relationship if it exists
            if (Schema::hasColumn('amenities', 'hotel_id')) {
                $table->index('hotel_id', 'amenities_hotel_id_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes from hotels table
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropIndex('hotels_city_id_index');
            $table->dropIndex('hotels_price_index');
            $table->dropIndex('hotels_star_rating_index');
            $table->dropIndex('hotels_city_price_index');
            $table->dropIndex('hotels_city_rating_index');
            $table->dropIndex('hotels_name_index');
            $table->dropIndex('hotels_created_at_index');
        });

        // Remove indexes from amenity_hotel table (if we created it)
        if (Schema::hasTable('amenity_hotel')) {
            Schema::table('amenity_hotel', function (Blueprint $table) {
                $table->dropIndex('amenity_hotel_amenity_index');
                $table->dropIndex('amenity_hotel_hotel_index');
                $table->dropUnique('amenity_hotel_unique');
            });
        }

        // Remove indexes from ratings table
        Schema::table('ratings', function (Blueprint $table) {
            $table->dropIndex('ratings_hotel_id_index');
            $table->dropIndex('ratings_hotel_number_index');
        });

        // Remove indexes from types table
        if (Schema::hasTable('types')) {
            Schema::table('types', function (Blueprint $table) {
                $table->dropIndex('types_hotel_id_index');
                if (Schema::hasColumn('types', 'price1')) {
                    $table->dropIndex('types_price1_index');
                }
            });
        }

        // Remove indexes from amenities table
        Schema::table('amenities', function (Blueprint $table) {
            $table->dropIndex('amenities_name_index');
            if (Schema::hasColumn('amenities', 'hotel_id')) {
                $table->dropIndex('amenities_hotel_id_index');
            }
        });
    }
};
