<?php

namespace App\Livewire\Users;

use App\Mail\RegistrationEmail;
use App\Models\User;
use Livewire\Component;
use App\Models\Customer;
use Illuminate\Support\Facades\Mail;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class CreateUser extends Component
{
    use WithFileUploads;
    public $username, $gross_annual_salary = 0, $signature, $payee = 0, $current_role, $password, $password_confirmation, $special_bonus = 0, $surcharges = 0, $health_insurance = 0, $transport_insurance = 0, $housing_insurance = 0, $overtime_insurance = 0, $special_duty_insurance = 0, $other_deductions = 0, $is_new, $bank_name, $account_number, $account_name, $firstname, $lastname, $nok_name, $nok_phone, $nok_email, $phone, $email, $date_of_birth, $date_of_employment, $city_id, $state_id, $hotel_id, $country_id, $can_login, $avatar, $gender, $address, $department_id, $designation;

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

    function confirmDelete(Customer $customer)
    {
        $this->customer = $customer;
        $this->confirm = true;
    }

    function addUser()
    {
        $data = $this->validate(
            [
                'firstname' => ['required'],
                'lastname' => ['required'],
                'phone' => ['required', 'numeric', 'digits:11', 'unique:users,phone'],
                'gender' => ['required'],
                // 'nok_name' => ['nullable', 'email'],
                // 'nok_email' => ['nullable', 'email'],
                // 'nok_phone' => ['nullable', 'numeric', 'digits:11'],
                'email' => ['required', 'email', 'unique:users,email'],
                'current_role' => ['required'],
                'country_id' => ['nullable'],
                'state_id' => ['nullable', 'required_unless:country_id:null,null'],
                'city_id' => ['nullable', 'required_unless:state_id:null,null'],
                'address' => ['required', 'required_unless:city_id:null,null'],
                'designation' => ['required'],
                'account_number' => ['nullable', 'numeric'],
                'account_name' => ['nullable'],
                'bank_name' => ['nullable'],
                'payee' => ['nullable', 'numeric'],
                'surcharges' => ['nullable', 'numeric'],
                'health_insurance' => ['nullable', 'numeric'],
                'transport_insurance' => ['nullable', 'numeric'],
                'housing_insurance' => ['nullable', 'numeric'],
                'gross_annual_salary' => ['nullable', 'numeric'],
                'overtime_insurance' => ['nullable', 'numeric'],
                'special_duty_insurance' => ['nullable', 'numeric'],
                'special_bonus' => ['nullable', 'numeric'],
                'other_deductions' => ['nullable', 'numeric'],
                'date_of_employment' => ['required', 'before:tomorrow'],
                'signature' => ['nullable', 'mimes:png,jpg,jpeg,pdf'],
                'password' => ['required', 'confirmed']
            ],
            ['state_id.required' => 'Business Source is required']
        );

        $data['hotel_id'] = auth()->user()->hotel_id;
        $data['password'] = bcrypt($data['password']);

        if ($this->signature) {
            $url = $this->signature->store('signature', 'public');
            $data['signature'] = $url;
        }

        $created = User::create($data);

        if ($created) {

            Mail::to($created)->later(now()->addSeconds(2), new RegistrationEmail($created));

            $this->reset();
            $details = [
                'text' => 'User created successfully',
                'title' => 'Staff Added'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }
    public function render()
    {
        return view('livewire.users.create-user');
    }
}