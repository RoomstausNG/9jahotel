<?php

namespace App\Http\Controllers\API;

use App\Models\Room;
use App\Models\Booking;
use App\Models\Business;
use App\Models\Customer;
use App\Traits\ApiResponse;
use App\Rules\AvailableRule;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\OccupiedResource;
use App\Http\Resources\BookingCollection;

class BookingController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $bookings = Booking::all();
        return new BookingCollection($bookings);
        // return $this->successResponse($bookings);
    }

    public function getDueCal(Request $request)
    {
        $val = $request->validate([
            'bookable_id' => ['required', 'exists:rooms,id'],
            'checked_in' => ['required', 'date'],
            'checked_out' => ['required', 'date', 'after:checked_in'],
            'discount' => ['nullable', 'numeric']
        ]);

        $val['discount'] = $val['discount'] ?? 0;

        $price = Room::find($val['bookable_id'])->price;

        $days = calculateDateDifferenceInDays($val['checked_in'], $val['checked_out']);

        $discountPrice = ($price * $days * intval($val['discount'])) / 100;

        $due = $price * $days - $discountPrice;

        if (myHotel()->vat) {
            $due = $due * 1.075;
        }

        $data = [
            'Room Rate' => $price,
            'Day(s)' => $days,
            'Total amount' => $price * $days,
            'discount price' => $discountPrice,
            'VAT' => myHotel()->vat ? 'YES' : 'NO',
            'Total Due' => $due
        ];

        return $this->successResponse($data, 'Booking calculation summary');
    }

    public function hallBooking()
    {
        $bookings = Booking::where('type', 'hall')->get();
        return new BookingCollection($bookings);
        // return $this->successResponse($bookings);
    }

    public function roomBooking()
    {
        $bookings = Booking::where('type', 'room')->get();
        return new BookingCollection($bookings);
        // return $this->successResponse($bookings);
    }

    public function occupied()
    {
        $bookings = Booking::with(['customer:id,firstname,lastname', 'bookable.type:id,name'])
            ->where('status', 'occupied')
            ->whereHas('statuses', fn($query) => $query->whereDate('date', '<=', now())->where('status', 'occupied'))
            ->get();
        return OccupiedResource::collection($bookings);
        // return $this->successResponse($bookings);
    }

    public function customer(Request $request)
    {
        $data = $request->validate([
            'phone' => 'required|numeric'
        ]);

        //  $customer = Customer::where('phone', $data['phone'])->myCustomer()->first();


        $customer =  myHotel()->customers()->where('phone', $data['phone'])->first();

        // return new CustomerResource($customer);
        if ($customer) {
            return $this->successResponse(new CustomerResource($customer), 'Customer Found');
        } else {
            return $this->errorResponse('No customer with the provided phone number, kindly create an account', 404);
        }
    }

    public function modeOfPayment()
    {
        return $this->successResponse(['Transfer', 'Cash', 'Credit', 'Online', 'POS'], 'Payment mode');
    }

    public function companies(Request $request)
    {
        $businesses = Business::where('name', 'LIKE', "%{$request->name}%")->paginate(20);

        if ($businesses) {
            return $this->successResponse(new CompanyResource($businesses));
        } else {
            return $this->errorResponse('Your hotel has no corporate account yet, kindly create an account', 404);
        }
    }

    public function addBooking(Request $request, Room $room)
    {
        $data = $request->validate(
            [
                'firstname' => ['required'],
                'lastname' => ['required'],
                'phone' => ['required', 'numeric', 'digits:11'],
                'gender' => ['required'],
                'email' => ['nullable', 'email'],
                'country_id' => ['nullable'],
                'state_id' => ['nullable'],
                'discount' => ['numeric', 'max:100'],
                'city_id' => ['nullable', 'required_unless:state_id:null,null'],
                'address' => ['required', 'required_unless:city_id:null,null'],
                'date_of_birth' => ['nullable', 'date'],
                'business_id' => ['nullable', 'exists:businesses,id'],
                // 'bookable_id' => ['required'],
                'id_type' => ['required'],
                'due' => ['nullable', 'numeric'],
                'mode_of_payment' => 'required',
                'id_number' => ['required'],
                // 'checked_in' => ['required', 'after:yesterday', new AvailableRule($this->room->id)],
                'checked_in' => ['required', new AvailableRule($room->id)],
                'checked_out' => ['required', 'after:checked_in'],
                'amount_paid' => ['required', 'numeric']
            ],
            ['bookable_id.required' => 'No room selected kindly select a room']
        );

        $data['bookable_id'] = $room->id;

        $customer =  myHotel()->customers()->where('phone', $data['phone'])->first();

        $data['discount'] = $data['discount'] ?? 0;
        // calling getdues;
        $data['due'] = $data['due'] ?? $this->getDue($data);


        if ($customer) {
            $created = Booking::create([
                'bookable_id' => $data['bookable_id'],
                'bookable_type' => 'App\Models\Room',
                'mode_of_payment' => $data['mode_of_payment'],
                'type' => 'room',
                'checked_in' => $data['checked_in'],
                'checked_out' => $data['checked_out'],
                'amount_paid' => intval($data['amount_paid']),
                'discount' => intval($data['discount']),
                'status' => $data['amount_paid'] < $data['due'] ? 'unpaid' : 'paid',
                'due' => $data['due'],
                'customer_id' => $customer->id,
                'agent_id' => $request->user()->id
            ]);

            // associate customer if not exist before
            $this->associateCustomer($customer->id);


            if ($created) {
                return $this->successResponse(new BookingResource($created), 'Room Booked successfully', 201);
            }
        } else {
            $customer = Customer::create($data);
            $created =   Booking::create([
                'bookable_id' => $data['bookable_id'],
                'bookable_type' => 'App\Models\Room',
                'type' => 'room',
                'discount' => $data['discount'],
                'mode_of_payment' => $data['mode_of_payment'],
                'checked_in' => $data['checked_in'],
                'checked_out' => $data['checked_out'],
                'amount_paid' => $data['amount_paid'],
                'due' => $data['due'],
                'status' => $data['amount_paid'] < $data['due'] ? 'unpaid' : 'paid',
                'agent_id' => $request->user()->id,
                'customer_id' => $customer->id
            ]);
            // associate customer if not exist before
            $this->associateCustomer($customer->id);

            if ($created) {
                return $this->successResponse(new BookingResource($created), 'Room Booked successfully', 201);
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Booking $booking)
    {
        return $this->successResponse(new BookingResource($booking));
    }

    public function checkedIn(Booking $booking)
    {
        if (in_array(auth()->user()->current_role, ['admin', 'super-admin'])  || $booking->customer->business_id != null || $booking->due <= $booking->amount_paid) {
            $checkedin = $booking->update(['status' => 'occupied']);
            return $this->successResponse(new BookingResource($booking), 'Room checked-in successfully');
        } elseif ($booking->due > $booking->amount_paid) {
            return $this->errorResponse('You can\'t checkin customer, there is ' . $booking->due - $booking->amount_paid . ' to be paid', 401);
        }
    }

    function checkedOut(Booking $booking)
    {
        if ($booking->customer->business_id || ($booking->amount_paid >= $booking->due)) {

            $booking->update(['status' => 'dirty']);
            // update hall
            $booking->bookable()->update(['status' => 'dirty']);

            // update credit if neccessary
            // if ($booking->orders()->exists()) {
            //     $booking->orders->each(function ($order) {
            //         $order->update(['mode_of_payment' => 'Credit']);
            //     });
            // }


            return $this->successResponse(new BookingResource($booking), 'Customer checked successfully', 201);
        } else {
            return $this->errorResponse('Customer is still oweing sum of ' . $booking->due - $booking->amount_paid, 401);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateBooking(Request $request, Booking $booking)
    {
        $data = $request->validate([

            'checked_in' => ['required'],
            'checked_out' => ['required', 'after:checked_in'],
            'mode_of_payment' => ['required'],
            'amount_paid' => ['required', 'numeric'],
            'due' => ['nullable', 'numeric'],
            'discount' => ['nullable', 'numeric']
        ]);

        $data['bookable_id'] = $booking->bookable_id;
        $data['discount'] = $data['discount'] ?? 0;

        $data['due'] = $data['due'] ?? $this->getDue($data);

        // if (in_array($booking->status, ['dirty', 'available'])) {
        //     return $this->errorResponse('This booking cannot be updated because it\'s past due');
        // }

        $updated = $booking->update([
            'checked_in' => $data['checked_in'],
            'checked_out' => $data['checked_out'],
            'amount_paid' => $data['amount_paid'],
            'due' => $data['due'],
            'discount' => $data['discount'],
            'status' => $data['amount_paid'] < $data['due'] ? 'unpaid' : 'paid',
            'customer_id' => $booking->customer_id
        ]);

        if ($updated) {
            return $this->successResponse(new BookingResource($booking), 'Booking updated successfully', 201);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking)
    {
        $done = $booking->delete();

        return $this->successResponse(null, 'Booking deleted successfully', 200);
    }


    function associateCustomer($id)
    {
        $customer = Customer::find($id);

        if (!$customer->hotels()->find(auth()->user()->hotel_id)) {
            $customer->hotels()->attach(auth()->user()->hotel_id);
        }
    }

    function getDue($data)
    {
        $price = Room::find($data['bookable_id'])->price;

        $days = calculateDateDifferenceInDays($data['checked_in'], $data['checked_out']);

        $discountPrice = ($price * $days * intval($data['discount'])) / 100;

        $due = $price * $days - $discountPrice;

        if (myHotel()->vat) {
            return $due * 1.075;
        }

        return $due;
    }
}
