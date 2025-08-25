<?php

namespace App\Jobs;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteCustomerJob implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public $customerId;

  public function __construct($customerId)
  {
    $this->customerId = $customerId;
  }

  public function handle()
  {
    $customer = Customer::find($this->customerId);
    if ($customer) {
      $customer->delete();
    }
  }
}
