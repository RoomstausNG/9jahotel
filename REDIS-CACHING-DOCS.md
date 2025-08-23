# Redis Caching System Documentation

## Overview

The application uses Redis as a caching layer to improve performance by storing frequently accessed data and reducing database load. This document outlines the caching implementation, patterns, and best practices used throughout the application.

## Redis Configuration

Redis is configured in `src/config/redis.ts` and made available throughout the application via an exported `redis` instance.

## Caching Patterns

### 1. Resource Caching Pattern

This pattern is used to cache API resources (like bookings, hotels, etc.) with a time-based expiration.

```typescript
// Example from getBookingByIdOrReference in BookingService.ts
const CACHE_EXPIRATION_SECONDS = 3600; // 1 hour cache TTL
const cacheKey = `booking:invoice:${idOrReference}`;

// Try to get from cache first
const cachedData = await redis.get(cacheKey);
if (cachedData) {
  console.log(`Cache hit for key: ${cacheKey}`);
  return JSON.parse(cachedData);
}
console.log(`Cache miss for key: ${cacheKey}`);

// Database lookup if not in cache
// ... fetch data from database ...

// Cache the result before returning
await redis.set(
  cacheKey,
  JSON.stringify(resourceResult),
  "EX",
  CACHE_EXPIRATION_SECONDS
);

return resourceResult;
```

### 2. Pagination Results Caching

Used for caching paginated results with user-specific keys.

```typescript
// Example from getBookingsByStatus in BookingService.ts
const CACHE_EXPIRATION_SECONDS = 3600;
const cacheKey = `bookings:status:${userId}:${status}:page:${page}:perPage:${perPage}`;

// Try to get from cache first
try {
  const cachedData = await redis.get(cacheKey);
  if (cachedData) {
    console.log(`Cache hit for key: ${cacheKey}`);
    return JSON.parse(cachedData);
  }
  console.log(`Cache miss for key: ${cacheKey}`);
} catch (error) {
  console.error("Redis error getting cache:", error);
}

// ... fetch data from database ...

// Cache the result before returning
try {
  await redis.set(
    cacheKey,
    JSON.stringify(result),
    "EX",
    CACHE_EXPIRATION_SECONDS
  );
  console.log(`Cached data for key: ${cacheKey}`);
} catch (error) {
  console.error("Redis error setting cache:", error);
}
```

### 3. Cache Invalidation Pattern

Used when data is modified and cached data needs to be invalidated.

```typescript
// Example from createBooking in BookingService.ts
try {
  const user = userId || c.get("user")?.id;
  if (user) {
    // Assuming redis supports the 'keys' and 'del' commands
    const pattern = `bookings:status:${user}:*`;
    const keys = await redis.keys(pattern);
    if (keys.length > 0) {
      await redis.del(...keys);
      console.log(`Cleared bookings cache for user ${user}`);
    }
  }
} catch (cacheError) {
  console.error("Failed to clear bookings cache:", cacheError);
}
```

## Key Naming Conventions

The application uses a consistent key naming convention using colons as separators:

- `resource:identifier` - For single resource caching (e.g., `booking:invoice:1234`)
- `resource:filter:parameters` - For collections with filters (e.g., `bookings:status:user_id:upcoming:page:1:perPage:24`)

## Cache Expiration Strategy

The application uses time-based expiration (TTL) for all cached data. Most resources expire after 1 hour (3600 seconds) by default.

## Error Handling

All Redis operations are wrapped in try/catch blocks to ensure that cache failures don't interrupt the main application flow:

```typescript
try {
  // Redis operations
} catch (error) {
  console.error("Redis error:", error);
  // Continue without cache
}
```

## Implementation Guide for New Features

To implement caching for a new endpoint or service:

1. **Define a unique cache key** using the conventions above
2. **Check cache first** before expensive operations
3. **Set cache after retrieving data** with appropriate TTL
4. **Implement cache invalidation** when data is modified
5. **Handle cache errors gracefully** to ensure the application works even if Redis is unavailable

## Redis Commands Used

The application primarily uses these Redis commands:

- `GET` - Retrieve cached data
- `SET` with `EX` - Store data with expiration
- `DEL` - Remove specific keys
- `KEYS` - Find keys matching a pattern (for invalidation)

## Considerations

- Redis operations are asynchronous and use `await` throughout the codebase
- All cached data is JSON-stringified before storage and parsed after retrieval
- Cache keys are carefully constructed to avoid collisions
- Cache invalidation is selective and targeted to specific user data when possible
