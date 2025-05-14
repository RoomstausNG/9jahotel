<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\Hall;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class HallHistory extends Component
{
    public $firstname, $status, $type, $bookable_type, $customer_id, $discount, $agent_id, $due, $amount_paid, $nationality, $bookable_id, $checked_in, $checked_out, $business, $lastname, $nok_name, $nok_phone, $nok_email, $avatar, $date_of_birth, $address, $gender, $phone, $email, $hotel_id, $city_id, $state_id, $country_id = 160, $business_id;

    use WithPagination, WithFileUploads;

    public Hall $hall;

    public $booking;
    public $perPage = 10;
    public $query = '';

    public $typeForm = false;
    public $hallForm = false;

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


    function addBooking()
    {
        $data = $this->validate(
            [
                'customer_id' => ['required'],
                'checked_in' => ['required', 'after:yesterday'],
                'checked_out' => ['required', 'after:checked_in'],
                'amount_paid' => ['required', 'numeric'],
                'discount' => ['nullable', 'numeric', 'max:100']
            ],
            ['bookable_id.required' => 'No Hall selected kindly select a Hall']
        );
        $data['agent_id'] = auth()->id();
        $data['type'] = 'Hall';
        $data['status'] = 'occupied';

        // dd([$data, $this->Hall]);

        $created = $this->hall->bookings()->create($data);

        $normal = $created->bookable->price * calculateDateDifferenceInDays($created->checked_in, $created->checked_out);
        $discounted = $normal - ($normal * $created->discount / 100);
        // dd([$normal, $discounted]);
        $created->update(['due' => $created->discount > 0 ? $discounted : $normal]);

        // $find customer
        $this->dispatch('setCustomerSelect', []);
        if ($created) {
            // $this->reset();
            $details = [
                'text' => 'Hall booked successfully.',
                'title' => 'Booking Added'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    function editBooking(Booking $booking)
    {
        $this->updateC = true;
        $this->hallForm = true;
        $this->booking = $booking;
        $this->cid = $booking->id;
        $this->bookable_id = $booking->bookable_id;
        $this->bookable_type = $booking->bookable_type;
        $this->hotel_id = $booking->hotel_id;
        $this->customer_id = $booking->customer_id;
        $this->due = $booking->due;
        $this->discount = $booking->discount;
        $this->type = $booking->type;
        $this->amount_paid = $booking->amount_paid;
        $this->status = $booking->status;
        $this->country_id = $booking->country_id;
        // $this->checked_in = $booking->checked_in?->format('d-m-Y');
        // $this->checked_out = $booking->checked_out?->format('d-m-Y');
        $this->checked_in = $booking->checked_in;
        $this->checked_out = $booking->checked_out;
    }

    function updateBooking()
    {
        $this->updateC = true;
        $booking = Booking::find($this->cid);
        $data = $this->validate(
            [
                'customer_id' => ['required'],
                'checked_in' => ['required', 'after:yesterday'],
                'checked_out' => ['required', 'after:checked_in'],
                'amount_paid' => ['required', 'numeric'],
                'discount' => ['nullable', 'numeric', 'max:100'],
                'due' => ['required']
            ],
            ['bookable_id.required' => 'No Hall selected kindly select a Hall']
        );
        $data['agent_id'] = auth()->id();
        $data['type'] = 'Hall';
        $data['status'] = 'occupied';

        // $normalDue = $booking->bookable->type->price1 * calculateDateDifferenceInDays($data['checked_in'], $data['checked_out']);
        // $normalDue = $booking->bookable->type->price1 * calculateDateDifferenceInDays($data['checked_in'], $data['checked_out']);

        // if ($data['discount'] > 0) {
        //     $data['due'] = $normalDue;
        // } else {
        //     $data['due'] = $normalDue - ($normalDue * $data['discount'] / 100);
        // }

        $updated = $booking->update($data);

        $booking->bookable()->update(['status' => $booking->status]);

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
            $details = [
                'text' => 'Booking successfully deleted',
                'title' => 'Booking Deleted'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    function deleteMultipleCustomer()
    {
        $selectedUsers = Booking::whereKey($this->checked)->delete();
        if ($selectedUsers) {
            $details = [
                'text' => 'Business Type removed successfully',
                'title' => 'Multiple Hall Type Deleted'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    function mount(Hall $hall)
    {
        $this->hall = $hall;
        $this->bookable_id = $this->hall->id;
    }

    public function render()
    {
        $term = "%" . $this->query . "%";
        $histories = $this->hall->bookings()
            ->whereHas('customer', fn(Builder $query) => $query->where('firstname', 'LIKE', trim($term)))
            ->paginate($this->perPage);
        return view('livewire.hall-history', compact(['histories']));
    }
}
