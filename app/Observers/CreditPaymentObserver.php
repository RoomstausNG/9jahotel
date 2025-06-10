<?php

namespace App\Observers;

use App\Models\CreditPayment;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreditPaymentObserver implements ShouldQueue
{
  /**
   * Handle the CreditPayment "created" event.
   */
  public function created(CreditPayment $creditPayment): void
  {
    // Cache handling removed
  }

  /**
   * Handle the CreditPayment "updated" event.
   */
  public function updated(CreditPayment $creditPayment): void
  {
    // Cache handling removed
  }

  /**
   * Handle the CreditPayment "deleted" event.
   */
  public function deleted(CreditPayment $creditPayment): void
  {
    // Cache handling removed
  }

  /**
   * Handle the CreditPayment "restored" event.
   */
  public function restored(CreditPayment $creditPayment): void
  {
    // Cache handling removed
  }

  /**
   * Handle the CreditPayment "force deleted" event.
   */
  public function forceDeleted(CreditPayment $creditPayment): void
  {
    // Cache handling removed
  }
}
