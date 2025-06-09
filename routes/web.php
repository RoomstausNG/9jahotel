<?php

use App\Models\User;
use App\Livewire\PayNow;
use App\Livewire\BookHall;
use App\Livewire\BookRoom;
use App\Livewire\Register;
use App\Livewire\Dashboard;
use App\Livewire\Favorites;
use App\Livewire\HotelHall;
use App\Livewire\LoginPage;
use App\Livewire\HallResult;
use App\Livewire\SignupPage;
use App\Livewire\CreateHotel;
use App\Livewire\GroupBooking;
use App\Livewire\HotelDetails;
use App\Livewire\SearchResult;
use App\Models\BookingInvoice;
use App\Livewire\BookingHistory;
use App\Livewire\OpayPaymentForm;
use App\Http\Livewire\RefundRequest;
use App\Livewire\ForgotPasswordPage;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\PageController;
use App\Livewire\RefundRequest as RRequest;
use Unicodeveloper\Paystack\Facades\Paystack;
use App\Http\Controllers\OpayCallbackController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// PayNow payment route with route model binding
Route::get('/paynow/{invoice:reference}', PayNow::class)->name('paynow');


use App\Livewire\CancelBooking;
// Booking cancellation route
Route::get('/cancel/{invoice:reference}', CancelBooking::class)->name('cancel.booking');

// Route::get('/payment/{reference?}', OpayPaymentForm::class)->name('payment.opay');
Route::get('/successpayment/{reference?}', [PageController::class, 'success'])->name('payment.success');
Route::get('/', [PageController::class, 'homepage'])->middleware('page-cache');
Route::get('/state/{state:name}/hotels', [PageController::class, 'hotelByState'])->name('hotels.state');
// Route::get('/terms-of-service', [PageController::class, 'term'])->name('terms');
Route::get('/privacy-policy', [PageController::class, 'policy'])->name('privacy');
Route::get('/support', [PageController::class, 'support'])->name('support');

Route::get('/home', Dashboard::class)->name('dashboard')->middleware('auth:customers');
Route::post('/customer/logout', function () {
    auth('customers')->logout();
    // Session::flush(); // Ensure all session data is cleared
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login'); // Redirect to the correct login page
})->name('customer.logout');

Route::get('/hotels/halls/{city?}', HallResult::class)->name('halls.result');
Route::get('/hotels/{city?}', SearchResult::class)->name('hotels.result');
Route::get('/hotel/{hotel:code}', HotelDetails::class)->name('hotels.show');
Route::get('/hotel/{hotel:code}/halls', HotelHall::class)->name('hotels.halls');

Route::get('/hotel/rooms/{category}', BookRoom::class)->name('bookroom');
Route::get('/hotel/halls/{hall}', BookHall::class)->name('bookhall');
Route::get('/signup/hotel', CreateHotel::class)->name('hotel.create');

Route::get("/login", LoginPage::class)->name('login')->middleware('page-cache');
Route::get("/register", Register::class)->name('register')->middleware('page-cache');
Route::get('/signup', SignupPage::class)->name('signup')->middleware('page-cache');
Route::get('/forgot-password', ForgotPasswordPage::class)->name('password.request')->middleware('page-cache');

Route::get('/session-debug', function () {
    return response()->json([
        'city' => Session::get('city'),
        'checkin' => Session::get('checkin'),
        'checkout' => Session::get('checkout'),
    ]);
})->name('session.debug');

Route::get('/group-booking', GroupBooking::class)->name('group.booking');

Route::get('/test', function () {

    // $permissions = $user->getPermissionsViaRoles();
    // if (count($permissions) > 0) {
    //     foreach ($permissions as $permission) {
    //         $user->givePermissionTo($permission->name);
    //     }
    // }

    return ["user Image" => "milla"];
});

// pages

Route::view("/faqs", 'faq');
Route::view("/about-us", 'about');
Route::view("/cookies", 'cookies');
Route::view("/affiliate", 'affiliate');
Route::view("/privacy-statement", 'privacy-statement')->name('privacy');
Route::view("/content", 'content');
Route::view("/terms-and-condition", 'terms')->name('terms');

Route::get('/favorites', Favorites::class)->name('favorites')->middleware('auth:customers');

// Route::get('/profile', \App\Livewire\UserProfile::class)->name('profile');
Route::get('/profile', \App\Livewire\Profile::class)->name('profile')->middleware('auth:customers');
Route::get('/bookings', BookingHistory::class)->name('bookings')->middleware('auth:customers');

Route::post('/opay/callback', [OpayCallbackController::class, 'handleGatewayCallback'])->name('opay.callback');

Route::get('/opay/return/{reference?}', [OpayCallbackController::class, 'handleGatewayReturn'])->name('opay.return');

// Success and failure routes for payment callback redirects
Route::get('/booking/success', function () {
    return view('booking.success');
})->name('booking.success');

Route::get("/booking/refund/{invoice:reference}", RRequest::class)->name('booking.refund');

Route::get('/booking/failed', function () {
    return view('booking.failed');
})->name('booking.failed');
