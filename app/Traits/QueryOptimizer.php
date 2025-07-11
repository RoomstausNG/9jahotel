<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

trait QueryOptimizer
{
    /**
     * Apply eager loading optimizations for commonly accessed relationships
     * 
     * @param Builder $query The query builder instance
     * @param array $relationships The relationships to eager load
     * @return Builder
     */
    public function scopeWithOptimized(Builder $query, array $relationships = []): Builder
    {
        return $query->with($relationships);
    }
    
    /**
     * Select only specified columns to reduce memory usage
     * 
     * @param Builder $query The query builder instance
     * @param array $columns The columns to select
     * @return Builder
     */
    public function scopeSelectOptimized(Builder $query, array $columns = ['*']): Builder
    {
        return $query->select($columns);
    }
    
    /**
     * Cache the query results using a smart caching key
     * 
     * @param Builder $query The query builder instance
     * @param string $key The cache key
     * @param int $minutes Minutes to cache
     * @return Builder
     */
    public function scopeCacheQuery(Builder $query, string $key, int $minutes = 60): Builder
    {
        $cacheKey = $this->generateCacheKey($key, $query);
        
        return $query->when(Cache::has($cacheKey), function (Builder $query) use ($cacheKey) {
            return $query->hydrate(Cache::get($cacheKey));
        }, function (Builder $query) use ($cacheKey, $minutes) {
            $results = $query->get();
            Cache::put($cacheKey, $results->toArray(), Carbon::now()->addMinutes($minutes));
            return $query->setResults($results);
        });
    }
    
    /**
     * Generate a unique cache key for a query
     * 
     * @param string $base Base key name
     * @param Builder $query Query being executed
     * @return string
     */
    protected function generateCacheKey(string $base, Builder $query): string
    {
        $sql = $query->toSql();
        $bindings = implode('_', $query->getBindings());
        return "query:{$base}:" . md5($sql . $bindings);
    }
    
    /**
     * Chunk results to process large datasets efficiently
     * 
     * @param Builder $query The query builder instance
     * @param int $count Number of records per chunk
     * @param callable $callback Function to process each chunk
     * @return bool
     */
    public function scopeChunkOptimized(Builder $query, int $count = 100, callable $callback): bool
    {
        return $query->chunk($count, $callback);
    }
    
    /**
     * Use cursor to reduce memory usage for large datasets
     * 
     * @param Builder $query The query builder instance
     * @return mixed
     */
    public function scopeCursorOptimized(Builder $query)
    {
        return $query->cursor();
    }
}
