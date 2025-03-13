<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CityCopy;
use Filament\Forms\Form;
use Filament\Forms\Components;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Session;
use Filament\Forms\Concerns\InteractsWithForms;

class SearchHall extends Component implements HasForms
{
    use InteractsWithForms;
    public $tools;

    public $city;
    public $checkin;
    public $checkout;
    public $capacity;


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Select::make('city')
                    ->label('Where City?')
                    ->options(CityCopy::where('country_id', 160)->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->default(Session::get('city'))
                    ->native(false),
                Components\TextInput::make('capacity')
                    ->label('Required Capacity')
                    ->integer()
                    ->default(Session::get('capacity'))
                    ->required(),
                Components\DatePicker::make('checkin')
                    ->label('Check-In')
                    ->default(Session::get('checkin'))
                    ->required(),
                Components\DatePicker::make('checkout')
                    ->label('Check-Out')
                    ->default(Session::get('checkout'))
                    ->required(),

                // ->submitAction(new HtmlString('<button type="submit"
                //     class="px-6 py-2 mx-auto my-3 font-medium text-white rounded bg-primary hover:bg-secondary hover:text-black max-w-max">Submit</button>'))
                // ->columnSpanFull(),

            ])->columns(4);
        // ->statePath('data');
    }

    public function searchHotel()
    {
        // delete all sessions
        // session()->forget('city');
        // session()->forget('checkin');
        // session()->forget('checkout');


        $data = $this->form->getState();

        // Store data in session
        Session::put('city', $data['city']);
        Session::put('checkin', $data['checkin']);
        Session::put('checkout', $data['checkout']);

        return redirect()->route('hotels.result', $data['city']);
    }

    public function render()
    {
        return view('livewire.search-hall');
    }
}