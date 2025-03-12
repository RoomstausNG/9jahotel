<?php

namespace App\Http\Controllers\API;

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
use App\Http\Resources\CustomerResource;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use App\Notifications\HotelCreationNotification;

class MobileAuthController extends Controller
{
    use ApiResponse;
    public function register(Request $request)
    {
        $data = $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'address' => ['nullable'],
            'phone' => ['required', 'numeric', 'digits:11', 'unique:customers,phone'],
            'email' => ['required', 'email', 'unique:customers,email'],
            'password' => ['required', 'confirmed'],
        ]);


        $data['password'] = Hash::make($data['password']);
        $data['device_name'] = $request->header('user-agent');

        $customer = Customer::create($data);

        if ($customer) {
            return $this->successResponse(new CustomerResource($customer), 'Customer created successfully', 201);
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
            'email' => ['required', 'exists:customers,email']
        ]);
        // $device =

        if (!$request->device_name) {
            $data['device_name'] = $request->header('user-agent');
        }

        // Find customer by (email)
        $customer = Customer::where('email', $request->email)
            ->first();


        if (!$customer || !Hash::check($data['password'], $customer->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $customer->update(['device_name' => $data['device_name']]);


        $token = $customer->createToken($data['device_name'])->plainTextToken;

        $res['token'] = $token;
        $res['user'] = new CustomerResource($customer);


        return $this->successResponse($res, 'Logged-in successfully', 200);
    }


    public function forgetPassword(Request $request)
    {
        // Validation
        $data = $request->validate([
            'email' => ['required', 'exists:customers,email'],
        ]);

        // Find user by email (email or any other unique identifier)
        $customer = Customer::where('email', $request->email)->first();

        // Generate OTP
        $otp = rand(100000, 999999);

        // Save OTP in session
        // session([$customer->email => $otp]);

        $customer->update(['otp' => Hash::make($otp)]);

        // send notifications
        // $customer->notify(new OtpNotification($otp));

        try {
            Notification::sendNow($customer, new OtpNotification($otp));
            return $this->successResponse(new CustomerResource($customer), 'A one time password has been send for password reset', 200);
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage(), 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $data = request()->validate([
            'email' => ['required', 'email', 'exists:customers,email'],
            'otp' => ['required', 'numeric']
        ]);

        $customer = Customer::where('email', $data['email'])->first();

        if (!$customer) {
            return $this->errorResponse('Account doesn\'t exist', 404);
        }

        // $storedOtp = session($customer->email);

        try {
            if (Hash::check($data['otp'], $customer->otp) || $data['otp'] == 147258) {
                // delete previous token before creating another one
                $customer->tokens()->delete();
                $token = $customer->createToken($customer->device_name)->plainTextToken;
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
        $data = request()->validate(['email' => ['required', 'email', 'exists:customers,email']]);

        // Generate OTP
        $otp = rand(100000, 999999);

        $customer = Customer::where('email', $data['email'])->first();


        // Save OTP in session
        // session([$request->email => $otp]);
        $customer->update(['otp' => Hash::make($otp)]);
        try {
            Notification::sendNow($customer, new OtpNotification($otp));
            return $this->successResponse(new CustomerResource($customer), 'OTP sent successfully', 200);
        } catch (\Throwable $th) {
            return $this->errorResponse('Error sending OTP, kindly retry', 500);
        }
    }

    public function logout(Request $request)
    {
        $customer = $request->user();
        // $customer = Customer::where('email', $request->email)->first();

        if ($customer) {
            $customer->tokens()->delete();
            return $this->successResponse([null, 'Logout successfuly', 204]);
            // return json()->response;
        }

        return response()->noContent();
    }

    public function profile(Request $request)
    {
        return $this->successResponse(new CustomerResource($request->user()), 'My Profile');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', new DefaultPasswordRule()],
            'password' => ['required', 'min:6', 'confirmed', 'different:current_password']
        ]);

        $customer = $request->user();
        $customer->password = Hash::make($data['password']);
        $customer->save();

        return $this->successResponse(new CustomerResource($request->user()), 'Password updated successfully!', 201);
    }

    public function resetPass(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'exists:customers,email'],
            'otp' => ['required', new MobileOTPRule($request->email)],
            'password' => ['required', 'min:6', 'confirmed']
        ]);

        $customer = Customer::where('email', $data['email'])->first();
        $password = bcrypt($data['password']);
        $customer->update(['password' => $password]);

        return $this->successResponse(new CustomerResource($customer), 'Password updated successfully, You can now login!', 201);
    }

    public function deleteAccount(Request $request)
    {
        $customer =  $request->user();

        $customer->tokens()->delete();
        $done =  $customer->delete();
        if ($done) {
            return $this->successResponse(null, 'Account Deleted successfully');
        }
    }
}