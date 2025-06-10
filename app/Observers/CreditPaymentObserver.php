<?php

namespace App\Observers;

use App\Models\CreditPayment;
use App\Services\CacheResponseService;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreditPaymentObserver implements ShouldQueue
{
  /**
   * Handle the CreditPayment "created" event.
   */
  public function created(CreditPayment $creditPayment): void
  {
    CacheResponseService::clearPaymentCache();

    // Clear hotel cache if hotel_id is available
    if ($creditPayment->hotel_id) {
      CacheResponseService::clearHotelCache($creditPayment->hotel_id);
    }
  }

  /**
   * Handle the CreditPayment "updated" event.
   */
  public function updated(CreditPayment $creditPayment): void
  {
    CacheResponseService::clearPaymentCache();

    // Clear hotel cache if hotel_id is available
    if ($creditPayment->hotel_id) {
      CacheResponseService::clearHotelCache($creditPayment->hotel_id);
    }
  }

  /**
   * Handle the CreditPayment "deleted" event.
   */
  public function deleted(CreditPayment $creditPayment): void
  {
    CacheResponseService::clearPaymentCache();

    // Clear hotel cache if hotel_id is available
    if ($creditPayment->hotel_id) {
      CacheResponseService::clearHotelCache($creditPayment->hotel_id);
    }
  }

  /**
   * Handle the CreditPayment "restored" event.
   */
  public function restored(CreditPayment $creditPayment): void
  {
    CacheResponseService::clearPaymentCache();

    // Clear hotel cache if hotel_id is available
    if ($creditPayment->hotel_id) {
      CacheResponseService::clearHotelCache($creditPayment->hotel_id);
    }
  }

  /**
   * Handle the CreditPayment "force deleted" event.
   */
  public function forceDeleted(CreditPayment $creditPayment): void
  {
    CacheResponseService::clearPaymentCache();

    // Clear hotel cache if hotel_id is available
    if ($creditPayment->hotel_id) {
      CacheResponseService::clearHotelCache($creditPayment->hotel_id);
    }
  }
}
