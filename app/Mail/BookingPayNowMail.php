<?php

namespace App\Mail;

use App\Models\BookingInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingPayNowMail extends Mailable
{
    use Queueable, SerializesModels;

    public BookingInvoice $invoice;

    public function __construct(BookingInvoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function build()
    {
        return $this->subject('Your Booking Confirmation - Refundable')
            ->view('emails.booking.paynow')
            ->with(['invoice' => $this->invoice]);
    }
}
