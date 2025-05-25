<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use App\Mail\SupportMessageMail;
use Filament\Notifications\Notification;

class SupportForm extends Component
{
    public $name;
    public $phone;
    public $email;
    public $message;

    protected $rules = [
        'name' => 'required|string|max:255',
        'phone' => 'nullable|string|max:30',
        'email' => 'required|email',
        'message' => 'required|string',
    ];

    public function submit()
    {
        $validated = $this->validate();

        // Queue the email to ask@9jahotel.com
        Mail::to('ask@9jahotel.com')
            ->cc('bowofadeoyerinde@gmail.com')
            ->queue(new SupportMessageMail(
                $validated['name'],
                $validated['phone'],
                $validated['email'],
                $validated['message']
            ));

        // Show Filament notification
        Notification::make()
            ->title('Support request submitted')
            ->body('Your message has been sent successfully.')
            ->success()
            ->send();

        // Optionally reset the form
        $this->reset(['name', 'phone', 'email', 'message']);
    }

    public function render()
    {
        return view('livewire.support-form');
    }
}
