<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Forms\Form;
use Livewire\Attributes\Layout;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use App\Models\Customer;

class SignupPage extends Component implements HasForms
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
          ->columnSpan('full'),
        TextInput::make('lastname')
          ->label('Last Name')
          ->required()
          ->columnSpan('full'),
        TextInput::make('email')
          ->label('Email')
          ->email()
          ->required()
          ->unique(Customer::class, 'email')
          ->columnSpan('full'),
        TextInput::make('phone')
          ->label('Phone')
          ->required()
          ->columnSpan('full'),
        TextInput::make('password')
          ->label('Password')
          ->password()
          ->revealable()
          ->required()
          ->confirmed()
          ->columnSpan('full'),
        TextInput::make('password_confirmation')
          ->label('Confirm Password')
          ->password()
          ->revealable()
          ->required()
          ->columnSpan('full'),
        Checkbox::make('terms')
          ->label('I agree to the terms and conditions')
          ->required()
          ->columnSpan('full'),
      ])
      ->columns(1)
      ->statePath('data');
  }

  public function signup()
  {
    $validated = $this->validate([
      'data.firstname' => ['required', 'string', 'max:255'],
      'data.lastname' => ['required', 'string', 'max:255'],
      'data.email' => ['required', 'email', 'unique:customers,email'],
      'data.phone' => ['required', 'string', 'max:20'],
      'data.password' => ['required', 'confirmed', 'min:6'],
      'data.terms' => ['accepted'],
    ]);

    $customer = Customer::create([
      'firstname' => $this->data['firstname'],
      'lastname' => $this->data['lastname'],
      'email' => $this->data['email'],
      'phone' => $this->data['phone'],
      'password' => Hash::make($this->data['password']),
    ]);

    Notification::make()
      ->title('Signup Successful')
      ->body('Your account has been created. Please log in.')
      ->success()
      ->send();

    return redirect()->route('login');
  }

  public function mount(): void
  {
    $this->form->fill();
  }

  #[Layout('components.layouts.auth')]
  public function render()
  {
    return view('livewire.signup-page');
  }
}
