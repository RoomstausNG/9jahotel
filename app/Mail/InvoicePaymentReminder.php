<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Hotel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoicePaymentReminder extends Mailable implements ShouldQueue
{
  use Queueable, SerializesModels;

  public $invoice;
  public $hotel;

  /**
   * Create a new message instance.
   */
  public function __construct(Invoice $invoice, Hotel $hotel)
  {
    $this->invoice = $invoice;
    $this->hotel = $hotel;
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope
  {
    return new Envelope(
      subject: 'Payment Reminder - Invoice ' . $this->invoice->reference,
    );
  }

  /**
   * Get the message content definition.
   */
  public function content(): Content
  {
    return new Content(
      markdown: 'emails.invoices.reminder',
      with: [
        'invoice' => $this->invoice,
        'hotel' => $this->hotel,
        'paymentUrl' => route('invoice.payment', $this->invoice->id),
      ],
    );
  }

  /**
   * Get the attachments for the message.
   *
   * @return array<int, \Illuminate\Mail\Mailables\Attachment>
   */
  public function attachments(): array
  {
    return [];
  }
}
