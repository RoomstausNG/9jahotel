<?php

namespace App\Livewire;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Attributes\Layout;
use Livewire\Component;

class LoginPage extends Component implements HasForms
{
    use InteractsWithForms;
    public $data;

    public function form(Form $form): Form
    {
        $this->data = $form->getData();
    }


    #[Layout("components.layouts.auth")]
    public function render()
    {
        return view('livewire.login-page');
    }
}