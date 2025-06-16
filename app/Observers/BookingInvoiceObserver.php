<?php

namespace App\Observers;

use App\Models\BookingInvoice;
use Illuminate\Contracts\Queue\ShouldQueue;

class BookingInvoiceObserver implements ShouldQueue
{
  /**
   * Handle the BookingInvoice "created" event.
   */
  public function created(BookingInvoice $bookingInvoice): void
  {
    // Cache handling removed
  }

  /**
   * Handle the BookingInvoice "updated" event.
   */
  public function updated(BookingInvoice $bookingInvoice): void
  {
    // Cache handling removed
  }

  /**
   * Handle the BookingInvoice "deleted" event.
   */
  public function deleted(BookingInvoice $bookingInvoice): void
  {
    // Cache handling removed
  }

  /**
   * Handle the BookingInvoice "restored" event.
   */
  public function restored(BookingInvoice $bookingInvoice): void
  {
    // Cache handling removed
  }

  /**
   * Handle the BookingInvoice "force deleted" event.
   */
  public function forceDeleted(BookingInvoice $bookingInvoice): void
  {
    // Cache handling removed
  }
}
