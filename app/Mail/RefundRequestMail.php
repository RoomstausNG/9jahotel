<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RefundRequestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public $data) {}

    public function build()
    {
        return $this->subject('Refund Request for Booking Invoice')
            ->view('emails.refund-request')
            ->with(['data' => $this->data]);
    }
}