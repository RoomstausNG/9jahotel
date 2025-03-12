<?php

namespace App\Livewire\Configuration;

use App\Models\Customer;
use App\Rules\UniqueCustomerEmailRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class CustomerPage extends Component
{
    public $firstname, $business, $id_type, $id_number, $lastname, $nok_name, $nok_phone, $nok_email, $avatar, $date_of_birth, $address, $gender, $phone, $email, $hotel_id, $city_id, $state_id, $country_id = 160, $business_id;
    use WithPagination, WithFileUploads;

    public $customer;
    public $perPage = 10;
    public $query = '';

    public $typeForm = false;
    public $customerForm = false;

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

    public function showForm()
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

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function confirmDelete(Customer $customer)
    {
        $this->business = $customer;
        $this->confirm = true;
    }

    public function AddCustomer()
    {
        $data = $this->validate(
            [
                'firstname' => ['required'],
                'lastname' => ['required'],
                'phone' => ['required', 'numeric', 'digits:11'],
                'nok_name' => ['nullable', 'email'],
                'gender' => ['required'],
                'nok_email' => ['nullable', 'email'],
                'nok_phone' => ['nullable', 'numeric', 'digits:11'],
                'email' => ['required', 'email', new UniqueCustomerEmailRule()],
                'country_id' => ['nullable'],
                'id_number' => ['required'],
                'id_type' => ['required'],
                'state_id' => ['nullable'],
                'city_id' => ['nullable', 'required_unless:state_id:null,null'],
                'address' => ['required', 'required_unless:city_id:null,null'],
                'date_of_birth' => ['nullable'],
                'business_id' => ['nullable'],
            ],
            ['state_id.required' => 'Business Source is required']
        );

        $cus = Customer::where('email', $data['email'])->first();

        //only associated the customer to the hotel and update his info if already exist
        if ($cus) {
            $created = $cus;
            $cus->update(array_merge($data, ['created_at' => now()]));
        } else {
            $created = Customer::create($data);
        }

        if ($created) {
            // attach customer to hotel
            $created->hotels()->attach(auth()->user()->hotel_id);

            $this->reset();
            $details = [
                'text' => 'Customer created successfully, you can now be booked',
                'title' => 'Customer Added',
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    public function editCustomer(Customer $customer)
    {
        $this->updateC = true;
        $this->customerForm = true;
        $this->business = $customer;
        $this->cid = $customer->id;
        $this->firstname = $customer->firstname;
        $this->lastname = $customer->lastname;
        $this->hotel_id = $customer->hotel_id;
        $this->email = $customer->email;
        $this->avatar = $customer->avatar;
        $this->address = $customer->address;
        $this->id_type = $customer->id_type;
        $this->id_number = $customer->id_number;
        $this->city_id = $customer->city_id;
        $this->state_id = $customer->state_id;
        $this->gender = $customer->gender;
        $this->phone = $customer->phone;
        $this->country_id = $customer->country_id;
        $this->business_id = $customer->business_id;
        $this->date_of_birth = $customer->date_of_birth;
    }

    public function updateCustomer()
    {
        $this->updateC = true;
        $type = Customer::find($this->cid);
        $data = $this->validate(
            [
                'firstname' => ['required'],
                'lastname' => ['required'],
                'gender' => ['required'],
                'phone' => ['required', 'numeric', 'digits:11'],
                'email' => ['required', 'email', Rule::unique('customers')->ignore($this->cid)],
                'nok_name' => ['nullable', 'email'],
                'nok_email' => ['nullable', 'email'],
                'nok_phone' => ['nullable', 'numeric', 'digits:11'],
                'country_id' => ['nullable'],
                'id_number' => ['required'],
                'id_type' => ['required'],
                'state_id' => ['nullable'],
                'city_id' => ['nullable', 'required_unless:state_id:null,null'],
                'address' => ['required', 'required_unless:city_id:null,null'],
                'date_of_birth' => ['nullable'],
                'business_id' => ['nullable'],
            ],
            ['state_id.required' => 'Business Source is required']
        );

        $updated = $type->update($data);

        if ($updated) {
            $this->reset();
            $details = [
                'text' => 'Customer updated successfully.',
                'title' => 'Customer updated',
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    public function deleteCustomer(Customer $customer)
    {
        $selectedUsers = $customer->delete();
        if ($selectedUsers) {
            $this->reset();
            $details = [
                'text' => 'Customer successfully deleted alongside bookings',
                'title' => 'Customer Deleted',
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    public function deleteMultipleCustomer()
    {
        $selectedUsers = Customer::whereKey($this->checked)->delete();
        if ($selectedUsers) {
            $this->reset();
            $details = [
                'text' => 'Business Type removed successfully',
                'title' => 'Multiple Room Type Deleted',
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    public function mount()
    {
        if (session('business_id')) {
            $this->business_id = session('business_id');
        }
    }

    public function render()
    {
        $term = "%" . $this->query . "%";
        $customers = Customer::with(['company'])
            ->myCustomer()
            ->where(function (Builder $query) use ($term) {
                $query->where('firstname', 'LIKE', $term)
                    ->orWhere('lastname', 'LIKE', $term)
                    ->orWhere('email', 'LIKE', $term)
                    ->orWhere('phone', 'LIKE', $term);
            })
            ->paginate($this->perPage);
        return view('livewire.configuration.customer-page', compact(['customers']));
    }
}
