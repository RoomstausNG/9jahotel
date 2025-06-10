<?php

namespace App\Observers;

use App\Models\Hotel;
use Illuminate\Contracts\Queue\ShouldQueue;

class HotelObserver implements ShouldQueue
{
  /**
   * Handle the Hotel "created" event.
   */
  public function created(Hotel $hotel): void
  {
    // Cache handling removed
  }

  /**
   * Handle the Hotel "updated" event.
   */
  public function updated(Hotel $hotel): void
  {
    // Cache handling removed
  }

  /**
   * Handle the Hotel "deleted" event.
   */
  public function deleted(Hotel $hotel): void
  {
    // Cache handling removed
  }

  /**
   * Handle the Hotel "restored" event.
   */
  public function restored(Hotel $hotel): void
  {
    // Cache handling removed
  }

  /**
   * Handle the Hotel "force deleted" event.
   */
  public function forceDeleted(Hotel $hotel): void
  {
    // Cache handling removed
  }
}
