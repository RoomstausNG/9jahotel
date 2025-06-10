<?php

namespace App\Services;

use Spatie\ResponseCache\Facades\ResponseCache;

class CacheResponseService
{
  /**
   * Clear the entire response cache
   */
  public static function clearAll()
  {
    ResponseCache::clear();
  }

  /**
   * Clear cache related to hotel listings and details
   *
   * @param int $hotelId
   */
  public static function clearHotelCache($hotelId = null)
  {
    // Since we don't support tagging, we'll clear the entire cache
    self::clearAll();
  }

  /**
   * Clear cache related to room listings
   *
   * @param int $roomId
   */
  public static function clearRoomCache($roomId = null)
  {
    // Since we don't support tagging, we'll clear the entire cache
    self::clearAll();
  }

  /**
   * Clear cache related to bookings
   *
   * @param int $bookingId
   */
  public static function clearBookingCache($bookingId = null)
  {
    // Since we don't support tagging, we'll clear the entire cache
    self::clearAll();
  }

  /**
   * Clear cache related to payments
   */
  public static function clearPaymentCache()
  {
    // Since we don't support tagging, we'll clear the entire cache
    self::clearAll();
  }

  /**
   * Clear homepage and search result caches
   */
  public static function clearHomepageCache()
  {
    // Since we don't support tagging, we'll clear the entire cache
    self::clearAll();
  }
}
