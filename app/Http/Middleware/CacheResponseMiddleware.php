<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class CacheResponseMiddleware
{
  /**
   * The number of minutes to cache the response.
   *
   * @var int
   */
  protected $minutes = 60;

  /**
   * The routes that should be excluded from caching.
   *
   * @var array
   */
  protected $except = [
    '/admin/*',
    '/login',
    '/register',
    '/logout',
    '/password/*',
    '/api/*',
    '/livewire/*',
    '/webhook/*',
  ];

  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @param  int|null  $minutes
   * @return mixed
   */
  public function handle(Request $request, Closure $next, $minutes = null)
  {
    // Don't cache if request is not cacheable
    if (!$this->isCacheable($request)) {
      return $next($request);
    }

    // Set the cache time if provided
    if ($minutes !== null) {
      $this->minutes = (int) $minutes;
    }

    // Generate a cache key based on the full URL and user authentication status
    $cacheKey = $this->generateCacheKey($request);

    // Check if we have a cached response
    if (Cache::has($cacheKey)) {
      return Cache::get($cacheKey);
    }

    // Get the response
    $response = $next($request);

    // Don't cache error responses or redirects
    if (!$this->shouldCache($response)) {
      return $response;
    }

    // Cache the response
    Cache::put(
      $cacheKey,
      $response,
      Carbon::now()->addMinutes($this->minutes)
    );

    // Set cache headers
    return $response->header('X-Cache', 'HIT');
  }

  /**
   * Generate a unique cache key for the request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return string
   */
  protected function generateCacheKey(Request $request)
  {
    $url = $request->fullUrl();
    $method = $request->method();
    $user = $request->user() ? 'user-' . $request->user()->id : 'guest';
    $language = $request->header('Accept-Language') ?: 'en';

    return 'response_cache:' . md5($method . '|' . $url . '|' . $user . '|' . $language);
  }

  /**
   * Determine if the request is cacheable.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return bool
   */
  protected function isCacheable(Request $request)
  {
    // Only cache GET requests
    if ($request->method() !== 'GET') {
      return false;
    }

    // Don't cache requests with query parameters that affect data
    if (count(array_intersect(array_keys($request->query()), ['page', 'sort', 'filter', 'per_page'])) > 0) {
      return true; // These are actually fine to cache with the query in the cache key
    }

    // Don't cache excluded routes
    foreach ($this->except as $pattern) {
      if ($request->is($pattern)) {
        return false;
      }
    }

    // Don't cache if there's a flash message or validation errors
    if ($request->session()->has('errors') || $request->session()->has('_flash')) {
      return false;
    }

    return true;
  }

  /**
   * Determine if the response should be cached.
   *
   * @param  \Symfony\Component\HttpFoundation\Response  $response
   * @return bool
   */
  protected function shouldCache($response)
  {
    // Don't cache error responses
    if ($response->getStatusCode() >= 400) {
      return false;
    }

    // Don't cache redirects
    if ($response->isRedirection()) {
      return false;
    }

    // Don't cache responses with certain headers
    if (
      $response->headers->has('Cache-Control') &&
      str_contains($response->headers->get('Cache-Control'), 'no-cache')
    ) {
      return false;
    }

    return true;
  }
}
