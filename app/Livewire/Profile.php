<?php

namespace App\Livewire;

use Filament\Forms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;

class Profile extends Component implements HasForms
{
    use InteractsWithForms;

    public $firstname;
    public $lastname;
    public $gender;
    public $dob;
    public $address;
    public $phone;
    public $id_type;
    public $id_number;
    public $email;
    public $city_id;

    public function mount()
    {
        $user = auth('customers')->user();
        $this->form->fill([
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'gender' => $user->gender,
            'dob' => $user->dob,
            'address' => $user->address,
            'phone' => $user->phone,
            'id_type' => $user->id_type,
            'id_number' => $user->id_number,
            'email' => $user->email,
            'city_id' => $user->city_id,
        ]);
    }

    public function submit()
    {
        $data = $this->form->getState();
        $user = auth('customers')->user();
        $user->update($data);
        Notification::make()
            ->title('Profile updated successfully!')
            ->success()
            ->send();
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('firstname')->label('First Name')->required(),
            Forms\Components\TextInput::make('lastname')->label('Last Name')->required(),
            Forms\Components\Select::make('gender')->label('Gender')->options([
                'male' => 'Male',
                'female' => 'Female',
            ])->required(),
            Forms\Components\TextInput::make('dob')->label('Date of Birth')->type('date')->required(),
            Forms\Components\TextInput::make('address')->label('Address'),
            Forms\Components\TextInput::make('phone')->label('Phone'),
            Forms\Components\TextInput::make('id_type')->label('ID Type'),
            Forms\Components\TextInput::make('id_number')->label('ID Number'),
            Forms\Components\TextInput::make('email')->label('Email')->email()->required(),
            Forms\Components\TextInput::make('city_id')->label('City ID'),
        ];
    }

    public function render()
    {
        return view('livewire.profile');
    }
}