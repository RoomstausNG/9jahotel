<?php

namespace App\Http\Controllers\API;

use App\Models\Room;
use App\Models\Type;
use App\Models\Hotel;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Scopes\HotelScope;
use App\Http\Controllers\Controller;
use App\Http\Resources\HotelResource;
use App\Http\Resources\ImageResource;
use App\Http\Resources\RoomsResource;
use App\Http\Resources\HotelsResource;
use Filament\Notifications\Collection;
use App\Http\Resources\RoomCategoryResource;
use App\Http\Resources\RoomCategoriesResource;

class HotelController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = $request->validate([
            'checked_in' => ['required', 'date'],
            'checked_out' => ['required', 'date', 'after_or_equal:checked_in'],
            'city_id' => 'required',
        ], ['city_id.required' => 'The city is field cannot be empty']);

        $hotels = HotelsResource::collection(Hotel::where('city_id', $request->city_id)->paginate(24));

        return $hotels;
    }

    /**
     * Display the specified resource.
     */

    public function show(Hotel $hotel): HotelResource
    {
        $hotel->load('roomCategory');
        return new HotelResource($hotel);
    }



    public function showCategories(Hotel $hotel)
    {
        $hotel->load('roomCategory');
        return $this->successResponse(RoomCategoriesResource::collection($hotel->roomCategory), 'Hotel Categories');
    }


    public function getRooms($cat)
    {
        $category = Type::find($cat);

        return $this->successResponse(RoomsResource::collection($category->rooms->filter(fn($room) => $room->isAvailable(request()->checkin, request()->checkout))), 'Available Rooms');
    }

    public function roomImages(Room $room)
    {
        return $this->successResponse(ImageResource::collection($room->images), 'Room Images');
    }

    public function getRoomByHotel(Hotel $hotel)
    {
        $rooms = Room::withoutGlobalScope(HotelScope::class)->all();
        $rooms->load('type');
        return RoomsResource::collection($rooms);
    }
}