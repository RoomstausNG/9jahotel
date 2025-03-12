<?php

use Carbon\Carbon;
use App\Models\Room;
use App\Models\User;
use App\Models\Hotel;
use App\Models\Order;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
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

Route::get('/', function () {
    return view('index');
    // return auth()->check() ? redirect('/bookings/reservations') : redirect('/login'); //redirect to hompage if logged-in alfready or to login page
});
Route::view('/features', 'feature');
Route::view('/faq', 'faq');

Route::post('/signin', [\App\Http\Controllers\AuthController::class, 'login'])->name('signin');

Route::get('/register', \App\Livewire\CreateHotel::class)->name('signup');
// Route::get('/register', [\App\Http\Controllers\AuthController::class, 'register'])->name('signup');

Route::get('/test', function () {
    return bcrypt('Doyin02');
    // return Customer::where('email', 'oyenrt1@gmail.com')->whereRelation('hotels', 'hotel_id', auth()->user()->id)->first();
});

Route::middleware(['auth', 'verified-otp'])->group(function () {
    Route::get('/home', fn () => redirect('/bookings/reservations'))->name('dashboard');
    // Route::get('/home', \App\Livewire\AdminDashbaord::class)->name('dashboard');
    Route::get('/profile', \App\Livewire\UserProfile::class)->name('profile');
});

// verify and confirmed otp
Route::group(['middleware' => 'verified-otp'], function () {

    //check if the hotel is still active
    Route::group(['middleware' => 'expired'], function () {
        Route::prefix('configurations')->middleware(['auth'])->group(function () {
            Route::view('/', 'configuration_menu')->name('configuration');
            Route::get('/hotel', \App\Livewire\Configuration\HotelPage::class)->name('configuration.hotel');
            Route::get('/users', \App\Livewire\Configuration\UsersPage::class)->name('configuration.users');
            Route::get('/items', \App\Livewire\Configuration\ItemsPage::class)->name('configuration.items');
            Route::get('/corporate/account', \App\Livewire\BusinessAccountPage::class)->name('configuration.business');
            Route::get('/customers', \App\Livewire\Configuration\CustomerPage::class)->name('configuration.customers');
            Route::get('/rooms', \App\Livewire\Configuration\RoomPage::class)->name('configuration.rooms');
            Route::get('/halls', \App\Livewire\Configuration\HallPage::class)->name('configuration.halls');
            Route::get('/expenditures', \App\Livewire\ExpenditurePage::class)->name('configuration.expenditures');
            Route::get('/finances', \App\Livewire\Configuration\FinancePage::class)->name('configuration.finances');
        });

        Route::prefix('users')->middleware(['auth'])->group(function () {
            Route::get('/create', \App\Livewire\Users\CreateUser::class)->name('users.create');
            Route::get('/{user:username}', \App\Livewire\Users\Profile::class)->name('users.profile');
        });

        Route::prefix('bookings')->middleware(['auth'])->group(function () {
            Route::get('/sales', \App\Livewire\Sales::class)->name('sales');
            Route::get('/sales/{sale:code}/receipt', function (Order $sale) {
                return view('livewire.sales-receipt', compact(['sale']));
            })->name('sales.receipt');

            Route::get('/{booking:code}/receipt', function (Booking $booking) {
                return view('livewire.booking-receipt', compact(['booking']));
            })->name('booking.receipt');
            Route::get('/room/{book:number}', \App\Livewire\RoomHistory::class)->name('room.histories');
            Route::get('/hall/{hall}', \App\Livewire\HallHistory::class)->name('hall.histories');
            Route::get('/reservations', \App\Livewire\Reservation::class)->name('reservations');
            Route::get('/hall-reservations', \App\Livewire\HallReservation::class)->name('hall-reservations');
            Route::get('/cusomers/{customer}', \App\Livewire\CustomerHistories::class)->name('customer.histories');
            Route::get('/business/{business}', \App\Livewire\BusinessBookingHistories::class)->name('business.histories');
            Route::get('/payment/{business}', \App\Livewire\UpdateCreditPayment::class)->name('business.payment');
        });

        Route::prefix('reservations')->middleware(['auth'])->group(function () {
            Route::get('/room/{room}/{booking?}', \App\Livewire\AddRoomReservation::class)->name('room.reservation');
            Route::get('/hall/{hall}', \App\Livewire\AddHallReservation::class)->name('hall.reservation');
        });


        Route::prefix('reports')->middleware(['auth'])->group(function () {
            Route::view('/', 'reports.report_view')->name('report');
            Route::get('/revenues', \App\Livewire\RevenueReport::class)->name('report.revenue');
            Route::get('/profit-and-loss', \App\Livewire\ProfitAndLoss::class)->name('report.finance');
            Route::get('{staff:username}/salary', fn (User $staff) => view('gross_salary', compact(['staff'])))->name('salary');
            Route::get('/guestlist', \App\Livewire\GuestList::class)->name('guest-list');
            Route::get('/guestreport/{date}', \App\Livewire\GuestReport::class)->name('guest-report');
            Route::get('/payrollreport', \App\Livewire\PayrollReport::class)->name('payroll-report');
        });

        Route::get('/manual', \App\Livewire\Manual::class)->name('manual');
        Route::get('/supplies', \App\Livewire\HotelSuppliers::class)->name('supplies');
        Route::get('/invoices', \App\Livewire\InvoiceList::class)->name('invoice.index');
        Route::get('/invoice/{invoice:reference}', \App\Livewire\HotelSuppliers::class)->name('invoices.receipt');
    });
});

Route::get('/payment/callback', function () {
    $paymentDetails = Paystack::getPaymentData();
    $ref = substr($paymentDetails['data']['reference'], 0, 10);

    $inv = Invoice::where('reference', $ref)->first();

    $done = $inv->update(['status' => 'paid', 'date_paid' => now()]);

    // update hotel renew date
    $inv->hotel()->update(['renew' => $inv->to, 'status' => 'active']);

    return redirect()->route('invoice.index');
})->name('paystack.callback');

Route::get('/renewal/{hotel:code}', \App\Livewire\RenewalPage::class)->name('renewal')->middleware('auth');


Route::get('/auth/otp', \App\Livewire\Otp::class)->name('otp.page')->middleware('auth');
Route::get('/errors/419', fn () => view('errors.419'))->name('errors.419');
Route::get('/privacy-policy', fn () => view('policy'))->name('policy');
