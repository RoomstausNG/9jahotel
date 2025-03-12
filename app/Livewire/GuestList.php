<?php

namespace App\Livewire;

use App\Mail\GuestListEmail;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Jantinnerezo\LivewireAlert\LivewireAlert;

use function Termwind\render;

class GuestList extends Component
{
    use LivewireAlert;

    public $bookings, $email;
    public $perPage = 10;
    public $date, $start, $end, $showTable = false;


    function generateList()
    {
        $data = $this->validate(
            [
                'date' => ['required', 'date', 'before_or_equal:today'],
            ],
            [
                'date.before_or_equal' => 'The start date cannot be a future date',
            ]
        );

        $this->bookings = Booking::where('bookable_type', 'App\Models\Room')
            ->whereHas('statuses', fn (Builder $query) => $query->where('date', $data['date']))
            ->where('status', 'occupied')->get();

        $this->showTable = true;
    }

    function sendList()
    {
        $this->validate(['email' => 'required|email']);

        $data['hotel'] = myHotel();
        $data['date'] = $this->date;
        $data['bookings'] = $this->bookings;

        try {
            // send to dss
            $done = Mail::to($this->email)
                ->cc('info@roomstatus.uk')
                ->send(new GuestListEmail($data));

            $this->alert('success', 'Email sent successfully!'); // 👈
            $this->reset('email');
        } catch (\Throwable $th) {
            $this->alert('error', 'Error sending email successfully! ' . $th->getMessage()); // 👈
            $this->reset('email');
        }
    }
    function viewList()
    {
        // $this->validate(['email' => 'required|email']);

        $data['hotel'] = myHotel();
        $data['date'] = $this->date;
        $data['bookings'] = $this->bookings;

        dd($data);

        return (new GuestListEmail($data))->render();
    }

    public function render()
    {
        return view('livewire.guest-list');
    }
}
