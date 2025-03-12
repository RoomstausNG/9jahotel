<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Business;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class BusinessAccountPage extends Component
{
    public $name, $balance, $address, $status, $limit, $phone, $email, $hotel_id, $city_id, $state_id, $country_id = 160;
    use WithPagination, WithFileUploads;

    public $business;
    public $perPage = 10;
    public $query = '';

    public $typeForm = false;
    public $businessForm = false;

    public $updateB = false;

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

    function confirmDelete(Business $business)
    {
        $this->business = $business;
        $this->confirm = true;
    }

    function AddBusiness()
    {
        $data = $this->validate(
            [
                'name' => ['required'],
                'balance' => ['nullable', 'numeric'],
                'limit' => ['required', 'numeric'],
                'phone' => ['required', 'numeric', 'digits:11'],
                'email' => ['required', 'email', 'unique:businesses,email'],
                'country_id' => ['nullable'],
                'state_id' => ['nullable'],
                'city_id' => ['nullable', 'required_unless:state_id:null,null'],
                'address' => ['nullable'],
            ],
            ['state_id.required' => 'Business Source is required']
        );

        $created = Business::create($data);

        if ($created) {
            $this->reset();
            $details = [
                'text' => 'Business created successfully, you can now add credit customers to business',
                'title' => 'Business Added'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    function editBusiness(Business $business)
    {
        $this->updateB = true;
        $this->businessForm = true;
        $this->business = $business;
        $this->cid = $business->id;
        $this->name = $business->name;
        $this->balance = $business->balance;
        $this->hotel_id = $business->hotel_id;
        $this->email = $business->email;
        $this->status = $business->status;
        $this->address = $business->address;
        $this->city_id = $business->city_id;
        $this->phone = $business->phone;
        $this->limit = $business->limit;
    }

    function updateBusiness()
    {
        $this->updateB = true;
        $type = Business::find($this->cid);
        $data = $this->validate(
            [
                'name' => ['required'],
                'balance' => ['nullable', 'numeric'],
                'limit' => ['required', 'numeric'],
                'phone' => ['required', 'numeric', 'digits:11'],
                'email' => ['required', 'email', Rule::unique('businesses')->ignore($this->cid)],
                'country_id' => ['nullable'],
                'state_id' => ['nullable'],
                'city_id' => ['nullable', 'required_unless:state_id:null,Null'],
                'address' => ['nullable'],
            ],
            ['state_id.required' => 'Business Source is required']
        );

        $updated = $type->update($data);

        if ($updated) {
            $this->reset();
            $details = [
                'text' => 'Business updated successfully.',
                'title' => 'Business updated'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    function addCustomer($id)
    {
        return redirect()->route('configuration.customers')->with('business_id', $id);
    }

    function deleteBusiness(Business $business)
    {
        $selectedUsers = $business->delete();
        if ($selectedUsers) {
            $this->reset();
            $details = [
                'text' => 'Business removed successfully',
                'title' => 'Business Deleted'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    function deleteMultipleType()
    {
        $selectedUsers = Business::whereKey($this->checked)->delete();
        if ($selectedUsers) {
            $this->reset();
            $details = [
                'text' => 'Business Type removed successfully',
                'title' => 'Multiple Room Type Deleted'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    public function render()
    {
        $term = "%" . $this->query . "%";
        $businesses = Business::with(['customers'])
            ->where('name', 'LIKE', trim($term))
            ->orWhere('email', 'LIKE', trim($term))
            ->orWhere('phone', 'LIKE', trim($term))
            ->orWhereHas('customers', fn (Builder $query) => $query->where('firstname', 'LIKE', trim($term)))
            ->paginate($this->perPage);
        return view('livewire.business-account-page', compact(['businesses']));
    }
}
