<?php

namespace App\Filament\Hotel\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Infolists;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\BookingInvoice;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\Section;
use Filament\Notifications\Actions\Action;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Hotel\Resources\BookingInvoiceResource\Pages;
use App\Filament\Hotel\Resources\BookingInvoiceResource\RelationManagers;

class BookingInvoiceResource extends Resource
{
    protected static ?string $model = BookingInvoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
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
                return $query->where('hotel_id', auth()->user()->hotel_id)->orderByDesc('created_at');
            })
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->state(fn(Model $record) => formatAsNaira($record->amount))
                    ->sortable(),

                Tables\Columns\TextColumn::make('hotel_payable')
                    ->state(fn(Model $record) => $record->hotel_payable ? formatAsNaira($record->hotel_payable) : null)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('bookings_count')
                    ->label('No of Rooms')
                    ->alignCenter()
                    ->counts('bookings')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->extraAttributes(['class' => 'capitalize'])
                    ->color(fn(string $state): string => match ($state) {
                        'unpaid' => 'warning',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('checked_in')
                    ->label('Checked In')
                    ->state(fn(Model $record) => $record->checked_in?->format('D M d, Y'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Booked On')
                    ->state(fn(Model $record) => $record->created_at?->format('D M d, Y'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->color('info'),
                    Tables\Actions\Action::make('cancel_request')
                        ->label('Request to Cancel')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->hidden(function (Model $record) {
                            if ($record->status == 'cancelled' || $record->status == 'paid') {
                                return true;
                            }
                            return false;
                        })

                        ->modalDescription('Are you sure you want to send request to cancel this booking?')
                        ->action(function (BookingInvoice $record) {
                            // $dates = getDateRange($data['from'], $data['to']);

                            // foreach ($dates as $date) {
                            //     $record->statuses()->firstOrCreate(
                            //         [
                            //             'room_id' => $record->id, // Conditions to check for existence
                            //             'date' => $date,
                            //         ],
                            //         [
                            //             'status' => 'paid', // Attributes to set if a new record is created
                            //         ]
                            //     );
                            // }
                            $myHotel = myHotel()->name;

                            $staff = User::role(['back-office'])->whereRelation('hotels', 'name', $myHotel)->get();


                            $admins = User::role(['super-admin'])->get();

                            foreach ($staff as $admin) {
                                Notification::make()
                                    ->color('warning')
                                    ->title("Booking Cancel alert from {$myHotel}")
                                    ->body("The booking with reference {$record->reference}. Kindly take neccessary action, you may want to call the customer to confirm or cancel the booking")
                                    ->actions([
                                        Action::make('view')
                                            ->button()
                                            ->url(route('filament.service.resources.bookings.index'), shouldOpenInNewTab: false),
                                        Action::make('cancel')
                                            ->button()
                                            ->color('danger')
                                            ->action(function (BookingInvoice $booking) {
                                                $booking->update([
                                                    'status' => "cancelled"
                                                ]);

                                                $users = User::role(["admin", "manager", 'frontdesk', 'frontdesk-manager'])->where("hotel_id", $booking->hotel_id)->get();

                                                foreach ($users as $hotelStaff) {
                                                    Notification::make()
                                                        ->title("Booking Cancelled from Back Office")
                                                        ->body("The booking with reference {$booking->reference} has been cancelled")
                                                        ->color('info')
                                                        ->sendToDatabase($hotelStaff);
                                                }
                                            })
                                            ->color('gray'),
                                    ])
                                    ->sendToDatabase($admin);
                            }

                            foreach ($admins as $admin) {
                                Notification::make()
                                    ->title("Booking Cancel alert from {$myHotel}")
                                    ->body("The booking with reference {$record->reference}. Kindly take neccessary action, you may want to called the customer to confirm or cancel the booking")
                                    ->actions([
                                        Action::make('view')
                                            ->button(),
                                        // ->url(route('posts.show', $post), shouldOpenInNewTab: true),
                                        Action::make('cancel')
                                            // ->action()
                                            ->color('gray'),
                                    ])
                                    ->sendToDatabase($admin);
                            }

                            Notification::make()
                                ->title("Success! Request sent to Back Office")
                                ->body("Kindly wait for the admin to respond to the message and the booking will be cancelled")
                                ->success()
                                ->seconds(10)
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
            'index' => Pages\ManageBookingInvoices::route('/'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('reference')
                    ->extraAttributes(['class' => 'font-medium']),
                Infolists\Components\TextEntry::make('customer.name')
                    ->label('Customer name'),
                Infolists\Components\TextEntry::make('customer.phone')
                    ->label('Phone Number'),
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
                    ->state(fn(Model $record) => $record->created_at?->format('D M d, Y')),
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

                // Section::make("Room Details")->schema([
                Infolists\Components\RepeatableEntry::make('bookings')
                    ->schema([
                        Infolists\Components\TextEntry::make('bookable.number')
                            ->label("Room Number"),
                        Infolists\Components\TextEntry::make('bookable.type.name')
                            ->label("Room Type"),
                        Infolists\Components\TextEntry::make('duration')
                            ->state(fn(Model $record) => $record->checked_in?->format('M d, Y') . " to " . $record->checked_out?->format('M d, Y')),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
                // ])->columns(3),
            ])->columns(5);
    }
}