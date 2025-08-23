# Using the Caching System in Your Application

This guide demonstrates how to use the application's Redis caching system in new features or services using the `CacheHelper` utility.

## Prerequisites

- Make sure Redis is properly configured in your environment
- Import the CacheHelper in your service/controller file

## Basic Usage Examples

### 1. Simple Resource Caching

Use this pattern for individual resources like a booking, user profile, or hotel details:

```typescript
import { CacheHelper } from "../utils/CacheHelper";

async function getUserProfile(userId: number) {
  // Define a unique cache key
  const cacheKey = CacheHelper.resourceKey("user", userId);

  // Try to get from cache first
  const cachedProfile = await CacheHelper.get(cacheKey);
  if (cachedProfile) {
    return cachedProfile;
  }

  // If not in cache, fetch from database
  const profile = await db
    .select()
    .from(users)
    .where(eq(users.id, userId))
    .limit(1);
  const userProfile = profile[0];

  if (userProfile) {
    // Cache the result with default expiration (1 hour)
    await CacheHelper.set(cacheKey, userProfile);
  }

  return userProfile;
}
```

### 2. Collection Caching (with Pagination/Filters)

Use this pattern for lists or collections, especially with filters:

```typescript
import { CacheHelper } from "../utils/CacheHelper";

async function getHotelsByCity(
  userId: number,
  cityId: number,
  page: number = 1,
  perPage: number = 20
) {
  // Create a cache key with all parameters
  const cacheKey = CacheHelper.collectionKey("hotels", userId, {
    city: cityId,
    page,
    perPage,
  });

  // Try to get from cache
  const cachedHotels = await CacheHelper.get(cacheKey);
  if (cachedHotels) {
    return cachedHotels;
  }

  // Calculate pagination
  const offset = (page - 1) * perPage;

  // Fetch from database
  const hotels = await db
    .select()
    .from(hotels)
    .where(eq(hotels.city_id, cityId))
    .limit(perPage)
    .offset(offset);

  // Get total count for pagination
  const countResult = await db
    .select({ count: count() })
    .from(hotels)
    .where(eq(hotels.city_id, cityId));

  const total = countResult[0]?.count ?? 0;

  // Format result with pagination metadata
  const result = {
    data: hotels,
    meta: {
      total,
      page,
      perPage,
      lastPage: Math.ceil(total / perPage),
    },
  };

  // Cache the result
  await CacheHelper.set(cacheKey, result);

  return result;
}
```

### 3. Cache Invalidation

Always invalidate cache when data is modified:

```typescript
import { CacheHelper } from "../utils/CacheHelper";

async function updateUserProfile(userId: number, data: any) {
  // Update in database
  await db.update(users).set(data).where(eq(users.id, userId));

  // Invalidate specific cache
  await CacheHelper.delete(CacheHelper.resourceKey("user", userId));

  // Also invalidate any collections that might include this user
  await CacheHelper.deletePattern(`users:*:${userId}:*`);

  return { success: true };
}
```

## Best Practices

1. **Use consistent key naming**:

   - Use the CacheHelper methods for consistent key formatting
   - Keep keys descriptive but concise

2. **Set appropriate TTLs**:

   - Use shorter TTLs for frequently changing data
   - Use longer TTLs for relatively static data
   - Default is 3600 seconds (1 hour)

3. **Handle cache failures gracefully**:

   - CacheHelper handles Redis errors internally
   - Your code should work even if caching fails

4. **Cache invalidation strategies**:

   - Delete specific keys when an entity is updated
   - Delete patterns when collections might be affected
   - Be surgical with invalidation to maintain cache efficiency

5. **Monitor cache performance**:

   - Watch for patterns of high cache misses
   - Consider preloading cache for high-traffic resources

6. **Security considerations**:
   - Don't store sensitive data in cache without encryption
   - Use user-specific cache keys for user-specific data

## Example Integration in a Controller

```typescript
import { Context } from "hono";
import { CacheHelper } from "../utils/CacheHelper";

export class HotelController {
  async getHotelDetails(c: Context) {
    const hotelId = c.req.param("id");
    const userId = c.get("user")?.id;

    // Try cache first
    const cacheKey = CacheHelper.resourceKey("hotel", hotelId);
    const cachedHotel = await CacheHelper.get(cacheKey);

    if (cachedHotel) {
      return c.json(cachedHotel);
    }

    // Fetch from database
    const hotelData = await this.hotelService.getHotelById(hotelId);

    // Cache the result
    await CacheHelper.set(cacheKey, hotelData);

    return c.json(hotelData);
  }
}
```
