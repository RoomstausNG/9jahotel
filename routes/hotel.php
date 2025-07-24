<?php

use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\CustomerResource;
use App\Http\Controllers\API\MobileHotelController;
use App\Http\Controllers\Api\BookingNotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
 */

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:customer')->get('/customer', function (Request $request) {
    return new CustomerResource(Auth::guard('customer')->user());
});


Route::post('/login', [MobileHotelController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [MobileHotelController::class, 'logout']);
    Route::get('/profile', [MobileHotelController::class, 'profile']);
    Route::post('/password/update', [MobileHotelController::class, 'updatePassword']);
    Route::get('/bookings', [MobileHotelController::class, 'bookings']);
    Route::get('/bookings/today', [MobileHotelController::class, 'todaysBookings']);
    Route::get('/stats', [MobileHotelController::class, 'stats']);

    Route::get('/rooms', [MobileHotelController::class, 'rooms']);
    Route::get('/halls', [MobileHotelController::class, 'halls']);
    Route::get('/room-categories', [MobileHotelController::class, 'roomCategories']);
    Route::patch('/room/{room}', [MobileHotelController::class, 'makeRoomAvailable']);
    Route::patch('/hall/{hall}', [MobileHotelController::class, 'makeHallAvailable']);
    Route::patch('/room/{room}/set-unavailability', [MobileHotelController::class, 'setRoomUnavailability']);
    Route::patch('/hall/{hall}/set-unavailability', [MobileHotelController::class, 'setHallUnavailability']);
});

// Booking Notification API Routes
// These routes are used for the booking notification system, including FCM push notifications for hotel bookings.
// Public endpoint for notifications
Route::post('/bookings/notify', [BookingNotificationController::class, 'notifyNewBooking'])->name('api.bookings.notify');

// Test endpoint for FCM (for development only)
Route::post('/test-fcm', [App\Http\Controllers\Api\FCMTestController::class, 'testFcm']);

// Authenticated endpoints (for booking notifications)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/{hotel}/notifications/unread-count', [BookingNotificationController::class, 'getUnreadCount']);
    Route::post('/{hotel}/notifications/mark-read', [BookingNotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/{notification}', [BookingNotificationController::class, 'getNotificationDetails']);
    Route::post('/user/fcm-token', [BookingNotificationController::class, 'updateFcmToken']);
});

// // for sample website
// Route::group(['prefix' => 'frontend'], function () {
//     Route::get('/', fn () => response()->json(['data' => "its working"]));

//     // get hotel room types
//     Route::get('{hotel:code}/category', [FrontendApiController::class, 'show']);

//     // get rooms under a particular caregory
//     Route::get('/hotel/categories/{cat:id}', [FrontendApiController::class, 'getRoomsByType']);

//     // gets rooms under an hotel
//     Route::post('/{hotel:code}/rooms', [FrontendApiController::class, 'getRoomsByHotel']);

//     // gets single room
//     Route::get('/room/{room:id}', [FrontendApiController::class, 'getSingleRoom']);


//     // book a room
//     Route::post('/{room:id}/reserve', [FrontendApiController::class, 'bookARoom']);
// });
