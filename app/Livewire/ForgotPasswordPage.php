<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Forms\Form;
use Livewire\Attributes\Layout;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use App\Models\Customer;

class ForgotPasswordPage extends Component implements HasForms
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
                    ->columnSpan('full'),
            ])
            ->columns(1)
            ->statePath('data');
    }

    public function sendResetLink()
    {
        $this->validate([
            'data.email' => ['required', 'email', 'exists:customers,email'],
        ]);

        // Here you would trigger the password reset notification logic
        Notification::make()
            ->title('Reset Link Sent')
            ->body('If your email exists in our system, a password reset link has been sent.')
            ->success()
            ->send();
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    #[Layout('components.layouts.auth')]
    public function render()
    {
        return view('livewire.forgot-password-page');
    }
}
