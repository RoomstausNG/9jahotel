<?php

namespace App\Filament\Hotel\Widgets;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Widgets\Widget;

class RoomStatusWidget extends Widget implements HasForms
{
    use InteractsWithForms;
    public ?array $data = [];
    public $today, $date;
    protected static string $view = 'filament.hotel.widgets.room-status-widget';
    protected ?string $description = 'An overview of Room Status.';
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;

    function mount()
    {
        $this->form->fill();
        $this->data['date'] = today()->format('Y-m-d');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            DatePicker::make('date')
                ->label('Change date')
                ->default($this->date)
                ->minDate(now()->toDateString())
                ->extraAttributes(['class' => 'font-medium cursor-pointer text-gray-300'])
                ->live(),
        ])->statePath('data');
    }

    protected function getViewData(): array
    {
        $date = today()->format('Y-m-d');
        $user = auth()->user(); // get the authenticated user
        return compact(['user', 'date']);
    }
}