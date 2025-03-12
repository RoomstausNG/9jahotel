<?php

namespace App\Notifications;

use App\Models\Hall;
use App\Models\Room;
use App\Models\Booking;
use App\Models\Scopes\HotelScope;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class HotelBookingNotification extends Notification implements ShouldQueue
{
    use Queueable;
    public $booking, $bookable;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;

        if ($booking->agent_id == null) {
            if ($booking->type == 'room') {
                $this->bookable = Room::withoutGlobalScope(HotelScope::class)->find($booking->bookable_id);
            } else {
                $this->bookable = Hall::withoutGlobalScope(HotelScope::class)->find($booking->bookable_id);
            }
        } else {
            if ($booking->type == 'room') {
                $this->bookable = Room::find($booking->bookable_id);
            } else {
                $this->bookable = Hall::find($booking->bookable_id);
            }
        }
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Booking')
            // ->from(getCurrentTenant()->email, getCurrentTenant()->name)
            ->view('emails.hotel-booking', ['booking' => $this->booking, 'bookable' => $this->bookable]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}