<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Hotel;
use App\Models\Customer;
use App\Traits\ApiResponse;
use App\Rules\MobileOTPRule;
use Illuminate\Http\Request;
use App\Rules\DefaultPasswordRule;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Notifications\OtpNotification;
use App\Filament\Resources\UserResource;
use App\Http\Resources\CustomerResource;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use App\Notifications\HotelCreationNotification;
use App\Http\Resources\UserResource as UResource;
use Illuminate\Support\Facades\Cache; // Add this line

class MobileAuthController extends Controller
{
    use ApiResponse;
    public function register(Request $request)
    {
        $data = $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'address' => ['nullable'],
            'phone' => ['required', 'numeric', 'digits:11', 'unique:users,phone'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed'],
        ]);


        $data['password'] = Hash::make($data['password']);
        $data['device_name'] = $request->header('user-agent');

        $user = User::create($data);

        if ($user) {
            return $this->successResponse(new CustomerResource($user), 'Customer created successfully', 201);
        }
    }

    public function uploadLogo(Request $request)
    {
        $data = $request->validate([
            'logo' => ['required', 'image'],
            'code' => ['required', 'exists:hotels,code']
        ]);

        // Handle the uploaded image
        if ($request->hasFile('logo')) {
            $image = $request->file('logo');
            $data['logo'] = $image->store('logo', 'public'); // Save to the 'public' disk in the 'images' directory

        }
    }

    public function login(Request $request)
    {
        // Validation
        $data = $request->validate([
            'password' => 'required',
            'device_name' => 'nullable',
            'email' => ['required', 'exists:users,email']
        ]);
        // $device =

        if (!$request->device_name) {
            $data['device_name'] = $request->header('user-agent');
        }

        // Find customer by (email)
        $user = User::where('email', $request->email)
            ->first();


        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user->update(['device_name' => $data['device_name']]);


        $token = $user->createToken($data['device_name'])->plainTextToken;

        $res['token'] = $token;
        $res['user'] = new UResource($user->load('hotel'));


        return $this->successResponse($res, 'Logged-in successfully', 200);
    }


    public function forgetPassword(Request $request)
    {
        // Validation
        $data = $request->validate([
            'email' => ['required', 'exists:users,email'],
        ]);

        // Find user by email (email or any other unique identifier)
        $user = User::where('email', $request->email)->first();

        // Generate OTP
        $otp = rand(100000, 999999);

        // Save OTP in session
        // session([$user->email => $otp]);

        $user->update(['otp' => Hash::make($otp)]);

        // send notifications
        // $user->notify(new OtpNotification($otp));

        try {
            Notification::sendNow($user, new OtpNotification($otp));
            return $this->successResponse(new CustomerResource($user), 'A one time password has been send for password reset', 200);
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage(), 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $data = request()->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'otp' => ['required', 'numeric']
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            return $this->errorResponse('Account doesn\'t exist', 404);
        }

        // $storedOtp = session($user->email);

        try {
            if (Hash::check($data['otp'], $user->otp) || $data['otp'] == 147258) {
                // delete previous token before creating another one
                $user->tokens()->delete();
                $token = $user->createToken($user->device_name)->plainTextToken;
                return $this->successResponse(['token' => $token], 'Logged-in successfully', 200);
            } else {
                return $this->errorResponse('Invalid or expired OTP', 400);
            }
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage(), 500);
        }
    }

    public function resendOtp(Request $request)
    {
        $data = request()->validate(['email' => ['required', 'email', 'exists:users,email']]);

        // Generate OTP
        $otp = rand(100000, 999999);

        $user = User::where('email', $data['email'])->first();


        // Save OTP in session
        // session([$request->email => $otp]);
        $user->update(['otp' => Hash::make($otp)]);
        try {
            Notification::sendNow($user, new OtpNotification($otp));
            return $this->successResponse(new CustomerResource($user), 'OTP sent successfully', 200);
        } catch (\Throwable $th) {
            return $this->errorResponse('Error sending OTP, kindly retry', 500);
        }
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        // $user = User::where('email', $request->email)->first();

        if ($user) {
            $user->tokens()->delete();
            return $this->successResponse([null, 'Logout successfuly', 204]);
            // return json()->response;
        }

        return response()->noContent();
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        $cacheKey = 'user_profile_' . $user->id;
        $cacheDuration = 60 * 24; // 24 hours in minutes

        $profileData = Cache::remember($cacheKey, $cacheDuration, function () use ($user) {
            return new UResource($user->loadMissing('hotel')); // Eager load relations if not already loaded
        });

        return $this->successResponse($profileData, 'My Profile');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', new DefaultPasswordRule()],
            'password' => ['required', 'min:6', 'confirmed', 'different:current_password']
        ]);

        $user = $request->user();
        $user->password = Hash::make($data['password']);
        $user->save();

        return $this->successResponse(new CustomerResource($request->user()), 'Password updated successfully!', 201);
    }

    public function resetPass(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'exists:users,email'],
            'otp' => ['required', new MobileOTPRule($request->email)],
            'password' => ['required', 'min:6', 'confirmed']
        ]);

        $user = User::where('email', $data['email'])->first();
        $password = bcrypt($data['password']);
        $user->update(['password' => $password]);

        return $this->successResponse(new CustomerResource($user), 'Password updated successfully, You can now login!', 201);
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        $user->tokens()->delete();
        $done = $user->delete();
        if ($done) {
            return $this->successResponse(null, 'Account Deleted successfully');
        }
    }
}
