<?php

namespace App\Observers;

use App\Models\BookingInvoice;
use App\Services\CacheResponseService;
use Illuminate\Contracts\Queue\ShouldQueue;

class BookingInvoiceObserver implements ShouldQueue
{
  /**
   * Handle the BookingInvoice "created" event.
   */
  public function created(BookingInvoice $bookingInvoice): void
  {
    CacheResponseService::clearBookingCache();
    CacheResponseService::clearPaymentCache();

    // Clear hotel cache if hotel_id is available
    if ($bookingInvoice->hotel_id) {
      CacheResponseService::clearHotelCache($bookingInvoice->hotel_id);
    }
  }

  /**
   * Handle the BookingInvoice "updated" event.
   */
  public function updated(BookingInvoice $bookingInvoice): void
  {
    CacheResponseService::clearBookingCache();
    CacheResponseService::clearPaymentCache();

    // Clear hotel cache if hotel_id is available
    if ($bookingInvoice->hotel_id) {
      CacheResponseService::clearHotelCache($bookingInvoice->hotel_id);
    }
  }

  /**
   * Handle the BookingInvoice "deleted" event.
   */
  public function deleted(BookingInvoice $bookingInvoice): void
  {
    CacheResponseService::clearBookingCache();
    CacheResponseService::clearPaymentCache();

    // Clear hotel cache if hotel_id is available
    if ($bookingInvoice->hotel_id) {
      CacheResponseService::clearHotelCache($bookingInvoice->hotel_id);
    }
  }

  /**
   * Handle the BookingInvoice "restored" event.
   */
  public function restored(BookingInvoice $bookingInvoice): void
  {
    CacheResponseService::clearBookingCache();
    CacheResponseService::clearPaymentCache();

    // Clear hotel cache if hotel_id is available
    if ($bookingInvoice->hotel_id) {
      CacheResponseService::clearHotelCache($bookingInvoice->hotel_id);
    }
  }

  /**
   * Handle the BookingInvoice "force deleted" event.
   */
  public function forceDeleted(BookingInvoice $bookingInvoice): void
  {
    CacheResponseService::clearBookingCache();
    CacheResponseService::clearPaymentCache();

    // Clear hotel cache if hotel_id is available
    if ($bookingInvoice->hotel_id) {
      CacheResponseService::clearHotelCache($bookingInvoice->hotel_id);
    }
  }
}
