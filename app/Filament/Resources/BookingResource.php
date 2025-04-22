<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Hotel;
use Filament\Infolists;
use Filament\Tables\Filters\QueryBuilder;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\BookingInvoice;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\Section;
use App\Filament\Resources\BookingResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\BookingResource\RelationManagers;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;

class BookingResource extends Resource
{
    protected static ?string $model = BookingInvoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $navigationLabel = 'Bookings';
    protected static ?string $modelLabel = 'Bookings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('reference')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('status')
                    ->maxLength(255)
                    ->default('unpaid'),
                Forms\Components\Select::make('customer_id')
                    ->relationship('customer', 'id'),
                Forms\Components\Select::make('hotel_id')
                    ->relationship('hotel', 'name'),
                Forms\Components\DatePicker::make('payed_on'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->orderByDesc('created_at');
            })
            ->columns([
                Tables\Columns\TextColumn::make('hotel.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reference')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount Received')
                    ->state(fn(Model $record) => formatAsNaira($record->amount))
                    ->sortable(),
                Tables\Columns\TextColumn::make('hotel_payable')
                    ->label('Amount Due Hotel')
                    ->state(fn(Model $record) => formatAsNaira($record->hotel_payable ?? $record->amount))
                    ->sortable(),
                Tables\Columns\TextColumn::make('commission_amount')
                    ->label('Commission')
                    ->state(fn(Model $record) => formatAsNaira($record->commission_amount ?? 0))
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->sortable(),

                Tables\Columns\TextColumn::make('bookings_count')
                    ->label('No of Rooms')
                    ->alignCenter()
                    ->counts('bookings')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->sortable()
                    ->badge()
                    ->extraAttributes(['class' => 'capitalize'])
                    ->color(fn(string $state): string => match ($state) {
                        'unpaid' => 'warning',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->searchable(),
                // Tables\Columns\TextColumn::make('payed_on')
                //     ->date()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('checked_in')
                    ->label('Checked In')
                    ->date()
                    ->default(fn(Model $record) => $record->bookings()?->first()?->checked_in)
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Booked On')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                QueryBuilder::make()
                    ->constraints([
                        DateConstraint::make('created_at')
                            ->label('Booked On'),
                    ])->columnSpanFull(),
                SelectFilter::make('hotel_id')
                    ->relationship('hotel', 'name')
                    ->label('Hotel')
                    ->preload()
                    ->native(false)
                    ->getOptionLabelFromRecordUsing(fn($record) => ucfirst("{$record->name}"))
                    ->searchable() // If you want a searchable dropdown
                    ->placeholder('Select Hotel'),
                SelectFilter::make('customer_id')
                    ->options(
                        fn() => Customer::query()
                            ->get()
                            ->mapWithKeys(fn($customer) => [
                                $customer->id => ucfirst("{$customer->firstname} {$customer->lastname}")
                            ])
                            ->toArray()
                    )
                    ->label('Customer/Client')
                    ->preload()
                    ->native(false)
                    ->getOptionLabelFromRecordUsing(fn($record) => ucfirst("{$record->firstname} {$record->lastname}"))
                    ->searchable() // If you want a searchable dropdown
                    ->placeholder('Select Customer/Client')
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->color('info'),
                    Tables\Actions\Action::make('cancel')
                        ->label('Cancelled')
                        ->icon('heroicon-m-archive-box-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->hidden(function (Model $record) {
                            if ($record->status == 'cancelled') {
                                return true;
                            }
                            return false;
                        })
                        ->action(function (BookingInvoice $record) {

                            $record->update([
                                'status' => 'cancelled',
                            ]);

                            // update the bookings
                            foreach ($record->bookings as $booking) {
                                $booking->update([
                                    "status" => "cancelled"
                                ]);
                            }

                            $hotel_staff = User::where("hotel_id", $record->hotel_id)->get();

                            foreach ($hotel_staff as $staff) {
                                Notification::make()
                                    ->title("Success! Booking cancelled successfully")
                                    ->body("The booking with reference {$record->reference} has been cancelled by 9Jahotel")
                                    ->danger()
                                    ->sendToDatabase($staff);
                            }

                            Notification::make()
                                ->title("Success! Booking cancelled")
                                ->body("The hotel and the customer will be notified")
                                ->success()
                                ->send();
                        }),
                    // Tables\Actions\DeleteAction::make(),
                ])->button()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageBookings::route('/'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('reference'),

                Infolists\Components\TextEntry::make('status')
                    ->badge()
                    ->extraAttributes(['class' => 'capitalize'])
                    ->color(fn(string $state): string => match ($state) {
                        'unpaid' => 'warning',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                    }),
                Infolists\Components\TextEntry::make('created_at')
                    ->label("Booked on")
                    ->state(fn(Model $record) => $record->created_at->format('D M d, Y')),
                Section::make("Money")->schema([
                    Infolists\Components\TextEntry::make('amount')
                        ->state(fn(Model $record) => formatAsNaira($record->amount)),
                    Infolists\Components\TextEntry::make('hotel_payable')
                        ->label("Amount Payable")
                        ->state(fn(Model $record) => formatAsNaira($record->hotel_payable ?? $record->amount)),
                    Infolists\Components\TextEntry::make('commission_amount')
                        ->label("Commission Amount")
                        ->state(fn(Model $record) => formatAsNaira($record->commission_amount)),
                ])->columns(3),

                Section::make("Customer Details")->schema([
                    Infolists\Components\TextEntry::make('customer.name')
                        ->label("Name"),
                    Infolists\Components\TextEntry::make('customer.phone')
                        ->label("Phone Number"),
                    Infolists\Components\TextEntry::make('customer.email')
                        ->label("Email Address"),
                ])->columns(3),
                Infolists\Components\RepeatableEntry::make('bookings')
                    ->schema([
                        Infolists\Components\TextEntry::make('bookable.number')
                            ->label("Room Number"),
                        Infolists\Components\TextEntry::make('bookable.type.name')
                            ->label("Room Type"),
                        Infolists\Components\TextEntry::make('duration')
                            ->state(fn(Model $record) => $record->checked_in->format('M d, Y') . " to " . $record->checked_out->format('M d, Y')),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

            ])->columns(3);
    }
}