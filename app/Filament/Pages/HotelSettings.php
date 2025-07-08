<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Tables;
use App\Models\Hotel;
use App\Models\Package;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;


class HotelSettings extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string $view = 'filament.pages.hotel-settings';
    protected static ?string $title = 'Hotel Settings';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 1;

    public ?array $hotel_data = [];

    public function mount(): void
    {
        $this->hotel_data = [
            'online_price' => Hotel::first()?->online_price ?? 10,
            'non_refundable_price' => Hotel::first()?->non_refundable_price ?? 5,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Package::query())
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('NGN')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->unique(Package::class, 'name'),
                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('NGN'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->unique(Package::class, 'name', ignoreRecord: true),
                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('NGN'),
                    ]),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function getForms(): array
    {
        return [
            'hotelForm' => $this->makeForm()
                ->schema($this->getHotelFormSchema())
                ->statePath('hotel_data'),
        ];
    }

    protected function getHotelFormSchema(): array
    {
        return [
            Section::make('Hotel Pricing Settings')
                ->description('Update pricing settings that will apply to all hotels')
                ->schema([
                    Forms\Components\TextInput::make('online_price')
                        ->label('Online Price (%)')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->suffix('%')
                        ->helperText('Percentage to add for online bookings'),

                    Forms\Components\TextInput::make('non_refundable_price')
                        ->label('Non-Refundable Price (%)')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->suffix('%')
                        ->helperText('Percentage to deduct for non-refundable bookings'),
                ])->columns(2),
        ];
    }

    protected function getHotelFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Hotel Settings')
                ->submit('hotelForm'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function saveHotelSettings(): void
    {
        try {
            $data = $this->getForm('hotelForm')->getState();

            $updatedCount = DB::transaction(function () use ($data) {
                return Hotel::query()->withTrashed()->update([
                    'online_price' => $data['online_price'],
                    'non_refundable_price' => $data['non_refundable_price'],
                    'updated_at' => now(),
                ]);
            });
            Notification::make()
                ->title('Hotel settings updated successfully')
                ->success()
                ->body("Updated pricing settings for {$updatedCount} hotel(s).")
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error updating hotel settings')
                ->danger()
                ->body('Please try again or contact support.')
                ->send();
        }
    }
}
