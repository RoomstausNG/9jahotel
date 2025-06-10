<?php

namespace App\Observers;

use App\Models\Booking;
use App\Services\CacheResponseService;
use Illuminate\Contracts\Queue\ShouldQueue;

class BookingObserver implements ShouldQueue
{
  /**
   * Handle the Booking "created" event.
   */
  public function created(Booking $booking): void
  {
    CacheResponseService::clearBookingCache();

    // Clear cache for the related hotel and room
    if ($booking->bookable_type === 'App\Models\Room' && $booking->bookable_id) {
      CacheResponseService::clearRoomCache($booking->bookable_id);
    }

    if ($booking->hotel_id) {
      CacheResponseService::clearHotelCache($booking->hotel_id);
    }
  }

  /**
   * Handle the Booking "updated" event.
   */
  public function updated(Booking $booking): void
  {
    CacheResponseService::clearBookingCache($booking->id);

    // Clear cache for the related hotel and room
    if ($booking->bookable_type === 'App\Models\Room' && $booking->bookable_id) {
      CacheResponseService::clearRoomCache($booking->bookable_id);
    }

    if ($booking->hotel_id) {
      CacheResponseService::clearHotelCache($booking->hotel_id);
    }
  }

  /**
   * Handle the Booking "deleted" event.
   */
  public function deleted(Booking $booking): void
  {
    CacheResponseService::clearBookingCache($booking->id);

    // Clear cache for the related hotel and room
    if ($booking->bookable_type === 'App\Models\Room' && $booking->bookable_id) {
      CacheResponseService::clearRoomCache($booking->bookable_id);
    }

    if ($booking->hotel_id) {
      CacheResponseService::clearHotelCache($booking->hotel_id);
    }
  }

  /**
   * Handle the Booking "restored" event.
   */
  public function restored(Booking $booking): void
  {
    CacheResponseService::clearBookingCache($booking->id);

    // Clear cache for the related hotel and room
    if ($booking->bookable_type === 'App\Models\Room' && $booking->bookable_id) {
      CacheResponseService::clearRoomCache($booking->bookable_id);
    }

    if ($booking->hotel_id) {
      CacheResponseService::clearHotelCache($booking->hotel_id);
    }
  }

  /**
   * Handle the Booking "force deleted" event.
   */
  public function forceDeleted(Booking $booking): void
  {
    CacheResponseService::clearBookingCache($booking->id);

    // Clear cache for the related hotel and room
    if ($booking->bookable_type === 'App\Models\Room' && $booking->bookable_id) {
      CacheResponseService::clearRoomCache($booking->bookable_id);
    }

    if ($booking->hotel_id) {
      CacheResponseService::clearHotelCache($booking->hotel_id);
    }
  }
}
