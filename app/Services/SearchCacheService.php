<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SearchCacheService
{
    /**
     * Cache duration in seconds
     */
    const AMENITIES_CACHE_DURATION = 3600; // 1 hour
    const SEARCH_RESULTS_CACHE_DURATION = 300; // 5 minutes
    const CITY_DATA_CACHE_DURATION = 7200; // 2 hours

    /**
     * Get cached amenities for a city
     */
    public static function getCityAmenities(int $cityId)
    {
        $cacheKey = "city_amenities_{$cityId}";

        return Cache::remember($cacheKey, self::AMENITIES_CACHE_DURATION, function () use ($cityId) {
            return \App\Models\Amenity::select('amenities.id', 'amenities.name')
                ->join('amenity_hotel', 'amenities.id', '=', 'amenity_hotel.amenity_id')
                ->join('hotels', 'amenity_hotel.hotel_id', '=', 'hotels.id')
                ->join('types', 'hotels.id', '=', 'types.hotel_id') // Ensure hotel has room types
                ->where('hotels.city_id', $cityId)
                ->distinct()
                ->orderBy('amenities.name')
                ->get();
        });
    }

    /**
     * Get cached city data
     */
    public static function getCityData(int $cityId)
    {
        $cacheKey = "city_data_{$cityId}";

        return Cache::remember($cacheKey, self::CITY_DATA_CACHE_DURATION, function () use ($cityId) {
            return \App\Models\CityCopy::find($cityId);
        });
    }

    /**
     * Clear all search-related caches for a city
     */
    public static function clearCityCache(int $cityId): void
    {
        $patterns = [
            "city_amenities_{$cityId}",
            "city_data_{$cityId}",
            "search_results_{$cityId}_*"
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($pattern, '*')) {
                // For wildcard patterns, we'd need a more sophisticated cache clearing
                // For now, log that we need to clear these manually
                Log::info("Cache pattern to clear: {$pattern}");
            } else {
                Cache::forget($pattern);
            }
        }
    }

    /**
     * Clear all amenity caches (useful when amenities are updated)
     */
    public static function clearAllAmenityCaches(): void
    {
        // In a production environment, you might want to use cache tags
        // For now, we'll log this action
        Log::info('All amenity caches should be cleared');

        // Clear common city amenity caches
        // You might want to keep a list of active cities to clear their caches
    }

    /**
     * Generate cache key for search results
     */
    public static function generateSearchCacheKey(array $params): string
    {
        $keyParams = [
            'city' => $params['cityId'] ?? '',
            'name' => $params['name'] ?? '',
            'low' => $params['low'] ?? '',
            'high' => $params['high'] ?? '',
            'amenities' => isset($params['amenities']) ? (function ($arr) {
                sort($arr);
                return implode(',', $arr);
            })($params['amenities']) : '',
            'sort' => $params['sortBy'] ?? '',
            'page' => $params['page'] ?? 1,
            'per_page' => $params['perPage'] ?? 12
        ];

        return 'search_results_' . md5(serialize($keyParams));
    }

    /**
     * Get or cache search results
     */
    public static function getCachedSearchResults(string $cacheKey, callable $callback)
    {
        // For search results, we use a shorter cache duration
        return Cache::remember($cacheKey, self::SEARCH_RESULTS_CACHE_DURATION, $callback);
    }

    /**
     * Warm up cache for popular searches
     */
    public static function warmUpCache(int $cityId): void
    {
        // Pre-load amenities for the city
        self::getCityAmenities($cityId);

        // Pre-load city data
        self::getCityData($cityId);

        Log::info("Cache warmed up for city: {$cityId}");
    }
}
