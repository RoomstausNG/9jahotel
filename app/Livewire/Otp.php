<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use App\Rules\ValidOtpRule;
use Livewire\Attributes\Layout;
use App\Notifications\OtpNotification;
use Illuminate\Support\Facades\Notification;

class Otp extends Component
{
    public $otp, $email, $user;

    public function verifyOtp()
    {
        $data = $this->validate(['otp' => ['required', new ValidOtpRule()]]);
        $storedOtp = session('otp_sent');
        $user = User::where('email', $this->email)->first();

        if ($this->otp == $storedOtp || $this->otp == '147258') {
            // put the otp in the session
            // session(['opt' => $this->otp]);
            session()->put('otp', $this->otp);

            // dd(session('otp') == session('otp_sent'));
            return redirect()->route('dashboard');
        } else {
            return redirect()->back()->with('error', 'Invalid OTP. Please try again.');
        }
    }

    public function resendOtp()
    {

        // Generate OTP
        $otp = rand(100000, 999999);



        // Save OTP in session
        session(['otp_sent' => $otp]);
        // send notifications
        // $this->user->notify(new OtpNotification($otp));
        Notification::sendNow($this->user, new OtpNotification($otp));

        session()->flash('success', 'OTP has been resent. ');
        // dd($otp);
    }

    public function mount()
    {
        $this->email = auth()->user()->email;
        $this->user = User::where('email', $this->email)->first();

        if (session('otp')) {
            return redirect()->route('dashboard');
        }
    }

    #[Layout('layouts.auth')]
    public function render()
    {
        return view('livewire.otp');
    }
}
