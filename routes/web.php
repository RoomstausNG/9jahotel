<?php

use App\Models\User;

use App\Livewire\BookRoom;
use App\Livewire\Register;
use App\Livewire\Dashboard;
use App\Livewire\LoginPage;
use App\Livewire\CreateHotel;
use App\Livewire\GroupBooking;
use App\Livewire\HotelDetails;
use App\Livewire\SearchResult;
use App\Models\BookingInvoice;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\PageController;
use Unicodeveloper\Paystack\Facades\Paystack;

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


Route::get('/successpayment/{reference?}', [PageController::class, 'success'])->name('payment.success');
Route::get('/', [PageController::class, 'homepage'])->middleware('page-cache');
Route::get('/state/{state:name}/hotels', [PageController::class, 'hotelByState'])->name('hotels.state');
Route::get('/terms-of-service', [PageController::class, 'term'])->name('terms');
Route::get('/privacy-policy', [PageController::class, 'policy'])->name('privacy');

Route::get('/home', Dashboard::class)->name('dashboard');

Route::get('/hotels/{city?}', SearchResult::class)->name('hotels.result');
Route::get('/hotel/{hotel:code}', HotelDetails::class)->name('hotels.show');

Route::get('/hotel/rooms/{category}', BookRoom::class)->name('bookroom');
Route::get('/signup/hotel', CreateHotel::class)->name('hotel.create');

Route::get("/login", LoginPage::class)->name('login')->middleware('page-cache');
Route::get("/register", Register::class)->name('register')->middleware('page-cache');

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
Route::view("/terms-and-condition", 'terms');


Route::get('/payment/callback', function () {
    $paymentDetails = Paystack::getPaymentData();
    $ref = $paymentDetails['data']['reference'];

    $inv = BookingInvoice::where('reference', $ref)->first();

    $done = $inv->update(['status' => 'paid', 'payed_on' => now()]);

    // update hotel renew date
    $inv->bookings->each(
        fn($booking) => $booking->update(['status' => 'paid', 'amount_paid' => $booking->due, 'mode_of_payment' => 'Online'])
    );

    // flush session
    session()->forget('city');
    session()->forget('checkin');
    session()->forget('checkout');

    return redirect()->route('payment.success', $inv->reference)->with(['invoice' => $inv]);
})->name('paystack.callback');
