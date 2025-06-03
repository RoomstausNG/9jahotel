<?php
// filepath: /Users/fade_networker/Documents/projects/php/wakalor/app/Mail/BookingInvoiceEmail.php

namespace App\Mail;

use App\Models\BookingInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class BookingInvoiceEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;


    /**
     * Create a new message instance.
     */
    public function __construct(public BookingInvoice $invoice, public string $customMessage) {}

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Your Booking Invoice')
            ->view('emails.booking-invoice')
            ->with([
                'invoice' => $this->invoice,
                'customer' => $this->invoice->customer,
                'hotel' => $this->invoice->hotel,
                'bookings' => $this->invoice->bookings,
                'customMessage' => $this->customMessage,
            ]);
    }
}
