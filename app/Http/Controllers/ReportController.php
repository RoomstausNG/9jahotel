<?php

namespace App\Http\Controllers;

use App\Mail\PayrollMail;
use App\Models\Hall;
use App\Models\Room;
use App\Models\User;
use App\Models\Order;
use App\Models\Booking;
use App\Models\Revenue;
use App\Models\Business;
use App\Models\Expenditure;
use App\Traits\ApiResponse;
use App\Mail\GuestListEmail;
use Illuminate\Http\Request;
use App\Models\CreditPayment;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\BookingResource;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\BookingCollection;
use App\Http\Resources\GuestListResource;
use App\Notifications\CreditNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Notification;
use App\Http\Resources\SalesReportCollection;
use App\Http\Resources\BookingReportCollection;
use App\Http\Resources\CreditPaymentCollection;
use App\Http\Resources\PayrollResource;

class ReportController extends Controller
{
    use ApiResponse;
    public $bookings;

    function room(Request $request)
    {
        $data = $request->validate(
            [
                'room' => 'required',
                'start' => ['required', 'date', 'before_or_equal:today'],
                'end' => ['required', 'date', 'after_or_equal:start']
            ],
            [
                'start.required' => 'The start date cannot be empty',
                'start.before_or_equal' => 'The start date must be a past date',
                'end.required' => 'The end date cannot be empty',
                'end.before_or_equal' => 'The start date cannot be a future date',
            ]
        );

        if ($data['room'] != 'all') {
            $room = Room::find($data['room']);
            $reports = Booking::where('bookable_type', 'App\Models\Room')
                ->where('bookable_id', $data['room'])
                ->whereDate('created_at', '>=', $data['start'])
                ->whereDate('created_at', '<=', $data['end'])->get();
            return $this->successResponse(new BookingReportCollection($reports), 'Report of room ' . $room->number . '(' . strtoupper($room->type->name) . ')');
        } else {
            $reports = Booking::where('bookable_type', 'App\Models\Room')
                ->whereDate('created_at', '>=', $data['start'])
                ->whereDate('created_at', '<=', $data['end'])->get();
            return $this->successResponse(new BookingReportCollection($reports), 'Report of all rooms');
        }
    }


    function hall(Request $request)
    {
        $data = $request->validate(
            [
                'hall' => 'required',
                'start' => ['required', 'date', 'before_or_equal:today'],
                'end' => ['required', 'date', 'after_or_equal:start']
            ],
            [
                'start.required' => 'The start date cannot be empty',
                'start.before_or_equal' => 'The start date must be a past date',
                'end.required' => 'The end date cannot be empty',
                'end.before_or_equal' => 'The start date cannot be a future date',
            ]
        );

        if ($data['hall'] != 'all') {
            $hall = Hall::find($data['hall']);
            $reports = Booking::where('bookable_type', 'App\Models\Hall')
                ->where('bookable_id', $data['hall'])
                ->whereDate('created_at', '>=', $data['start'])
                ->whereDate('created_at', '<=', $data['end'])->get();
            return $this->successResponse(new BookingReportCollection($reports), 'Report of hal ' . strtoupper($hall->name));
        } else {
            $reports = Booking::where('bookable_type', 'App\Models\Hall')
                ->whereDate('created_at', '>=', $data['start'])
                ->whereDate('created_at', '<=', $data['end'])->get();
            return $this->successResponse(new BookingReportCollection($reports), 'Report of all halls');
        }
    }

    function sale(Request $request)
    {
        $data = $request->validate(
            [
                'item_id' => ['required'],
                'start' => ['required', 'date', 'before_or_equal:today'],
                'end' => ['required', 'date', 'after_or_equal:start']
            ],
            [
                'start.required' => 'The start date cannot be empty',
                'start.before_or_equal' => 'The start date must be a past date',
                'end.required' => 'The end date cannot be empty',
                'end.before_or_equal' => 'The start date cannot be a future date',
            ]
        );


        if ($data['item_id'] != 'all') {
            $sales = Order::where('item_id', $data['item_id'])
                ->whereDate('created_at', '>=', $data['start'])
                ->whereDate('created_at', '<=', $data['end'])->get();
            return $this->successResponse(new SalesReportCollection($sales), 'Sales report');
        } else {
            // $sales = Order::allItemSales($data['start'], $data['end']);
            $sales = Order::whereDate('created_at', '>=', $data['start'])
                ->whereDate('created_at', '<=', $data['end'])->get();

            return $this->successResponse(new SalesReportCollection($sales), 'Sales report');
        }
    }

    public function corporate()
    {
        return $this->successResponse(CompanyResource::collection(Business::all()), 'List of all corporate account');
    }

    public function creditNotify(Business $corporate)
    {

        if ($corporate->balance < 0) {
            // $done = $business->notify(new CreditNotification($business));
            $done = Notification::send($corporate, new CreditNotification($corporate));

            return $this->successResponse(new CompanyResource($corporate), 'Payment reminder sent successfully');
        } else {
            return $this->errorResponse('Business has no outstanding debt');
        }
    }

    public function payment(Request $request, Business $corporate)
    {
        $data = $request->validate([
            'amount_paid' => ['required', 'numeric', 'min:500'],
        ], ['amount_paid.required' => 'amount must not be empty']);
        // dd($this->getBalance());
        $payed = $corporate->payments()->create([
            'amount_paid' => $data['amount_paid'],
            'amount_owed' => $corporate->balance
        ]);

        if ($payed) {
            $corporate->update([
                'balance' => $this->getBalance($corporate->balance, $data['amount_paid'])
            ]);
            return $this->successResponse(new CompanyResource($corporate), 'Payment made successfully', 201);
        }
    }


