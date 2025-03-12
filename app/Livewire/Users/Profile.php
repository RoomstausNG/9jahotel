<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Component;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Contracts\Role;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role as RoleModel;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class Profile extends Component
{

    use WithFileUploads;

    public User $user;

    public  $signature, $password, $payee, $gross_annual_salary, $current_role, $password_confirmation, $special_bonus, $surcharges, $health_insurance, $transport_insurance, $housing_insurance, $overtime_insurance, $special_duty_insurance, $other_deductions, $bank_name, $account_number, $account_name, $firstname, $date_of_employment, $lastname, $department_id, $username, $current_password, $phone, $email, $date_of_birth, $avatar, $can_login,  $designation,  $hotel_id, $gender, $address, $city_id, $country_id, $state_id, $is_new, $cid;

    function mount()
    {
        $this->cid = $this->user->id;
        $this->firstname = $this->user->firstname;
        $this->hotel_id = $this->user->hotel_id;
        $this->state_id = $this->user->state_id;
        $this->country_id = $this->user->country_id;
        $this->lastname = $this->user->lastname;
        $this->city_id = $this->user->city_id;
        $this->username = $this->user->username;
        $this->email = $this->user->email;
        $this->phone = $this->user->phone;
        $this->date_of_birth = $this->user->date_of_birth;
        $this->gender = $this->user->gender;
        $this->avatar = $this->user->avatar;
        $this->can_login = $this->user->can_login;
        $this->current_role = $this->user->current_role;
        $this->department_id = $this->user->department_id;
        $this->designation = $this->user->designation;
        $this->is_new = $this->user->is_new;
        $this->address = $this->user->address;
        $this->bank_name = $this->user->bank_name;
        $this->account_name = $this->user->account_name;
        $this->account_number = $this->user->account_number;
        $this->date_of_employment = $this->user->date_of_employment;
        $this->payee = $this->user->payee;
        $this->special_bonus = $this->user->special_bonus;
        $this->surcharges = $this->user->surcharges;
        $this->other_deductions = $this->user->other_deductions;
        $this->gross_annual_salary = $this->user->gross_annual_salary;
        $this->health_insurance = $this->user->health_insurance;
        $this->housing_insurance = $this->user->housing_insurance;
        $this->special_duty_insurance = $this->user->special_duty_insurance;
        $this->overtime_insurance = $this->user->overtime_insurance;
        $this->transport_insurance = $this->user->transport_insurance;
        $this->current_role = $this->user->current_role;
    }

    function updateProfile()
    {
        $data = $this->validate([
            'firstname' => ['required'],
            'lastname' => ['required'],
            'phone' => ['required', 'numeric', 'digits:11', Rule::unique('users')->ignore($this->cid)],
            'gender' => ['required'],
            // 'nok_name' => ['nullable', 'email'],
            // 'nok_email' => ['nullable', 'email'],
            // 'nok_phone' => ['nullable', 'numeric', 'digits:11'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($this->cid)],
            'current_role' => ['required'],
            'country_id' => ['nullable'],
            'state_id' => ['nullable', 'required_unless:country_id:null,null'],
            'city_id' => ['nullable', 'required_unless:state_id:null,null'],
            'address' => ['required', 'required_unless:city_id:null,null'],
            'designation' => ['nullable'],
            'account_number' => ['nullable', 'numeric'],
            'account_name' => ['nullable'],
            'can_login' => ['nullable'],
            'bank_name' => ['nullable'],
            'payee' => ['nullable', 'numeric'],
            'surcharges' => ['nullable', 'numeric'],
            'health_insurance' => ['nullable', 'numeric'],
            'gross_annual_salary' => ['nullable', 'numeric'],
            'transport_insurance' => ['nullable', 'numeric'],
            'housing_insurance' => ['nullable', 'numeric'],
            'overtime_insurance' => ['nullable', 'numeric'],
            'special_duty_insurance' => ['nullable', 'numeric'],
            'special_bonus' => ['nullable', 'numeric'],
            'other_deductions' => ['nullable', 'numeric'],
            'department_id' => 'required',
            'date_of_employment' => ['required', 'before:tomorrow'],
            'signature' => ['nullable', 'mimes:png,jpg,jpeg,pdf'],
        ]);

        if ($this->signature) {
            $url = $this->signature->store('signature', 'public');
            $data['signature'] = $url;
        }


        $submitted = $this->user->update($data);

        $role = RoleModel::where('name', $this->user->current_role)->first();

        // update the roles
        $this->user->roles()->sync([$role->id]);

        if ($this->user->hasRole($data['current_role'])) {
            $this->user->assignRole($data['current_role']);
        }

        if ($submitted) {
            $details = [
                'text' => 'User profile updated successfully',
                'title' => 'Profile updated'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    function updatePassword()
    {
        $data = $this->validate(
            [
                'password' => ['required', 'confirmed', Password::min(8)],
            ]
        );

        $saved = $this->user->update([
            'password' => Hash::make($data['password'])
        ]);

        if ($saved) {
            $this->reset([
                'password',
                'current_password'
            ]);
            $details = [
                'text' => 'User password has been successfully changed~',
                'title' => 'Password Updated'
            ];
            $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }


    public function render()
    {
        return view('livewire.users.profile');
    }
}
