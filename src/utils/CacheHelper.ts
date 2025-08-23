// src/utils/CacheHelper.ts
import { redis } from "../config/redis";

/**
 * Helper class for consistent cache operations throughout the application
 */
export class CacheHelper {
  /**
   * Default cache expiration time in seconds (1 hour)
   */
  static DEFAULT_EXPIRATION = 3600;

  /**
   * Get data from cache
   * @param key - The cache key
   * @returns The cached data (parsed from JSON) or null if not found
   */
  static async get<T>(key: string): Promise<T | null> {
    try {
      const data = await redis.get(key);
      if (data) {
        console.log(`Cache hit for key: ${key}`);
        return JSON.parse(data) as T;
      }
      console.log(`Cache miss for key: ${key}`);
      return null;
    } catch (error) {
      console.error(`Cache get error for key ${key}:`, error);
      return null;
    }
  }

  /**
   * Store data in cache with expiration
   * @param key - The cache key
   * @param data - The data to cache (will be JSON stringified)
   * @param expiration - Expiration time in seconds (defaults to 1 hour)
   * @returns True if successful, false otherwise
   */
  static async set(
    key: string,
    data: any,
    expiration: number = CacheHelper.DEFAULT_EXPIRATION
  ): Promise<boolean> {
    try {
      await redis.set(key, JSON.stringify(data), "EX", expiration);
      console.log(`Cached data for key: ${key}`);
      return true;
    } catch (error) {
      console.error(`Cache set error for key ${key}:`, error);
      return false;
    }
  }

  /**
   * Delete keys from cache
   * @param keys - Array of keys to delete
   * @returns Number of keys deleted or -1 if error
   */
  static async delete(...keys: string[]): Promise<number> {
    try {
      if (keys.length === 0) return 0;
      const deleted = await redis.del(...keys);
      console.log(`Deleted ${deleted} cache keys`);
      return deleted;
    } catch (error) {
      console.error(`Cache delete error:`, error);
      return -1;
    }
  }

  /**
   * Find and delete keys matching a pattern
   * @param pattern - Redis key pattern (e.g., "user:123:*")
   * @returns Number of keys deleted or -1 if error
   */
  static async deletePattern(pattern: string): Promise<number> {
    try {
      const keys = await redis.keys(pattern);
      if (keys.length === 0) return 0;

      const deleted = await redis.del(...keys);
      console.log(`Deleted ${deleted} cache keys matching pattern: ${pattern}`);
      return deleted;
    } catch (error) {
      console.error(`Cache deletePattern error for pattern ${pattern}:`, error);
      return -1;
    }
  }

  /**
   * Create a consistent resource cache key
   * @param resource - Resource type (e.g., "booking", "hotel")
   * @param id - Resource identifier
   * @returns Formatted cache key
   */
  static resourceKey(resource: string, id: string | number): string {
    return `${resource}:${id}`;
  }

  /**
   * Create a consistent collection cache key
   * @param resource - Resource type (e.g., "bookings", "hotels")
   * @param userId - User ID for user-specific collections
   * @param filters - Object containing filter parameters
   * @returns Formatted cache key
   */
  static collectionKey(
    resource: string,
    userId: string | number,
    filters: Record<string, any>
  ): string {
    const filterParts = Object.entries(filters)
      .map(([key, value]) => `${key}:${value}`)
      .join(":");

    return `${resource}:user:${userId}:${filterParts}`;
  }
}
