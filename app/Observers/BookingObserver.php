<?php

namespace App\Observers;

use App\Models\Booking;
use Illuminate\Contracts\Queue\ShouldQueue;

class BookingObserver implements ShouldQueue
{
  /**
   * Handle the Booking "created" event.
   */
  public function created(Booking $booking): void
  {
    // Cache handling removed
  }

  /**
   * Handle the Booking "updated" event.
   */
  public function updated(Booking $booking): void
  {
    // Cache handling removed
  }

  /**
   * Handle the Booking "deleted" event.
   */
  public function deleted(Booking $booking): void
  {
    // Cache handling removed
  }

  /**
   * Handle the Booking "restored" event.
   */
  public function restored(Booking $booking): void
  {
    // Cache handling removed
  }

  /**
   * Handle the Booking "force deleted" event.
   */
  public function forceDeleted(Booking $booking): void
  {
    // Cache handling removed
  }
}
