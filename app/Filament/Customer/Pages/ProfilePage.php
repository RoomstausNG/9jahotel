<?php

namespace App\Filament\Customer\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Notifications\Notification;

class ProfilePage extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static string $view = 'filament.customer.pages.profile-page';
    protected static ?string $title = 'My Profile';
    protected static ?string $navigationLabel = 'Profile';
    protected static ?string $slug = 'profile';

    public ?array $data = [];

    public function mount(): void
    {
        $customer = Auth::guard('customers')->user();
        $this->form->fill([
            'firstname' => $customer->firstname,
            'lastname' => $customer->lastname,
            'email' => $customer->email,
            // Add more fields as needed
        ]);
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('firstname')->required()->label('First Name'),
                    TextInput::make('lastname')->required()->label('Last Name'),
                ]),
                TextInput::make('email')->email()->required()->label('Email'),
            ])
            ->statePath('data');
    }

    public function save()
    {
        $customer = Auth::guard('customers')->user();
        $this->validate([
            'data.firstname' => 'required',
            'data.lastname' => 'required',
            'data.email' => 'required|email|unique:customers,email,' . $customer->id,
        ]);
        $customer->update($this->data);
        Notification::make()->title('Profile updated')->success()->send();
    }
}