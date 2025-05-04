<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class UserProfile extends Component
{
    use WithFileUploads;

    public $firstname, $lastname, $gender, $dob, $address, $city_id, $id_type, $id_number, $avatar, $phone, $email;
    public $password = '';
    public $password_confirmation = '';
    public $old_password = '';
    public $user;

    public function mount()
    {
        $customer = auth('customers')->user();

        if (!$customer) {
            abort(403, 'No authenticated customer.');
        }

        Log::info('UserProfile mount customer', ['customer' => $customer->id]);
        $this->user = $customer;
        $this->firstname = $customer->firstname;
        $this->lastname = $customer->lastname;
        $this->gender = $customer->gender;
        $this->dob = $customer->dob;
        $this->address = $customer->address;
        $this->city_id = $customer->city_id;
        $this->id_type = $customer->id_type;
        $this->id_number = $customer->id_number;
        $this->avatar = $customer->avatar;
        $this->phone = $customer->phone;
        $this->email = $customer->email;
    }

    public function updateProfile()
    {
        $customer = auth('customers')->user();
        $data = $this->validate([
            'firstname' => ['required'],
            'lastname' => ['required'],
            'gender' => ['required'],
            'dob' => ['nullable', 'date'],
            'address' => ['required'],
            'city_id' => ['nullable'],
            'id_type' => ['required'],
            'id_number' => ['required'],
            'avatar' => ['nullable'],
            'phone' => ['required'],
            'email' => ['required', 'email'],
        ]);
        if ($this->avatar && $this->avatar !== $customer->avatar) {
            $data['avatar'] = $this->avatar->store('avatars', 'public');
        }
        $customer->update($data);
        session()->flash('success', 'Profile updated successfully!');
    }

    public function updatePassword()
    {
        $this->validate([
            'old_password' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $customer = auth('customers')->user();
        if (!\Illuminate\Support\Facades\Hash::check($this->old_password, $customer->password)) {
            $this->addError('old_password', 'The old password is incorrect.');
            return;
        }
        $customer->password = \Illuminate\Support\Facades\Hash::make($this->password);
        $customer->save();
        $this->password = '';
        $this->password_confirmation = '';
        $this->old_password = '';
        session()->flash('success', 'Password updated successfully!');
    }

    public function updateAvatar()
    {
        $customer = auth('customers')->user();
        $this->validate([
            'avatar' => ['required', 'image', 'max:2048'],
        ]);
        if ($this->avatar) {
            // Upload to Cloudinary
            $uploaded = \CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary::upload($this->avatar->getRealPath(), [
                'folder' => 'avatars',
                'transformation' => [
                    ['width' => 300, 'height' => 300, 'crop' => 'fill', 'gravity' => 'face']
                ]
            ]);
            $url = $uploaded->getSecurePath();
            $customer->avatar = $url;
            $customer->save();
            $this->avatar = $url;
            session()->flash('success', 'Avatar updated successfully!');
        }
    }

    public function render()
    {
        return view('livewire.user-profile');
    }
}