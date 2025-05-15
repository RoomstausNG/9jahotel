<?php

use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\CustomerResource;
use App\Http\Controllers\API\CityController;
use App\Http\Controllers\API\HotelController;
use App\Http\Controllers\API\RatingController;
use App\Http\Controllers\FrontendApiController;
use App\Http\Controllers\API\MobileAuthController;
use App\Http\Controllers\API\MobileHotelController;

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

// // get a single hotels with categories under it
// Route::get('/hotel/{hotel:code}', [HotelController::class, 'show']);

// // get rooms under a room categories
// Route::get('/hotel/categories/{cat:id}', [HotelController::class, 'getRooms']);

// Route::post('/register', [MobileAuthController::class, 'register']);
Route::post('/login', [MobileAuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [MobileAuthController::class, 'logout']);
    Route::get('/profile', [MobileAuthController::class, 'profile']);
    Route::post('/password/update', [MobileAuthController::class, 'updatePassword']);

    Route::get('/rooms', [MobileHotelController::class, 'rooms']);
    Route::get('/halls', [MobileHotelController::class, 'halls']);
    Route::get('/room-categories', [MobileHotelController::class, 'roomCategories']);
    Route::patch('/room/{room}', [MobileHotelController::class, 'makeRoomAvailable']);
    Route::patch('/hall/{hall}', [MobileHotelController::class, 'makeHallAvailable']);
    Route::patch('/room/{room}/set-unavailability', [MobileHotelController::class, 'setRoomUnavailability']);
    Route::patch('/hall/{hall}/set-unavailability', [MobileHotelController::class, 'setHallUnavailability']);
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