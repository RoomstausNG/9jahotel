<?php

namespace App\Observers;

use App\Models\Hotel;
use App\Services\CacheResponseService;
use Illuminate\Contracts\Queue\ShouldQueue;

class HotelObserver implements ShouldQueue
{
  /**
   * Handle the Hotel "created" event.
   */
  public function created(Hotel $hotel): void
  {
    CacheResponseService::clearHotelCache();
    CacheResponseService::clearHomepageCache();
  }

  /**
   * Handle the Hotel "updated" event.
   */
  public function updated(Hotel $hotel): void
  {
    CacheResponseService::clearHotelCache($hotel->id);
    CacheResponseService::clearHomepageCache();
  }

  /**
   * Handle the Hotel "deleted" event.
   */
  public function deleted(Hotel $hotel): void
  {
    CacheResponseService::clearHotelCache($hotel->id);
    CacheResponseService::clearHomepageCache();
  }

  /**
   * Handle the Hotel "restored" event.
   */
  public function restored(Hotel $hotel): void
  {
    CacheResponseService::clearHotelCache($hotel->id);
    CacheResponseService::clearHomepageCache();
  }

  /**
   * Handle the Hotel "force deleted" event.
   */
  public function forceDeleted(Hotel $hotel): void
  {
    CacheResponseService::clearHotelCache($hotel->id);
    CacheResponseService::clearHomepageCache();
  }
}
