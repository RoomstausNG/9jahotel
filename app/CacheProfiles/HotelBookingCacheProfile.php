<?php

namespace App\CacheProfiles;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Spatie\ResponseCache\CacheProfiles\CacheAllSuccessfulGetRequests;

class HotelBookingCacheProfile extends CacheAllSuccessfulGetRequests
{
  /**
   * Determine if the given request should be cached.
   */
  public function shouldCacheRequest(Request $request): bool
  {
    // First apply the parent rules (only cache GET requests, etc.)
    if (!parent::shouldCacheRequest($request)) {
      return false;
    }

    // Don't cache any requests that have specific session data
    if (
      $request->session()->has('city') ||
      $request->session()->has('checkin') ||
      $request->session()->has('checkout')
    ) {
      return false;
    }

    // Don't cache authenticated requests
    if (auth()->check() || auth('customers')->check()) {
      return false;
    }

    // Don't cache routes related to bookings or payments
    $uri = $request->getRequestUri();

    // These patterns should not be cached as they contain dynamic/sensitive content
    $excludePatterns = [
      '/paynow/',
      '/payment/',
      '/cancel/',
      '/booking/',
      '/successpayment/',
      '/paystack/',
      '/opay/',
      '/profile',
      '/bookings',
      '/home',
      '/session-debug',
      '/group-booking',
      '/hotel/rooms/', // Dynamic booking page
      '/hotel/halls/', // Dynamic booking page
    ];

    foreach ($excludePatterns as $pattern) {
      if (Str::contains($uri, $pattern)) {
        return false;
      }
    }

    return true;
  }

  /**
   * Determine if the given response should be cached.
   */
  public function shouldCacheResponse(Response $response): bool
  {
    // Apply the parent logic first
    if (!parent::shouldCacheResponse($response)) {
      return false;
    }

    // Don't cache responses with specific cookies or headers
    if ($response->headers->has('X-No-Cache')) {
      return false;
    }

    return true;
  }
}
