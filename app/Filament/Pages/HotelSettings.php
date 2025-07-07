<?php

namespace App\Filament\Pages;

use App\Models\Hotel;
use App\Models\Package;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;

class HotelSettings extends Page
{
  protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
  protected static string $view = 'filament.pages.hotel-settings';
  protected static ?string $title = 'Hotel Settings';
  protected static ?string $navigationGroup = 'Settings';

  public ?array $hotel_data = [];
  public ?array $package_data = [];

  public function mount(): void
  {
    $this->hotel_data = [
      'online_price' => Hotel::first()?->online_price ?? 10,
      'non_refundable_price' => Hotel::first()?->non_refundable_price ?? 5,
    ];

    $this->package_data = [
      'packages' => Package::all()->map(function ($package) {
        return [
          'id' => $package->id,
          'name' => $package->name,
          'amount' => $package->amount,
        ];
      })->toArray(),
    ];
  }

  public function hotelForm(Form $form): Form
  {
    return $form
      ->schema([
        Section::make('Hotel Pricing Settings')
          ->description('Update pricing settings that will apply to all hotels')
          ->schema([
            Forms\Components\TextInput::make('online_price')
              ->label('Online Price (%)')
              ->required()
              ->numeric()
              ->suffix('%')
              ->helperText('Percentage to add for online bookings'),

            Forms\Components\TextInput::make('non_refundable_price')
              ->label('Non-Refundable Price (%)')
              ->required()
              ->numeric()
              ->suffix('%')
              ->helperText('Percentage to deduct for non-refundable bookings'),
          ])->columns(2),
      ])
      ->statePath('hotel_data');
  }

  public function packageForm(Form $form): Form
  {
    return $form
      ->schema([
        Section::make('Package Settings')
          ->description('Manage subscription packages for invoices')
          ->schema([
            Repeater::make('packages')
              ->schema([
                Forms\Components\Hidden::make('id'),
                Forms\Components\TextInput::make('name')
                  ->label('Package Name')
                  ->required()
                  ->unique(Package::class, 'name', ignoreRecord: fn($record) => $record?->id),

                Forms\Components\TextInput::make('amount')
                  ->label('Amount')
                  ->required()
                  ->numeric()
                  ->prefix('NGN')
                  ->formatStateUsing(fn($state) => number_format($state ?? 0)),
              ])
              ->columns(2)
              ->addActionLabel('Add Package')
              ->deleteAction(
                fn(Action $action) => $action->requiresConfirmation()
              ),
          ]),
      ])
      ->statePath('package_data');
  }

  protected function getHeaderActions(): array
  {
    return [
      Action::make('saveHotelSettings')
        ->label('Save Hotel Settings')
        ->action('saveHotelSettings')
        ->color('primary'),

      Action::make('savePackageSettings')
        ->label('Save Package Settings')
        ->action('savePackageSettings')
        ->color('success'),
    ];
  }

  public function saveHotelSettings(): void
  {
    $data = $this->hotelForm(new Form())->getState();

    // Update all hotels with the new pricing settings
    Hotel::query()->update([
      'online_price' => $data['online_price'],
      'non_refundable_price' => $data['non_refundable_price'],
    ]);

    Notification::make()
      ->title('Hotel settings updated successfully')
      ->success()
      ->body('All hotels have been updated with the new pricing settings.')
      ->send();
  }

  public function savePackageSettings(): void
  {
    $data = $this->packageForm(new Form())->getState();

    foreach ($data['packages'] as $packageData) {
      // Clean the amount value (remove commas and convert to integer)
      $amount = str_replace(',', '', $packageData['amount']);
      $amount = (int) $amount;

      if (isset($packageData['id'])) {
        // Update existing package
        Package::where('id', $packageData['id'])->update([
          'name' => $packageData['name'],
          'amount' => $amount,
        ]);
      } else {
        // Create new package
        Package::create([
          'name' => $packageData['name'],
          'amount' => $amount,
        ]);
      }
    }

    // Remove packages that are no longer in the list
    $existingIds = collect($data['packages'])->pluck('id')->filter();
    Package::whereNotIn('id', $existingIds)->delete();

    // Refresh the form data
    $this->mount();

    Notification::make()
      ->title('Package settings updated successfully')
      ->success()
      ->body('All packages have been updated.')
      ->send();
  }
}
