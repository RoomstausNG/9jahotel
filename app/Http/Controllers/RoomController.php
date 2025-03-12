<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Booking;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Resources\RoomsResource;
use App\Http\Resources\RoomCollection;
use App\Http\Resources\BookingResource;

class RoomController extends Controller
{
    use ApiResponse;
    public function index(Request $request)
    {
        $rooms = Room::all();
        return $this->successResponse(new RoomCollection($rooms, $request->date), $request->date ? 'Room status for ' . $request->date : 'Rooms without date');
    }

    public function statuses(Request $request)
    {
        $data = [
            'All' => Room::count(),
            'available' => countStatus($request->date, 'available'),
            'unpaid' => countStatus($request->date, 'unpaid'),
            'paid' => countStatus($request->date, 'paid'),
            'occupied' => Room::where('status', 'occupied')->count(),
            'repair' => Room::where('status', 'repair')->count(),
            'dirty' => Room::where('status', 'dirty')->count()
        ];
        return $this->successResponse($data, $request->date);
    }

    public function show(Request $request, Room $roomID)
    {
        return $this->successResponse(new RoomsResource($roomID, $request->date));
    }

    public function bookings(Room $roomID)
    {
        $bookings = Booking::where('type', 'room')->where('bookable_id', $roomID->id)->orderByDesc('id')->paginate(20);
        // return $this->successResponse(BookingResource::collection($bookings));
        return BookingResource::collection($roomID->bookings()->orderByDesc('id')->paginate(20));
    }
}
