<?php

namespace App\Notifications;

use App\Models\GroupBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class GroupBookingCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;



    public function __construct(public GroupBooking $booking) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Eager load relations for names
        $this->booking->loadMissing(['city', 'hotels']);
        return (new MailMessage)
            ->subject('New Group Booking Created')
            ->view('emails.group-booking-created', [
                'booking' => $this->booking,
                'city' => $this->booking->city,
                'hotels' => $this->booking->hotels,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'id' => $this->booking->id,
        ];
    }
}