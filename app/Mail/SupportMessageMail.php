<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupportMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $phone;
    public $email;
    public $messageText;

    public function __construct($name, $phone, $email, $messageText)
    {
        $this->name = $name;
        $this->phone = $phone;
        $this->email = $email;
        $this->messageText = $messageText;
    }

    public function build()
    {
        return $this->subject('New Support Request')
            ->view('emails.support-message');
    }
}
