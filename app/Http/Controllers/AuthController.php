<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Notifications\OtpNotification;
use Illuminate\Support\Facades\Notification;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validation
        $request->validate([
            'username' => ['required', 'exists:users,username'],
            'password' => 'required',
            'code' => ['required', 'exists:hotels,code'],
        ]);

        // Find user by username (email or any other unique identifier)
        $user = User::where('email', $request->username)
            ->orWhere('username', $request->username)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return redirect()->back()->with('error', 'Invalid password');
        }

        // Assuming you have a relationship between User and Hotel
        $hotel = $user->hotel()->where('code', trim($request->code))->first();

        if (!$hotel) {
            return redirect()->back()->with('error', 'You are not linked to the hotel code')->withInput();
        }
        if (!$user->can_login) {
            return redirect()->back()->with('error', 'Your account is disabled, kindly contact the admin')->withInput();
        }


        // Manually log in the user
        Auth::login($user);


        // Generate OTP
        $otp = rand(100000, 999999);

        // Save OTP in session
        session(['otp_sent' => $otp]);

        // send notifications
        // $user->notify(new OtpNotification($otp));
        Notification::sendNow($user, new OtpNotification($otp));

        // Redirect authenticated user
        return redirect()->route('otp.page');
    }

    public function register()
    {
        return view('auth.signup');
    }
}
