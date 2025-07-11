<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class CacheService
{
  /**
   * Default cache time in minutes
   */
  protected int $defaultCacheTime = 60;

  /**
   * Cache a value with a specified key and optional expiration time
   * 
   * @param string $key Cache key
   * @param mixed $value Value to cache
   * @param int|null $minutes Minutes to cache (null for default)
   * @return bool True if caching was successful
   */
  public function put(string $key, $value, ?int $minutes = null): bool
  {
    $minutes = $minutes ?? $this->defaultCacheTime;
    return Cache::put($key, $value, Carbon::now()->addMinutes($minutes));
  }

  /**
   * Get a value from cache or retrieve it using the callback if not found
   * 
   * @param string $key Cache key
   * @param \Closure $callback Function to execute if cache is missing
   * @param int|null $minutes Minutes to cache (null for default)
   * @return mixed The cached or retrieved value
   */
  public function remember(string $key, \Closure $callback, ?int $minutes = null)
  {
    $minutes = $minutes ?? $this->defaultCacheTime;
    return Cache::remember($key, Carbon::now()->addMinutes($minutes), $callback);
  }

  /**
   * Remove a value from cache
   * 
   * @param string $key Cache key
   * @return bool True if removal was successful
   */
  public function forget(string $key): bool
  {
    return Cache::forget($key);
  }

  /**
   * Remove multiple values from cache using a pattern
   * 
   * @param string $pattern Pattern to match cache keys
   * @return bool True if removal was successful
   */
  public function forgetPattern(string $pattern): bool
  {
    // This implementation works for Redis cache driver
    if (config('cache.default') === 'redis') {
      $redis = Cache::getRedis();
      $keys = $redis->keys($pattern);

      foreach ($keys as $key) {
        $redis->del($key);
      }

      return true;
    }

    return false;
  }

  /**
   * Clear all application cache
   * 
   * @return bool True if clearing was successful
   */
  public function clear(): bool
  {
    return Cache::flush();
  }

  /**
   * Generate a standardized cache key for entities
   * 
   * @param string $entity Entity name
   * @param int|string $id Entity ID
   * @param string|null $action Specific action or segment
   * @return string Formatted cache key
   */
  public function generateKey(string $entity, $id, ?string $action = null): string
  {
    return $action
      ? "app:{$entity}:{$id}:{$action}"
      : "app:{$entity}:{$id}";
  }
}
