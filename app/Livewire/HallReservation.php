<?php

namespace App\Livewire;

use App\Models\Booking;
use Livewire\Component;
use App\Models\Customer;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class HallReservation extends Component
{
    public $firstname, $customer_id, $discount, $agent_id, $due, $amount_paid, $nationality, $bookable_id, $checked_in, $checked_out, $business, $lastname, $nok_name, $nok_phone, $nok_email, $avatar, $date_of_birth, $address, $gender, $phone, $email, $hotel_id, $city_id, $state_id, $country_id = 160, $business_id;
    use WithPagination, WithFileUploads;

    public $booking;
    public $date;
    public $customer;
    public $bookable_type = "App\Models\Room";
    public $perPage = 10;
    public $query = '';
    public $is_new = false;
    public $type = 'room';

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
        $this->validate(['customer_id' => ['required', 'exists:customers,id']]);
        $this->customer = Customer::find($this->customer_id);
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


    function bookRoom()
    {
        $data = $this->validate(
            [
                'firstname' => ['required'],
                'lastname' => ['required'],
                'phone' => ['required', 'numeric', 'digits:11'],
                'email' => ['required', 'email', Rule::unique('customers')->ignore($this->customer_id)],
                'country_id' => ['nullable'],
                'state_id' => ['nullable'],
                'discount' => ['nullable', 'numeric', 'max:100'],
                'city_id' => ['nullable', 'required_unless:state_id:null,null'],
                'address' => ['required', 'required_unless:city_id:null,null'],
                'date_of_birth' => ['nullable'],
                'business_id' => ['nullable'],
                'bookable_id' => ['required'],
                'checked_in' => ['required', 'after:yesterday'],
                'checked_out' => ['required', 'after:checked_in'],
                'amount_paid' => ['required', 'numeric']
            ],
            ['bookable_id.required' => 'No room selected kindly select a room']
        );

        if ($this->customer_id) {
            $created = Booking::create([
                'bookable_id' => $data['bookable_id'],
                'bookable_type' => 'App\Models\Room',
                'type' => 'room',
                'checked_in' => $data['checked_in'],
                'checked_out' => $data['checked_out'],
                'amount_paid' => $data['amount_paid'],
                'discount' => $data['discount'],
                'status' => 'booked',
                'customer_id' => $this->customer_id,
                'agent_id' => auth()->id()
            ]);

            // $find customer
            $this->dispatch('setCustomerSelect', []);
            if ($created) {
                $normal = $created->bookable->type->price1 * calculateDateDifferenceInDays($created->checked_in, $created->checked_out);
                $discounted = $normal - ($normal * $created->discount / 100);
                $created->update(['due' => $created->discount > 0 ? $discounted : $normal]);
                $this->reset();
                $details = [
                    'text' => 'Room booked successfully.',
                    'title' => 'Booking Added'
                ];
                return $this->dispatch('swal:success', details: sweetAlert(...$details));
            }
        } else {
            $customer = Customer::create($data);
            $created =   Booking::create([
                'bookable_id' => $data['bookable_id'],
                'bookable_type' => 'App\Models\Room',
                'type' => 'room',
                'discount' => $data['discount'],
                'checked_in' => $data['checked_in'],
                'checked_out' => $data['checked_out'],
                'amount_paid' => $data['amount_paid'],
                'status' => 'occupied',
                'agent_id' => auth()->id(),
                'customer_id' => $customer->id
            ]);
            // $find customer
            $this->dispatch('setCustomerSelect', []);

            $normal = $created->bookable->type->price1 * calculateDateDifferenceInDays($created->checked_in, $created->checked_out);
            $discounted = $normal - ($normal * $created->discount / 100);
            $created->update(['due' => $created->discount > 0 ? $discounted : $normal]);

            if ($created) {
                $this->reset();
                $details = [
                    'text' => 'Room booked successfully.',
                    'title' => 'Booking Added'
                ];
                return $this->dispatch('swal:success', details: sweetAlert(...$details));
            }
        }
    }

    function editBooking(Booking $booking)
    {
        $this->updateC = true;
        $this->bookingForm = true;
        $this->business = $booking;
        $this->cid = $booking->id;
        $this->firstname = $booking->firstname;
        $this->lastname = $booking->lastname;
        $this->hotel_id = $booking->hotel_id;
        $this->email = $booking->email;
        $this->avatar = $booking->avatar;
        $this->address = $booking->address;
        $this->city_id = $booking->city_id;
        $this->state_id = $booking->state_id;
        $this->gender = $booking->gender;
        $this->phone = $booking->phone;
        $this->country_id = $booking->country_id;
        $this->business_id = $booking->business_id;
        $this->date_of_birth = $booking->date_of_birth;
    }

    function updateBooking()
    {
        $this->updateC = true;
        $type = Booking::find($this->cid);
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

        $updated = $type->update($data);

        if ($updated) {
            $this->reset();
            $details = [
                'text' => 'Booking updated successfully.',
                'title' => 'Booking updated'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
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

    function deleteMultipleBooking()
    {
        $selectedUsers = Booking::whereKey($this->checked)->delete();
        if ($selectedUsers) {
            $this->reset();
            $details = [
                'text' => 'Business Type removed successfully',
                'title' => 'Multiple Room Type Deleted'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    function changeDate()
    {
        $data = $this->validate(['date' => 'required']);
    }

    function mount()
    {
        if (session('business_id')) {
            $this->business_id = session('business_id');
        }

        $this->date  = now()?->format('Y-m-d');
    }

    public function render()
    {
        $term = "%" . $this->query . "%";
        $reservations = Booking::with(['bookable'])
            ->where('type', 'LIKE', trim($term))
            ->orWhereHas(
                'customer',
                fn(Builder $query)
                => $query->where('firstname', 'LIKE', trim($term))
                    ->orWhere('phone', 'LIKE', trim($term))
            )
            ->orWhereHas('bookable', fn(Builder $query) => $query->where('number', 'LIKE', trim($term)))
            ->paginate($this->perPage);
        return view('livewire.hall-reservation', compact(['reservations']));
    }
}
