<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Forms\Form;
use Illuminate\Support\HtmlString;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;

class Subscribe extends Component  implements HasForms
{
    public ?array $data = [];

    use InteractsWithForms;
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('email')
                    ->label('Email Address')
                    ->default(null)
                    ->placeholder("Your email address here")
                    ->email()
                    ->required()
                    ->columnSpanFull()
                // ->submitAction(new HtmlString('<button type="submit"
                //     class="px-6 py-2 mx-auto my-3 font-medium text-white rounded bg-primary hover:bg-secondary hover:text-black max-w-max">Submit</button>'))
                // ->columnSpanFull(),

            ])->columns(2)
            ->statePath('data');
    }

    public function subscribe()
    {
        $data = $this->form->getState();
        $this->form->fill();

        Notification::make()
            ->title('Saved successfully ' . $data['email'])
            ->body('You have successully subscribe to our newsletter')
            ->success()
            ->duration(7000)
            ->send();
    }


    public function render()
    {
        return view('livewire.subscribe');
    }
}