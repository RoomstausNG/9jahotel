<?php

namespace App\Livewire;

use App\Mail\BookingCancelledCustomerMail;
use App\Mail\BookingCancelledHotelMail;
use App\Models\BookingInvoice;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class CancelBooking extends Component
{
    public BookingInvoice $invoice;
    public $confirming = false;
    public $reason = '';
    public $other_reason = '';
    public $success = false;

    protected $rules = [
        'reason' => 'required|string',
        'other_reason' => 'nullable|string',
    ];

    public function mount(BookingInvoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function confirmCancel()
    {
        $this->confirming = true;
    }

    public function cancelBooking()
    {
        $this->validate();
        if ($this->invoice->status === 'cancelled') {
            $this->addError('cancel', 'This booking is already cancelled.');
            return;
        }
        DB::transaction(function () {
            $this->invoice->status = 'cancelled';
            //   $this->invoice->cancel_reason = $this->reason === 'other' ? $this->other_reason : $this->reason;
            $this->invoice->save();
            // Update all related bookings
            foreach ($this->invoice->bookings as $booking) {
                $booking->status = 'cancelled';
                $booking->save();
            }
        });
        // Send emails (queue)
        Mail::to($this->invoice->customer->email)
            ->queue(new BookingCancelledCustomerMail($this->invoice, $this->reason, $this->other_reason));
        $hotelEmail = $this->invoice->hotel->email ?? 'info@9jahotel.com';
        Mail::to($hotelEmail)
            ->queue(new BookingCancelledHotelMail($this->invoice, $this->reason, $this->other_reason));
        $this->success = true;
        Notification::make()
            ->title('Booking Cancelled')
            ->body('The booking has been cancelled and notifications sent.')
            ->success()
            ->send();
    }

    public function render()
    {
        $cancelReasons = [
            'Change of plans',
            'Found a better price',
            'Personal reasons',
            'Other',
        ];
        return view('livewire.cancel-booking', [
            'cancelReasons' => $cancelReasons,
            'invoice' => $this->invoice,
        ]);
    }
}