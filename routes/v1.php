<?php

use App\Models\Favourite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\TeamController;
use App\Http\Resources\CustomerResource;
use App\Http\Controllers\API\CityController;
use App\Http\Controllers\API\HotelController;
use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\V1\FavouriteController;
use App\Http\Controllers\API\MobileAuthController;
use App\Http\Controllers\Api\BookingNotificationController as NotificationController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Public endpoints (protected by API key)
Route::middleware(['api.key'])->group(function () {
    Route::post('/bookings/notify', [NotificationController::class, 'notifyNewBooking']);
});

// Authenticated endpoints (for Flutter app)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/hotels/{hotel}/notifications/unread-count', [NotificationController::class, 'getUnreadCount']);
    Route::post('/hotels/{hotel}/notifications/mark-read', [NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/{notification}', [NotificationController::class, 'getNotificationDetails']);
    Route::post('/user/fcm-token', [NotificationController::class, 'updateFcmToken']);
});

Route::middleware('auth:customer')->get('/profile', function (Request $request) {
    return new CustomerResource(Auth::guard('customer')->user());
});

Route::group(['prefix' => 'teams'], function () {
    Route::apiResource('teams', TeamController::class);
    Route::post('hotels', [HotelController::class, 'store']);
});

// City route
Route::get('/city', [CityController::class, 'cities']);
Route::get('/states', [CityController::class, 'states']);
Route::get('/countries', [CityController::class, 'countries']);
Route::get('/ids', fn() => response()->json(['data' => ['International Passport', "Driver's License", 'NIN Card / Slip', 'Work ID Card', 'School ID Card', 'Bank Card', 'Birth Certificate', 'Other ID']], 200));

Route::post('/register', [MobileAuthController::class, 'register']);
Route::post('/login', [MobileAuthController::class, 'login']);
Route::post('/otp', [MobileAuthController::class, 'verifyOtp']);
Route::post('/otp/resend', [MobileAuthController::class, 'resendOtp']);
Route::post('/password/forget', [MobileAuthController::class, 'forgetPassword']);
Route::post('/password/reset', [MobileAuthController::class, 'resetPass']);

Route::post('/hotels', [HotelController::class, 'index']);
Route::get('/hotel/{hotel:code}', [HotelController::class, 'show']);
Route::get('/hotel/{hotel:code}/categories', [HotelController::class, 'showCategories']);

// rooms
Route::get('/category/{category}/rooms', [HotelController::class, 'getRooms']);

Route::get('/room/{room}/images', [HotelController::class, 'roomImages']);

// auth guard routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [MobileAuthController::class, 'profile']);
    Route::post('/logout', [MobileAuthController::class, 'logout']);
    Route::post('/password/update', [MobileAuthController::class, 'updatePassword']);
    Route::delete('/account/delete', [MobileAuthController::class, 'deleteAccount']);

    // booking route
    Route::prefix('bookings')->group(function () {
        Route::get('', [BookingController::class, 'index']);
    });

    Route::get('/favourites', [FavouriteController::class, 'favourites']);
    Route::post('/favourites/{hotel:code}', [FavouriteController::class, 'toggleFavourite']);
});

Route::post('/uploadlogo', [MobileAuthController::class, 'uploadLogo']);
