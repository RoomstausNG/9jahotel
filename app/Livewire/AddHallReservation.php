<?php

namespace App\Livewire;

use App\Models\Hall;
use App\Models\Booking;
use Livewire\Component;
use App\Models\Customer;
use App\Rules\BookingDateAvailableRule;
use App\Rules\HallAvailableRule;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class AddHallReservation extends Component
{
    public Hall $hall;
    public $booking;
    public $bookings;

    public $firstname, $code, $name, $status, $customer_id, $discount = 0, $agent_id, $due, $amount_paid, $nationality, $bookable_id, $checked_in, $checked_out, $business, $lastname, $nok_name, $nok_phone, $nok_email, $avatar, $date_of_birth, $address, $gender, $phone, $email, $hotel_id, $city_id, $state_id, $country_id = 160, $business_id;
    use WithPagination, WithFileUploads;

    public $customer;
    public $newCustomer = false;
    public $bookable_type = "App\Models\Hall";
    public $perPage = 10;
    public $query = '';
    public $is_new = false;
    public $type = 'hall';

    public $typeForm = false;
    public $bookingForm = false;

    public $updateC = false;

    public function search()
    {
        $this->resetPage();
    }

    public $cid;
    public $confirm = false;
    public $update = false;
    public $form = false;

    public ?array $checked = [];
    public $sortField = 'id';
    public $sortAsc = true;

    function showForm()
    {
        $this->form = true;
    }



    public function sortBy($field)
    {

        if ($this->sortField == $field) {
            $this->sortAsc = !$this->sortAsc;
        } else {
            $this->sortAsc = true;
        }
        $this->sortField = $field;
    }

    function updatingSearch()
    {
        $this->resetPage();
    }

    function updatedSearch()
    {
        $this->resetPage();
    }

    function updatedPerPage()
    {
        $this->resetPage();
    }

    function confirmDelete(Booking $booking)
    {
        $this->business = $booking;
        $this->confirm = true;
    }

    function findCustomer()
    {
        $this->validate(['phone' => ['required', 'exists:customers,phone']], ['phone.exists' => 'No customer with the phone number']);
        $this->customer = Customer::where('phone', $this->phone)->first();
        $this->customer_id = $this->customer->id;
        $this->firstname = $this->customer->firstname;
        $this->lastname = $this->customer->lastname;
        $this->gender = $this->customer->gender;
        $this->business_id = $this->customer->business_id;
        $this->phone = $this->customer->phone;
        $this->email = $this->customer->email;
        $this->address = $this->customer->address;
        $this->country_id = $this->customer->country_id;
        $this->state_id = $this->customer->state_id;
        $this->city_id = $this->customer->city_id;
        $this->nationality = $this->customer->nationality;
    }


    function selectBookable($id)
    {
        $this->bookable_id = $id;
    }

    function editBooking(Booking $booking)
    {
        $this->booking = $booking;
        $this->updateC = true;
        $this->bookingForm = true;
        $this->customer = Customer::find($this->booking->customer_id);
        $this->customer_id = $this->booking->customer_id;
        $this->cid = $this->booking->id;
        $this->status = $this->booking->status;
        $this->checked_in = $this->booking->checked_in?->format('Y-m-d');
        $this->checked_out = $this->booking->checked_out?->format('Y-m-d');
        $this->amount_paid = $this->booking->amount_paid;
        $this->due = $this->booking->due;
        $this->discount = $this->booking->discount;
        $this->firstname = $this->customer->firstname;
        $this->lastname = $this->customer->lastname;
        $this->hotel_id = $this->booking->hotel_id;
        $this->email = $this->customer->email;
        $this->address = $this->customer->address;
        $this->city_id = $this->customer->city_id;
        $this->state_id = $this->customer->state_id;
        $this->gender = $this->customer->gender;
        $this->phone = $this->customer->phone;
        $this->country_id = $this->customer->country_id;
        $this->business_id = $this->customer->business_id;
    }


    function bookHall()
    {
        $data = $this->validate(
            [
                'firstname' => ['required'],
                'lastname' => ['required'],
                'phone' => ['required', 'numeric', 'digits:11', Rule::unique('customers')->ignore($this->customer_id)],
                'email' => ['required', 'email', Rule::unique('customers')->ignore($this->customer_id)],
                'country_id' => ['nullable'],
                'state_id' => ['nullable'],
                'discount' => ['nullable', 'numeric', 'max:100'],
                'city_id' => ['nullable', 'required_unless:state_id:null,null'],
                'address' => ['required', 'required_unless:city_id:null,null'],
                'date_of_birth' => ['nullable'],
                'business_id' => ['nullable'],
                'bookable_id' => ['required'],
                'checked_in' => ['required', 'after:yesterday', new HallAvailableRule($this->hall, $this->checked_in, $this->checked_out)],
                'checked_out' => ['required', 'after:checked_in'],
                'amount_paid' => ['required', 'numeric']
            ],
            ['bookable_id.required' => 'No hall selected kindly select a hall']
        );

        // calling getdues;
        $this->getDue();


        if ($this->customer_id) {
            $created = Booking::create([
                'bookable_id' => $data['bookable_id'],
                'bookable_type' => 'App\Models\Hall',
                'type' => 'hall',
                'checked_in' => $data['checked_in'],
                'checked_out' => $data['checked_out'],
                'amount_paid' => $data['amount_paid'],
                'discount' => $data['discount'],
                'status' => $this->amount_paid < $this->due ? 'unpaid' : 'paid',
                'due' => $this->getDue(),
                'customer_id' => $this->customer_id,
                'agent_id' => auth()->id()
            ]);

            // $find customer
            $this->dispatch('setCustomerSelect', []);
            if ($created) {
                $details = [
                    'text' => 'Hall booked successfully.',
                    'title' => 'Booking Added'
                ];
                return $this->dispatch('swal:success', details: sweetAlert(...$details));
            }
        } else {
            $customer = Customer::create($data);
            $created =   Booking::create([
                'bookable_id' => $data['bookable_id'],
                'bookable_type' => 'App\Models\Hall',
                'type' => 'hall',
                'discount' => $data['discount'],
                'checked_in' => $data['checked_in'],
                'checked_out' => $data['checked_out'],
                'amount_paid' => $data['amount_paid'],
                'due' => $this->getDue(),
                'status' => $this->amount_paid < $this->due ? 'unpaid' : 'paid',
                'agent_id' => auth()->id(),
                'customer_id' => $customer->id
            ]);

            if ($created) {
                // $this->reset();
                $details = [
                    'text' => 'Hall booked successfully.',
                    'title' => 'Booking Added'
                ];
                return $this->dispatch('swal:success', details: sweetAlert(...$details));
            }
        }
    }

    function checkedIn()
    {
        if (auth()->user()->current_role == 'admin' || auth()->user()->current_role == 'super-admin' || $this->booking->customer->business_id != null) {

            $this->booking->update([
                'bookable_id' => $this->bookable_id,
                'bookable_type' => 'App\Models\Hall',
                'type' => 'hall',
                'checked_in' => $this->checked_in,
                'checked_out' => $this->checked_out,
                'amount_paid' => $this->amount_paid,
                'discount' => $this->discount,
                'status' => 'occupied',
                'due' => $this->getDue(),
                'customer_id' => $this->customer_id,
                'agent_id' => auth()->id()
            ]);

            // update hall
            $this->booking->bookable()->update(['status' => 'occupied']);

            $details = [
                'text' => 'Customer checked-in successfully',
                'title' => 'Customer Checked In'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        } else {
            $details = [
                'icon' => 'warning',
                'text' => "A sum of" . formatAsNaira($this->booking->due) . " needed to be paid before checking in",
                'title' => 'Cannot Check-in customer'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    function checkedOut()
    {
        if ($this->customer->business_id || ($this->booking->amount_paid >= $this->booking->due)) {

            $this->booking->update(['status' => 'dirty']);

            // update hall
            $this->booking->bookable()->update(['status' => 'dirty']);
            $details = [
                'text' => 'Customer checked-out successfully, you can now clean the hall',
                'title' => 'Customer Checkedout'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        } else {
            $details = [
                'icon' => 'warning',
                'text' => "Customer is still oweing a sum of " . formatAsNaira($this->booking->due - $this->booking->amount_paid) . " You can't checkout customer, and it's not a credit customer.",
                'title' => 'Still owing'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    function updateBooking()
    {
        $booking = Booking::find($this->cid);

        if ($this->newCustomer) {
            // created new customer
            $data = $this->validate(
                [
                    'firstname' => ['required'],
                    'lastname' => ['required'],
                    'phone' => ['required', 'numeric', 'digits:11'],
                    'nok_name' => ['nullable', 'email'],
                    'nok_email' => ['nullable', 'email'],
                    'nok_phone' => ['nullable', 'numeric', 'digits:11'],
                    'country_id' => ['nullable'],
                    'state_id' => ['nullable'],
                    'city_id' => ['nullable', 'required_unless:state_id:null,null'],
                    'address' => ['required', 'required_unless:city_id:null,null'],
                    'date_of_birth' => ['nullable'],
                    'business_id' => ['nullable']
                ],
                ['state_id.required' => 'Business Source is required']
            );

            $this->due = $this->getDue();

            $this->customer = Customer::create($data);
            $this->customer_id = $this->customer->id;

            $updated = $this->booking->update([
                'bookable_id' => $this->bookable_id,
                'bookable_type' => 'App\Models\Hall',
                'type' => 'hall',
                'checked_in' => $this->checked_in,
                'checked_out' => $this->checked_out,
                'amount_paid' => $this->amount_paid,
                'due' => $this->due,
                'discount' => $this->discount,
                'status' => $this->amount_paid < $this->due ? 'unpaid' : 'paid',
                'due' => $this->getDue(),
                'customer_id' => $this->customer_id,
                'agent_id' => auth()->id()
            ]);

            if ($updated) {
                $details = [
                    'text' => 'Hall updated successfully.',
                    'title' => 'Booking saved'
                ];
                return $this->dispatch('swal:success', details: sweetAlert(...$details));
            }
        } else {
            $updated = $this->booking->update([
                'bookable_id' => $this->bookable_id,
                'bookable_type' => 'App\Models\Hall',
                'type' => 'hall',
                'checked_in' => $this->checked_in,
                'checked_out' => $this->checked_out,
                'amount_paid' => $this->amount_paid,
                'due' => $this->due,
                'discount' => $this->discount,
                'status' => $this->amount_paid < $this->due ? 'unpaid' : 'paid',
                'due' => $this->getDue(),
                'customer_id' => $this->customer_id,
                'agent_id' => auth()->id()
            ]);

            if ($updated) {
                // $this->reset();
                $details = [
                    'text' => 'Hall updated successfully.',
                    'title' => 'Booking saved'
                ];
                return $this->dispatch('swal:success', details: sweetAlert(...$details));
            }
        }
    }

    function deleteBooking(Booking $booking)
    {
        $selectedUsers = $booking->delete();
        if ($selectedUsers) {
            $this->reset();
            $details = [
                'text' => 'Booking successfully deleted alongside bookings',
                'title' => 'Booking Deleted'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    function cleanHall()
    {
        $this->booking->update(['status' => 'available']);
        $this->reset(['customer', 'booking']);
        $details = [
            'text' => 'Hall marked as clean and the status changed to available, now you can re-book the hall',
            'title' => 'Hall is Available'
        ];
        return $this->dispatch('swal:success', details: sweetAlert(...$details));
    }

    function mount()
    {
        $this->hall->load(['type', 'bookings']);
        $this->bookable_id = $this->hall->id;
        if ($this->hall->bookings()->exists()) {
            $this->bookings = $this->hall->bookings()->orderByDesc('id')->get();
        } else {
            $this->updateC = false;
            $this->bookingForm = false;
            $this->booking = null;
            $this->customer = null;
            $this->customer_id = null;
            $this->cid = null;
            $this->status = null;
            $this->checked_in = null;
            $this->checked_out = null;
            $this->amount_paid = null;
            $this->due = null;
            $this->firstname = null;
            $this->lastname = null;
            $this->hotel_id = null;
            $this->email = null;
            $this->address = null;
            $this->city_id = null;
            $this->state_id = null;
            $this->gender = null;
            $this->phone = null;
            $this->country_id = null;
            $this->business_id = null;
        }
        // $this->hall->load('type');
    }

    public function render()
    {
        $term = "%" . $this->query . "%";
        if ($this->hall->bookings()->exists()) {
            // $this->bookings = $this->hall->bookings()->orderByDesc('id')->get();
            $reservations = $this->hall->bookings()->orderByDesc('id')->paginate(10);
            return view('livewire.add-hall-reservation', compact(['reservations']));
        }
        return view('livewire.add-hall-reservation');
    }

    function getDue()
    {
        $price = \App\Models\Hall::find($this->bookable_id)->getPrice();

        $days = calculateDateDifferenceInDays($this->checked_in, $this->checked_out);

        $discountPrice = ($price * $days * intval($this->discount)) / 100;

        $this->due = $price * $days - $discountPrice;

        if (auth()->user()->hotel->vat) {
            return $this->due * 1.075;
        }

        return $this->due;
    }
}
