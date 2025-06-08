<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\BookingInvoice;

class PaymentConfirmationCustomerMail extends Mailable
{
    use Queueable, SerializesModels;

    public BookingInvoice $invoice;

    public function __construct(BookingInvoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function build()
    {
        return $this->subject('Payment Confirmation - 9jahotel')
            ->view('emails.payment-confirmation-customer')
            ->with([
                'invoice' => $this->invoice,
                'customer' => $this->invoice->customer,
                'hotel' => $this->invoice->hotel,
                'bookings' => $this->invoice->bookings,
            ]);
    }
}
