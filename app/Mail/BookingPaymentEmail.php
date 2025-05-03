<?php
// filepath: /Users/fade_networker/Documents/projects/php/wakalor/app/Mail/BookingPaymentEmail.php

namespace App\Mail;

use App\Models\BookingInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class BookingPaymentEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public BookingInvoice $invoice;

    /**
     * Create a new message instance.
     */
    public function __construct(BookingInvoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Payment Confirmation for Your Booking')
            ->view('emails.booking-payment')
            ->with([
                'invoice' => $this->invoice,
                'customer' => $this->invoice->customer,
                'hotel' => $this->invoice->hotel,
                'bookings' => $this->invoice->bookings,
            ]);
    }
}
