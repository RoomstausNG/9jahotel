<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Type;
use App\Models\Hotel;
use App\Models\Booking;
use App\Rules\AvailableRule;
use Illuminate\Http\Request;
use App\Models\Scopes\HotelScope;
use App\Http\Resources\HotelResource;
use App\Http\Resources\RoomsResource;
use App\Http\Resources\RoomCategoryResource;
use App\Models\Customer;

class FrontendApiController extends Controller
{
    public $bookable_type = 'App\Models\Room';
    public function show(Hotel $hotel)
    {
        $hotel->load('roomCategory');
        return new HotelResource($hotel);
    }

    public function getRoomsByType($cat)
    {
        $category = Type::withoutGlobalScope(HotelScope::class)->find($cat);
        $category->load('rooms');
        return new RoomCategoryResource($category);
    }

    public function getRoomsByHotel(Hotel $hotel, Request $request)
    {
        $data = $request->validate([
            'checked_in' => ['required', 'date'],
            'checked_out' => ['required', 'date'],
        ]);

        $rooms = Room::withoutGlobalScope(HotelScope::class)->where('hotel_id', $hotel->id)
            ->whereNotIn('status', ['repair', 'occupied', 'dirty'])
            ->whereDoesntHave('statuses', function ($query) use ($data) {
                $query->whereBetween('date', [$data['checked_in'], $data['checked_out']]);
                // $query->whereDate('date', $data['checked_in'])
                //     ->orWhereDate('date', $data['checked_out']);
            })->inRandomOrder()->get();

        $rooms->load('type');
        return RoomsResource::collection($rooms);
    }

    public function getSingleRoom($roomID)
    {
        $room = Room::withoutGlobalScope(HotelScope::class)->with('type')->find($roomID);
        // $type = Type::find($room->type_id);
        $room->load('type');
        return new RoomsResource($room, now());
    }

    public function bookARoom($roomID, Request $request)
    {
        $room = Room::withoutGlobalScope(HotelScope::class)->find($roomID);
        $data = $request->validate([
            'checked_in' => ['required', 'date', 'after:yesterday', new AvailableRule($roomID)],
            'checked_out' => ['required', 'date', 'after:checked_in', new AvailableRule($roomID)],
            'firstname' => ['required'],
            'lastname' => ['required'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'numeric']
        ]);

        $customer = $this->getOrCreateCustomer($data);
        $booking = $this->createBooking($room, $data, $customer);

        if (!$customer->hotels->contains($room->hotel_id)) {
            $customer->hotels()->attach($room->hotel_id);
        }

        if ($booking) {
            // $booking->calculateDue();
            return $this->calculateDue($booking, $room);
            // return $booking;
        }

        // if ($created) {
        //     // update the amount due of bookings.
        //     $normal = $created->bookable->type->price1 * calculateDateDifferenceInDays($created->checked_in, $created->checked_out);
        //     $discounted = $normal - ($normal * $created->discount / 100);
        //     $created->update(['due' => $created->discount > 0 ? $discounted : $normal]);
        //     return $created;
        // }
    }

    public function getOrCreateCustomer($data): Customer
    {
        return Customer::firstOrCreate(
            ['email' => $data['email']],
            [
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'phone' => $data['phone'],
            ]
        );
    }

    function createBooking($room, $data, $customer): Booking
    {
        return Booking::create([
            'bookable_id' => $room->id,
            'bookable_type' => 'App\Models\Room',
            'type' => 'room',
            'discount' => 0,
            'checked_in' => $data['checked_in'],
            'checked_out' => $data['checked_out'],
            'status' => 'unpaid',
            'agent_id' => null,
            'hotel_id' => $room->hotel_id,
            'customer_id' => $customer->id
        ]);
    }
    public function calculateDue($booking, $room)
    {
        $normal = $room->type->price1 * calculateDateDifferenceInDays($booking->checked_in, $booking->checked_out);
        $discounted = $normal - ($normal * $booking->discount / 100);
        $booking->update([
            // 'hotel_id' => $room->hotel_id,
            'due' => $booking->discount > 0 ? $discounted : $normal
        ]);
        return $booking;
    }
}