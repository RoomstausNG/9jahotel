<?php

namespace App\Livewire;

use Closure;
use App\Models\Team;
use App\Models\User;
use App\Models\Hotel;
use Filament\Forms\Get;
use Livewire\Component;
use Filament\Forms\Form;
use Filament\Actions\Action;
use Livewire\WithFileUploads;
use Nnjeim\World\Models\City;
use Nnjeim\World\Models\State;
use Livewire\Attributes\Layout;
use Filament\Forms\Components\Radio;
use Illuminate\Support\Facades\Hash;
use App\Rules\UniqueAccountEmailRule;
use App\Rules\UniqueAccountPhoneRule;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Tabs\Step;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Illuminate\Validation\ValidationException;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Notifications\HotelCreationNotification;
use Illuminate\Support\Facades\Notification as ModelNotification;

class CreateHotel extends Component implements HasForms
{
    // public $name, $code, $country_id = 160, $background_image, $primary_currency, $secondary_currency, $city_id, $state_id, $primary_color, $surcharge, $telephone, $email, $checked_out, $address, $zipcode, $vat, $logo, $secondary_color, $firstname, $lastname, $phone, $can_login, $avatar, $current_role, $gender, $designation, $password, $password_confirmation, $department_id;

    use WithFileUploads;
    public $logo, $background_image;

    use InteractsWithForms;
    public ?array $data = [];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Hotel Details')
                        ->schema([
                            TextInput::make('name')
                                ->label('Hotel Name')
                                ->required()
                                ->columnSpan('full'),

                            Select::make('state_id')
                                ->label('State')
                                ->options(State::where('country_id', 160)->pluck('name', 'id')->toArray())
                                ->reactive()
                                ->afterStateUpdated(fn($state, callable $set) => $set('city_id', null))
                                ->native(false)
                                ->searchable()
                                ->required(),

                            Select::make('city_id')
                                ->label('City')
                                ->options(fn(Get $get) => City::where('state_id', $get('state_id'))->pluck('name', 'id')->toArray())
                                ->native(false)
                                ->searchable()
                                ->required(),
                            TextInput::make('address')
                                ->required(),
                            Select::make('team_id')
                                ->label('Team')
                                // ->relationship('team', 'name')
                                ->options(Team::all()->mapWithKeys(fn($team) => [$team->id => ucfirst($team->name . " - " . $team->code)]))
                                ->preload()
                                ->native(false)
                                ->searchable()
                                ->nullable(),
                            FileUpload::make('logo')
                                ->image()
                                ->imageEditor()
                                ->disk('cloudinary')->visibility('private')
                                ->directory('hotel_logo')
                                ->getUploadedFileNameForStorageUsing(function ($file) {
                                    $this->data['logo'] = 'hotel_logo/' . $file->getClientOriginalName();
                                    return $this->data['logo'];
                                })
                                ->avatar()
                                ->label('Logo')
                                ->maxSize(512)
                                ->required(),
                            FileUpload::make('background_image')
                                ->label('Background Image')
                                ->required()
                                ->image()
                                ->getUploadedFileNameForStorageUsing(function ($file) {
                                    $this->data['background_image'] = 'background/' . $file->getClientOriginalName();
                                    return $this->data['background_image'];
                                })
                                ->imageEditor()
                                ->disk('cloudinary')->visibility('private')
                                ->directory('background')
                                ->maxSize(2048),
                        ])->columns(2),

