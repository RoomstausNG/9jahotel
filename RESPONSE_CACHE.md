# Response Cache Implementation Guide

This document explains how the Spatie Laravel ResponseCache package has been implemented in the 9jahotel booking application.

## Overview

The ResponseCache package has been configured to cache successful GET requests, improving the application's performance by serving cached responses when possible. The implementation includes proper cache invalidation for dynamic content.

> **Note**: The current cache store does not support tagging, so all cache invalidation operations clear the entire cache.

## Configuration

-   **Cache Duration**: 1 day (86400 seconds) by default, configurable via `.env`
-   **Cache Driver**: Uses the application's default cache driver, configurable via `.env`
-   **Cache Bypass Header**: `X-Cache-Bypass: true` can be used to bypass the cache

## Cache Exclusions

The custom `HotelBookingCacheProfile` class excludes the following from being cached:

-   Authenticated user requests
-   Session-specific content (search criteria, booking details, etc.)
-   Booking and payment flows
-   User-specific pages (profile, bookings history, etc.)

## Cache Invalidation

Cache invalidation is handled automatically through Model Observers for the following models:

-   Hotel
-   Room
-   Booking
-   BookingInvoice
-   CreditPayment

When any of these models are created, updated, or deleted, the relevant parts of the cache are automatically cleared. All observers implement the `ShouldQueue` interface, which means cache invalidation operations run in the background and don't block the main request.

## Commands

A custom Artisan command has been added to clear the response cache:

```bash
php artisan cache:response:clear
```

This command is automatically included in the deployment script.

## Environment Variables

The following environment variables are available for configuration:

```
RESPONSE_CACHE_ENABLED=true
RESPONSE_CACHE_LIFETIME=86400
RESPONSE_CACHE_DRIVER=redis
CACHE_BYPASS_HEADER_NAME=X-Cache-Bypass
CACHE_BYPASS_HEADER_VALUE=true
```

## Middleware

The response cache middleware has been added to the web middleware group in `app/Http/Kernel.php`. The `doNotCacheResponse` middleware is also available for routes that should never be cached.

## Best Practices

1. Use the `doNotCacheResponse` middleware for routes that should never be cached
2. When adding new models that affect content, create a new Observer to handle cache invalidation
3. Update the `HotelBookingCacheProfile` class when adding new routes that should be excluded from caching
4. The cache is automatically cleared during deployment
