<?php

namespace App\Mail;

use App\Models\BookingInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingNonRefundableMail extends Mailable
{
  use Queueable, SerializesModels;

  public BookingInvoice $invoice;

  public function __construct(BookingInvoice $invoice)
  {
    $this->invoice = $invoice;
  }

  public function build()
  {
    return $this->subject('Your Booking Confirmation - Non-Refundable')
      ->view('emails.booking.nonrefundable')
      ->with(['invoice' => $this->invoice]);
  }
}
