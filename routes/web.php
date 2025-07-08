<?php

use App\Models\User;

use App\Models\Hotel;
use App\Livewire\BookRoom;
use App\Livewire\LoginPage;
use App\Livewire\CreateHotel;
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

Route::get('/invoice/payment/{id}', function ($id) {
    $invoice = \App\Models\Invoice::findOrFail($id);
    // Redirect to the payment gateway or invoice payment page
    return redirect('/successpayment/' . $invoice->reference);
})->name('invoice.payment');

Route::get('/coupon/print/{coupon}', function (\App\Models\Coupon $coupon) {
    return view('print.coupon', compact('coupon'));
})->name('coupon.print');

Route::get('/', function () {
    return redirect('hotel/login');
});
Route::get('/state/{state:name}/hotels', [PageController::class, 'hotelByState'])->name('hotels.state');

Route::get('/hotels/{city?}', SearchResult::class)->name('hotels.result');
Route::get('/hotel/{hotel:code}', HotelDetails::class)->name('hotels.show');

Route::get('/hotel/rooms/{category}', BookRoom::class)->name('bookroom');
Route::get('/signup/hotel', CreateHotel::class)->name('hotel.create');

Route::get("/signin", LoginPage::class);

Route::get('/session-debug', fn() =>
response()->json([
    'city' => Session::get('city'),
    'checkin' => Session::get('checkin'),
    'checkout' => Session::get('checkout'),
]))->name('session.debug');

Route::get('/test', function () {
    $hotels = Hotel::all();
    $count = 0;
    foreach ($hotels as $hotel) {
        $done = updateHotelLowestPrices($hotel->id);

        if ($done) {
            $count++;
        }
    }
    return response()->json(['Successfully' => "{$count} hotels got their updated out of {$hotels->count()} hotels"]);
});



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
