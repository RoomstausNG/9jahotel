<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RefundConfirmationMail extends Mailable
{
  use Queueable, SerializesModels;

  public $customer;
  public $hotel;
  public $invoice;
  public $refundAmount;

  /**
   * Create a new message instance.
   */
  public function __construct($customer, $hotel, $invoice, $refundAmount = null)
  {
    $this->customer = $customer;
    $this->hotel = $hotel;
    $this->invoice = $invoice;
    $this->refundAmount = $refundAmount;
  }

  /**
   * Build the message.
   */
  public function build()
  {
    return $this->subject('Your Refund Request Has Been Received')
      ->view('emails.refund-confirmation');
  }
}
