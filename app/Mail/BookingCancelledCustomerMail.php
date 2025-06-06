<?php

namespace App\Mail;

use App\Models\BookingInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingCancelledCustomerMail extends Mailable
{
  use Queueable, SerializesModels;

  public BookingInvoice $invoice;
  public $reason;
  public $other_reason;

  public function __construct(BookingInvoice $invoice, $reason, $other_reason = null)
  {
    $this->invoice = $invoice;
    $this->reason = $reason;
    $this->other_reason = $other_reason;
  }

  public function build()
  {
    return $this->subject('Your Booking Has Been Cancelled')
      ->view('emails.booking.cancelled-customer')
      ->with([
        'invoice' => $this->invoice,
        'reason' => $this->reason,
        'other_reason' => $this->other_reason,
      ]);
  }
}
