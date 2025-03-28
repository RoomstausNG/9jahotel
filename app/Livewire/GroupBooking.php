<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Forms\Form;
use App\Models\GroupBooking as ModelBooking;
use Livewire\Attributes\Layout;
use App\Models\GroupBookingRequest;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Concerns\InteractsWithForms;

class GroupBooking extends Component implements HasForms
{
    use InteractsWithForms;

    public $steps = [];
    public $currentStep = 'step1';

    // Add the data property to store form state
    public $data = [];

    public function mount()
    {
        $this->steps = [
            'step1' => [
                'title' => 'Step 1',
                'description' => 'Basic Information',
                'component' => 'group-booking.step1',
                'completed' => false,
            ],
            'step2' => [
                'title' => 'Step 2',
                'description' => 'Room Requirements',
                'component' => 'group-booking.step2',
                'completed' => false,
            ],
            'step3' => [
                'title' => 'Step 3',
                'description' => 'Additional Services',
                'component' => 'group-booking.step3',
                'completed' => false,
            ],
            'step4' => [
                'title' => 'Step 4',
                'description' => 'Review and Confirm',
                'component' => 'group-booking.step4',
                'completed' => false,
            ],
        ];

        // Initialize the form with default values if needed
        $this->form->fill([
            'need_hall' => false,
            'need_meal' => false,
            'allow_external_bids' => false,
            'meal_options' => [],
            'hotels' => [],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Basic Information')
                        ->schema([
                            TextInput::make('name')
                                ->required()
                                ->label('Contact Person Name')
                                ->placeholder('Enter your full name'),

                            TextInput::make('phone')
                                ->required()
                                ->tel()
                                ->label('Phone Number')
                                ->placeholder('Enter your phone number'),

                            TextInput::make('email')
                                ->email()
                                ->required()
                                ->label('Email Address')
                                ->placeholder('Enter your email address'),

                            TextInput::make('organisation')
                                ->label('Organisation Name')
                                ->placeholder('Enter your organisation name (if applicable)'),

                            TextInput::make('expected_guest')
                                ->numeric()
                                ->required()
                                ->label('Expected Number of Guests')
                                ->placeholder('Enter the total number of expected guests'),
                        ])->columns(2),

                    Step::make('Room Requirements')
                        ->schema([
                            TextInput::make('room_needed')
                                ->numeric()
                                ->label('Number of Rooms Needed')
                                ->placeholder('Enter the number of rooms required'),

                            Select::make('room_type')
                                ->options([
                                    'single' => 'Single Room',
                                    'double' => 'Double Room',
                                    'twin' => 'Twin Room',
                                    'suite' => 'Suite',
                                    'mixed' => 'Mixed Types',
                                ])
                                ->label('Room Type'),

                            DatePicker::make('room_from')
                                ->label('Check-in Date')
                                ->required(),

                            DatePicker::make('room_to')
                                ->label('Check-out Date')
                                ->required(),

                            TextInput::make('room_budget')
                                ->numeric()
                                ->label('Budget per Room (per night)')
                                ->prefix('₦')
                                ->placeholder('Enter your budget per room'),
                        ])->columns(2),

                    Step::make('Additional Services')
                        ->schema([
                            Toggle::make('need_hall')
                                ->label('Do you need a conference/event hall?')
                                ->reactive(),

                            TextInput::make('hall_capacity')
                                ->numeric()
                                ->label('Required Capacity')
                                ->placeholder('Enter the required capacity')
                                ->visible(fn(callable $get) => $get('need_hall')),

                            TextInput::make('hall_budget')
                                ->numeric()
                                ->label('Hall Budget')
                                ->prefix('₦')
                                ->placeholder('Enter your budget for the hall')
                                ->visible(fn(callable $get) => $get('need_hall')),

                            DatePicker::make('hall_from')
                                ->label('Hall Required From')
                                ->visible(fn(callable $get) => $get('need_hall')),

                            DatePicker::make('hall_to')
                                ->label('Hall Required To')
                                ->visible(fn(callable $get) => $get('need_hall')),

                            Toggle::make('need_meal')
                                ->label('Do you need meal services?')
                                ->reactive(),

                            TextInput::make('meal_attendees')
                                ->numeric()
                                ->label('Number of Meal Attendees')
                                ->placeholder('Enter the number of people for meals')
                                ->visible(fn(callable $get) => $get('need_meal')),

                            TextInput::make('meal_budget')
                                ->numeric()
                                ->label('Meal Budget (per person)')
                                ->prefix('₦')
                                ->placeholder('Enter your budget per person for meals')
                                ->visible(fn(callable $get) => $get('need_meal')),

                            CheckboxList::make('meal_options')
                                ->options([
                                    'breakfast' => 'Breakfast',
                                    'lunch' => 'Lunch',
                                    'dinner' => 'Dinner',
                                    'coffee_breaks' => 'Coffee Breaks',
                                    'special_dietary' => 'Special Dietary Requirements',
                                ])
                                ->label('Meal Options')
                                ->visible(fn(callable $get) => $get('need_meal')),

                            DatePicker::make('meal_from')
                                ->label('Meals Required From')
                                ->visible(fn(callable $get) => $get('need_meal')),

                            DatePicker::make('meal_to')
                                ->label('Meals Required To')
                                ->visible(fn(callable $get) => $get('need_meal')),
                        ])->columns(2),

                    Step::make('Review & Confirm')
                        ->schema([
                            Select::make('hotels')
                                ->multiple()
                                ->relationship('selectedHotels', 'name')
                                ->searchable()
                                ->preload()
                                ->native(false)
                                ->label('Preferred Hotels')
                                ->placeholder('Select your preferred hotels'),
                            Toggle::make('allow_external_bids')
                                ->label('Allow external hotels to bid for this booking?')
                                ->helperText('If enabled, hotels outside your preferred list can also offer their services'),
                        ]),
                ])
                    ->skippable(false)
                    ->persistStepInQueryString(),
                // ->submitAction(view('livewire.group-booking.submit-button')),
            ])
            ->statePath('data');
    }

    public function submitForm()
    {
        // Validate the form data
        $data = $this->form->getState();

        // Process the form data
        $booking = new ModelBooking();
        $booking->name = $data['name'];
        $booking->phone = $data['phone'];
        $booking->email = $data['email'] ?? null;
        $booking->organisation = $data['organisation'] ?? null;
        $booking->expected_guest = $data['expected_guest'];
        $booking->room_needed = $data['room_needed'] ?? null;
        $booking->room_type = $data['room_type'] ?? null;
        $booking->room_from = $data['room_from'] ?? null;
        $booking->room_to = $data['room_to'] ?? null;
        $booking->room_budget = $data['room_budget'] ?? null;
        $booking->need_hall = $data['need_hall'] ?? false;
        $booking->hall_capacity = $data['hall_capacity'] ?? null;
        $booking->hall_budget = $data['hall_budget'] ?? null;
        $booking->hall_from = $data['hall_from'] ?? null;
        $booking->hall_to = $data['hall_to'] ?? null;
        $booking->need_meal = $data['need_meal'] ?? false;
        $booking->meal_budget = $data['meal_budget'] ?? null;
        $booking->meal_attendees = $data['meal_attendees'] ?? null;
        $booking->meal_options = $data['meal_options'] ?? null;
        $booking->meal_from = $data['meal_from'] ?? null;
        $booking->meal_to = $data['meal_to'] ?? null;
        $booking->hotels = $data['hotels'] ?? null;
        $booking->allow_external_bids = $data['allow_external_bids'] ?? false;

        $booking->save();

        // Redirect or show success message
        session()->flash('success', 'Your group booking request has been submitted successfully!');

        return redirect()->route('booking.confirmation', ['id' => $booking->id]);
    }

    #[Layout("components.layouts.frontend")]
    public function render()
    {
        return view('livewire.group-booking');
    }
}