                    Wizard\Step::make('Owner Details')
                        ->schema([
                            TextInput::make('firstname')
                                ->label('First Name')
                                ->required(),
                            TextInput::make('lastname')
                                ->label('Last Name')
                                ->required(),
                            TextInput::make('telephone')
                                ->label('Telephone')
                                ->numeric()
                                ->required(),
                            TextInput::make('email')
                                ->label('Email')
                                ->email()
                                ->required()
                                ->rules([new UniqueAccountEmailRule()]),
                            Radio::make('gender')
                                ->label('Gender')
                                ->inline()
                                ->options([
                                    'male' => 'Male',
                                    'female' => 'Female',
                                ])->columnSpan('full')
                                ->nullable(),
                            TextInput::make('password')
                                ->label('Password')
                                ->password()
                                ->revealable()
                                ->required()
                                ->confirmed(),
                            TextInput::make('password_confirmation')
                                ->label('Confirm Password')
                                ->password()
                                ->revealable()
                                ->required(),
                        ])->columns(2),
                ])->columnSpan('full')
            ])
            // ->submitAction(view('components.form.submit'))
            ->columns(2)
            ->statePath('data');
    }

    // protected function onValidationError(ValidationException $exception): void
    // {
    //     Notification::make()
    //         ->title($exception->getMessage())
    //         ->danger()
    //         ->send();
    // }

    // protected $rules = [];

    public function signUp()
    {
        $this->validate([
            'data.name' => 'required|string|max:255',
            'data.telephone' => ['required', 'numeric', new UniqueAccountPhoneRule()],
            'data.email' => ['required', 'email', 'max:255', new UniqueAccountEmailRule()],
            'data.address' => 'required|string|max:255',
            'data.state_id' => 'required|integer',
            'data.city_id' => 'required|integer',
            'data.logo' => 'required|max:1024', // Example validation for logo
            'data.background_image' => 'nullable|max:2048', // Example validation for background image
            'data.password' => 'required|string|min:8|confirmed',
            'data.team_id' => ['nullable', 'exists:team,id'],
            'data.password_confirmation' => 'required|string|min:8',
        ]);

        if ($this->data['logo']) {
            foreach ($this->data['logo'] as $key => $value) {
                if (is_object($value)) {
                    $this->data['logo'] = $value->store('hotel_logo', 'cloudinary');
                }
            }
        }

        if ($this->data['background_image']) {
            foreach ($this->data['background_image'] as $key => $value) {
                if (is_object($value)) {
                    $this->data['background_image'] = $value->store('background', 'cloudinary');
                }
            }
        } else {
            $this->data['background_image'] = null;
        }

        // $city = City::find($this->data['city_id']);

        $hotel = Hotel::create([
            'name' => $this->data['name'],
            'address' => $this->data['address'],
            'state_id' => $this->data['state_id'],
            'city_id' => $this->data['city_id'],
            'type' => "9jahotel",
            'logo' => $this->data['logo'],
            'background_image' => $this->data['background_image'] ?? null,
            'telephone' => $this->data['telephone'],
            'email' => $this->data['email']
        ]);



        if ($hotel) {
            $created = $hotel->user()->create([
                'firstname' => $this->data['firstname'],
                'lastname' => $this->data['lastname'],
                'email' => $this->data['email'],
                'phone' => $this->data['telephone'],
                'current_role' => 'admin',
                // 'gender' => $this->data['gender'],
                'can_login' => true,
                'date_of_employment' => now(),
                'city_id' => $this->data['city_id'],
                'state_id' => $this->data['state_id'],
                'designation' => 'Owner',
                'address' => $this->data['address'],
                // 'country_id' => $this->data['country_id'],
                'password' => Hash::make($this->data['password'])
            ]);

            if ($created) {
                // send notification to hotel
                // $hotel->notify(new HotelCreationNotification($created));
                ModelNotification::sendNow($hotel, new HotelCreationNotification($created));

                $this->reset();
                $details = [
                    'text' => 'Your hotel has been successfully registered with us and an account has been created for you to manage it. Kindly check your email for details of your username and hotel code.',
                    'title' => 'Hotel account created successfully',
                    'timer' => 9000
                ];
                return $this->dispatch('swal:success', details: sweetAlert(...$details));
            }
        }
    }

    public function mount(): void
    {
        $this->form->fill();
    }


    #[Layout('layouts.frontend')]
    public function render()
    {
        return view('livewire.create-hotel');
    }
}
