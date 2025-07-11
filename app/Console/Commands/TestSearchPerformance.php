<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Hotel;
use App\Models\CityCopy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TestSearchPerformance extends Command
{
    protected $signature = 'search:test-performance {--city-id=1 : City ID to test} {--iterations=10 : Number of test iterations}';
    protected $description = 'Test search performance with optimized queries';

    public function handle()
    {
        $cityId = $this->option('city-id');
        $iterations = $this->option('iterations');
        
        $this->info("Testing search performance for city ID: {$cityId}");
        $this->info("Running {$iterations} iterations...");
        
        // Clear cache to ensure fair testing
        Cache::flush();
        
        $totalTime = 0;
        $queryCount = 0;
        
        for ($i = 1; $i <= $iterations; $i++) {
            $startTime = microtime(true);
            $queryCountStart = DB::getQueryLog() ? count(DB::getQueryLog()) : 0;
            
            // Enable query logging for this test
            DB::enableQueryLog();
            
            // Test the optimized query
            $hotels = Hotel::query()
                ->select([
                    'hotels.*',
                    DB::raw('COALESCE(AVG(ratings.number), 0) as avg_rating'),
                    DB::raw('COUNT(ratings.id) as rating_count')
                ])
                ->leftJoin('ratings', 'hotels.id', '=', 'ratings.hotel_id')
                ->with(['city:id,name', 'amenities:id,name'])
                ->where('hotels.city_id', $cityId)
                ->whereHas('roomCategory')
                ->groupBy('hotels.id')
                ->orderBy('hotels.created_at', 'desc')
                ->paginate(12);
            
            $queries = DB::getQueryLog();
            $queryCountEnd = count($queries);
            $endTime = microtime(true);
            
            $iterationTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
            $iterationQueries = $queryCountEnd - $queryCountStart;
            
            $totalTime += $iterationTime;
            $queryCount += $iterationQueries;
            
            $this->line("Iteration {$i}: {$iterationTime}ms, {$iterationQueries} queries, {$hotels->total()} results");
            
            DB::flushQueryLog();
        }
        
        $avgTime = $totalTime / $iterations;
        $avgQueries = $queryCount / $iterations;
        
        $this->info("Results:");
        $this->info("Average execution time: " . round($avgTime, 2) . "ms");
        $this->info("Average queries per search: " . round($avgQueries, 2));
        $this->info("Total hotels found: " . ($hotels->total() ?? 0));
        
        // Test amenities query performance
        $this->info("\nTesting amenities query...");
        $startTime = microtime(true);
        
        $amenities = \App\Models\Amenity::select('amenities.id', 'amenities.name')
            ->join('amenity_hotel', 'amenities.id', '=', 'amenity_hotel.amenity_id')
            ->join('hotels', 'amenity_hotel.hotel_id', '=', 'hotels.id')
            ->join('types', 'hotels.id', '=', 'types.hotel_id')
            ->where('hotels.city_id', $cityId)
            ->distinct()
            ->orderBy('amenities.name')
            ->get();
            
        $amenitiesTime = (microtime(true) - $startTime) * 1000;
        
        $this->info("Amenities query: " . round($amenitiesTime, 2) . "ms, " . $amenities->count() . " amenities found");
        
        // Performance recommendations
        $this->info("\nPerformance Analysis:");
        
        if ($avgTime < 100) {
            $this->info("✅ Excellent performance (< 100ms)");
        } elseif ($avgTime < 300) {
            $this->info("✅ Good performance (< 300ms)");
        } elseif ($avgTime < 500) {
            $this->info("⚠️  Acceptable performance (< 500ms)");
        } else {
            $this->warn("❌ Poor performance (> 500ms) - consider further optimization");
        }
        
        if ($avgQueries <= 3) {
            $this->info("✅ Excellent query efficiency (<= 3 queries)");
        } elseif ($avgQueries <= 5) {
            $this->info("✅ Good query efficiency (<= 5 queries)");
        } else {
            $this->warn("⚠️  Consider reducing query count (current: " . round($avgQueries, 2) . ")");
        }
        
        return 0;
    }
}
