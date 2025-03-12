<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Hash;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Validator;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use App\Actions\Fortify\UpdateUserProfileInformation;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());
            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        Fortify::loginView(function () {
            return view('auth.login');
        });


        // customized login
        // Fortify::authenticateUsing(function (Request $request) {
        //     $user = User::where('email', $request->username)
        //         ->orWhere('email', $request->username)
        //         ->whereRelation('hotel', 'code', trim($request->code))
        //         ->first();
        //     if ($user && Hash::check($request->password, $user->password)) {
        //         return $user;
        //     }
        // });
        Fortify::authenticateUsing(function (Request $request) {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string',
                'password' => ['required|string'],
                'code' => ['required', 'exists:hotels,code'],
            ]);

            if ($validator->fails()) {
                throw ValidationException::withMessages($validator->errors()->toArray());
            }

            $user = User::where('email', $request->username)
                ->orWhere('username', $request->username)
                ->first();

            if (!$user) {
                throw ValidationException::withMessages(['username' => 'Invalid username or email address.']);
            }

            // Assuming you have a relationship between User and Hotel
            $hotel = $user->hotel()->where('code', trim($request->code))->first();

            if (!$hotel) {
                throw ValidationException::withMessages(['code' => 'Invalid hotel code.']);
            }

            if (!Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages(['password' => 'Invalid password.']);
            }

            return $user;
        });
    }
}