<?php

use App\Models\User;

use App\Livewire\BookRoom;
use App\Livewire\LoginPage;
use App\Livewire\HotelDetails;
use App\Livewire\SearchResult;
use App\Models\BookingInvoice;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\PageController;
use App\Livewire\CreateHotel;
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
Route::redirect('/', "/hotel/login");
Route::get('/state/{state:name}/hotels', [PageController::class, 'hotelByState'])->name('hotels.state');

Route::get('/hotels/{city?}', SearchResult::class)->name('hotels.result');
Route::get('/hotel/{hotel:code}', HotelDetails::class)->name('hotels.show');

Route::get('/hotel/rooms/{category}', BookRoom::class)->name('bookroom');
Route::get('/signup/hotel', CreateHotel::class)->name('hotel.create');

Route::get("/signin", LoginPage::class);

Route::get('/session-debug', function () {
    return response()->json([
        'city' => Session::get('city'),
        'checkin' => Session::get('checkin'),
        'checkout' => Session::get('checkout'),
    ]);
})->name('session.debug');

Route::get(
    '/test',
    fn() =>

    // $permissions = $user->getPermissionsViaRoles();
    // if (count($permissions) > 0) {
    //     foreach ($permissions as $permission) {
    //         $user->givePermissionTo($permission->name);
    //     }
    // }
    ["user Image" => "milla"]
);



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