    public function getBalance($balance, $amount_paid)
    {
        return $balance + $amount_paid;
    }

    public function bookingHistories(Business $corporate)
    {
        $customers = $corporate->customers()->select('id')->pluck('id')->toArray();
        $bookings = Booking::whereIn('customer_id', $customers)->orderByDesc("id")->paginate(10);
        return $this->successResponse(new BookingReportCollection($bookings), 'Booking histories for ' . ucfirst($corporate->name));
    }

    public function paymentHistories(Business $corporate)
    {
        $payments = CreditPayment::where('business_id', $corporate->id)->paginate(24);
        return $this->successResponse(new CreditPaymentCollection($payments), 'List of credit payments by ' . $corporate->name);
    }

    public function profitAndLoss(Request $request)
    {
        $data = $request->validate(
            [
                'start' => ['required', 'date', 'before_or_equal:today'],
                'end' => ['required', 'date', 'after_or_equal:start']
            ],
            [
                'start.required' => 'The start date cannot be empty',
                'start.before_or_equal' => 'The start date must be a past date',
                'end.required' => 'The end date cannot be empty',
                'end.before_or_equal' => 'The start date cannot be a future date',
            ]
        );

        // expenditures
        $expenditures = Expenditure::has('finances')
            ->whereHas(
                'finances',
                fn (Builder $query) =>
                $query->whereDate('created_at', '>=', $data['start'])
                    ->whereDate('created_at', '<=', $data['end'])
                // fn (Builder $query) => $query->whereBetween('created_at', [$data['start'], $data['end']])
            )
            ->withSum('finances as total', 'amount')
            ->get();

        // incomes
        $incomes = Revenue::has('finances')
            ->whereHas(
                'finances',
                fn (Builder $query) =>
                $query->whereDate('created_at', '>=', $data['start'])
                    ->whereDate('created_at', '<=', $data['end'])
                // fn (Builder $query) => $query->whereBetween('created_at', [$data['start'], $data['end']])
            )->withSum('finances as total', 'amount')
            ->get();

        // hall reservation total income
        $hall = Booking::amountGenerated('App\Models\Hall', $data['start'], $data['end']);


        // room reservation total income
        $room = Booking::amountGenerated('App\Models\Room', $data['start'], $data['end']);

        // sales order
        $orders = Order::whereDate('created_at', '>=', $data['start'])
            ->whereDate('created_at', '<=', $data['end'])->sum('total_price');

        $result = [];

        $inco = [];

        $expenses = [];

        if ($expenditures) {
            foreach ($expenditures as $exp) {
                $expenses[$exp->source] = intval($exp->total);
            }
            $expenses['total expense'] = $expenditures->sum('total');
        }

        if ($incomes?->count()) {
            $totalRevenue = $orders + $room + $hall + $incomes->sum('total');
            foreach ($incomes as $inc) {
                $inco[$inc->source] = intval($inc->total);
            }
        } else {
            $totalRevenue = $orders + $room + $hall;
        }


        // revenues
        $result['revenues'] = [
            'room' => $room,
            'hall' => $hall,
            'orders' => intval($orders),
            'Total Revenue' =>  $totalRevenue
        ];

        $result['revenues'] = array_merge($result['revenues'], $inco);

        return $this->successResponse(array_merge($result, ['expenditures' => $expenses, 'balance' => $totalRevenue - $expenditures->sum('total')]), 'Profit and Loss report');
    }

    public function guestList(Request $request)
    {
        $data = $request->validate(
            [
                'date' => ['required', 'date', 'before_or_equal:today'],
            ],
            [
                'date.before_or_equal' => 'The start date cannot be a future date',
            ]
        );

        $bookings = Booking::where('bookable_type', 'App\Models\Room')
            ->whereHas('statuses', fn (Builder $query) => $query->where('date', $data['date']))
            ->where('status', 'occupied')->paginate(24);

        $this->bookings = $bookings;

        return GuestListResource::collection($bookings);
    }

    function sendToEmail(Request $request)
    {
        $data = $request->validate(
            [
                'date' => ['required', 'date', 'before_or_equal:today'],
                'email' => ['required', 'email']
            ],
            [
                'date.before_or_equal' => 'The start date cannot be a future date',
            ]
        );

        $data['hotel'] = myHotel();

        $data['bookings'] = Booking::where('bookable_type', 'App\Models\Room')
            ->whereHas('statuses', fn (Builder $query) => $query->where('date', $data['date']))
            ->where('status', 'occupied')->paginate(24);

        try {
            // send to dss
            $done = Mail::to($data['email'])
                ->cc('info@roomstatus.uk')
                ->send(new GuestListEmail($data));

            return $this->successResponse($data, 'Email sent successfully!', 201); // 👈
        } catch (\Throwable $th) {
            return $this->errorResponse('Error sending email successfully! ' . $th->getMessage()); // 👈
        }
    }

    public function payRoll()
    {
        $staff = User::role(['manager', 'frontdesk', 'frontdesk-manager', 'sales-staff', 'sales-manager'])->where('hotel_id', auth()->user()->hotel_id)->get();
        return $this->successResponse(PayrollResource::collection($staff), 'Staff payroll');
    }


    public function payRollNotify(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email']
        ]);

        $staff = User::where('email', $data['email'])->first();

        try {
            // send to dss
            $done = Mail::to($data['email'])
                // ->cc('info@roomstatus.uk')
                ->send(new PayrollMail($staff));

            return $this->successResponse($staff, 'Email sent successfully!', 201); // 👈
        } catch (\Throwable $th) {
            return $this->errorResponse('Error sending email successfully! ' . $th->getMessage()); // 👈
        }
    }
}
