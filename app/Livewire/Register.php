<?php

namespace App\Livewire;

use App\Models\Customer;
use Livewire\Component;
use Filament\Forms\Form;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;

class RegisterPage extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('firstname')
                    ->label('First Name')
                    ->required()
                    ->prefixIcon('heroicon-o-user')
                    ->columnSpan('full'),

                TextInput::make('lastname')
                    ->label('Last Name')
                    ->required()
                    ->prefixIcon('heroicon-o-user')
                    ->columnSpan('full'),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->prefixIcon('heroicon-o-envelope-open')
                    ->unique(Customer::class)
                    ->autocomplete('email')
                    ->columnSpan('full'),

                TextInput::make('phone')
                    ->label('Phone Number')
                    ->tel()
                    ->required()
                    ->prefixIcon('heroicon-o-phone')
                    ->columnSpan('full'),

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->required()
                    ->minLength(8)
                    ->autocomplete('new-password')
                    ->columnSpan('full'),

                TextInput::make('password_confirmation')
                    ->label('Confirm Password')
                    ->password()
                    ->revealable()
                    ->required()
                    ->same('password')
                    ->autocomplete('new-password')
                    ->columnSpan('full'),
            ])
            ->columns(1)
            ->statePath('data');
    }

    public function register()
    {
        $this->validate([
            'data.firstname' => ['required', 'string', 'max:255'],
            'data.lastname' => ['required', 'string', 'max:255'],
            'data.email' => ['required', 'email', 'unique:customers,email'],
            'data.phone' => ['required', 'string'],
            'data.password' => ['required', 'min:8', 'same:data.password_confirmation'],
        ]);

        $customer = Customer::create([
            'firstname' => $this->data['firstname'],
            'lastname' => $this->data['lastname'],
            'email' => $this->data['email'],
            'phone' => $this->data['phone'],
            'password' => Hash::make($this->data['password']),
        ]);

        // Log the user in after registration
        Auth::guard('customers')->login($customer);

        session()->regenerate();

        $details = [
            'text' => "Welcome {$customer->firstname}!",
            'title' => 'Registration Successful',
            'icon' => 'success',
            'timer' => 3000
        ];

        $this->dispatch('swal:success', details: sweetAlert(...$details));

        return redirect()->intended(route('dashboard'));
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    #[Layout("components.layouts.auth")]
    public function render()
    {
        return view('livewire.register');
    }
}