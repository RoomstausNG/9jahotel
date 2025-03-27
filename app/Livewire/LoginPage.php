<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Forms\Form;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;

class LoginPage extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->prefixIcon('heroicon-o-envelope-open')
                    ->autocomplete('username')
                    ->columnSpan('full'),

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->required()
                    ->autocomplete('current-password')
                    ->columnSpan('full'),

                Checkbox::make('remember')
                    ->label('Remember me')
                    ->columnSpan('full'),
            ])
            ->columns(1)
            ->statePath('data');
    }

    public function login()
    {
        $this->validate([
            'data.email' => 'required|email',
            'data.password' => 'required',
        ]);

        $credentials = [
            'email' => $this->data['email'],
            'password' => $this->data['password'],
        ];

        $remember = $this->data['remember'] ?? false;

        // Specify the customers guard if you have it configured in auth.php
        if (Auth::guard('customers')->attempt($credentials, $remember)) {
            session()->regenerate();

            $customer = Auth::guard('customers')->user();

            $details = [
                'text' => "Welcome back {$customer->firstname}!",
                'title' => 'Login Successful',
                'icon' => 'success',
                'timer' => 3000
            ];

            $this->dispatch('swal:success', details: sweetAlert(...$details));

            return redirect()->intended(route('dashboard'));
        }

        Notification::make()
            ->title('Invalid credentials')
            ->body('The provided credentials do not match our records.')
            ->danger()
            ->send();

        $this->addError('data.email', 'These credentials do not match our records.');
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    #[Layout("components.layouts.auth")]
    public function render()
    {
        return view('livewire.login-page');
    }
}