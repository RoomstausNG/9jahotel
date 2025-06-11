<?php

namespace App\Filament\Hotel\Resources;

use Filament\Forms;
use App\Models\Room;
use App\Models\Type;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Hotel\Resources\RoomResource\Pages;
use App\Filament\Hotel\Resources\RoomResource\RelationManagers;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;
    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    protected static ?string $navigationGroup = 'Rooms & Halls';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('number')
                    ->required()
                    ->integer()
                    ->maxLength(255),
                Forms\Components\Select::make('type_id')
                    ->label("Room Category")
                    ->relationship('type', modifyQueryUsing: fn(Builder $query) => $query->where('hotel_id', auth()->user()->hotel_id)->orderBy('price1'),)
                    ->getOptionLabelFromRecordUsing(fn(Model $record) => strtoupper($record->name) . " " . formatAsNaira($record->price1) . "")
                    ->native(false)
                    ->preload()
                    ->searchable(['price1', 'name'])
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Forms\Components\TextInput::make('price1')
                            ->label('Price')
                            ->required()
                            ->prefix('NGN'),
                    ])->columns(1)
                    ->createOptionAction(
                        fn(Action $action) => $action->modalWidth('sm'),
                    ),
                // ->createOptionUsing(function (array $data): int {
                //     return auth()->user()->team->roomCategory()->create($data)->getKey();
                // }),
                Forms\Components\Select::make('floor_id')
                    ->relationship('floor', 'name')
                    ->extraAttributes(['class' => 'capitalize'])
                    ->getOptionLabelFromRecordUsing(fn(Model $record) => ucfirst($record->name) . "  Floor"),
                // Forms\Components\Select::make('hotel_id')
                //     ->relationship('hotel', 'name'),
                // Forms\Components\TextInput::make('price')
                //     ->numeric()
                //     ->prefix('$'),
                Forms\Components\TextInput::make('capacity')
                    ->required()
                    ->numeric(),
                Forms\Components\Select::make('bed_type')
                    ->label('Bed Type')
                    ->preload()
                    ->native(false)
                    ->required()
                    ->options([
                        'single bed' => 'Single bed',
                        'twin bed' => 'Twin bed',
                        'double bed' => 'Double bed',
                        'queen bed' => 'Queen bed',
                        'king bed' => 'King bed',
                        'sofa bed' => 'Sofa bed',
                        'bunk bed' => 'Bunk bed',
                    ])
                    ->placeholder('Select bed type'),
                // Forms\Components\Select::make('status')
                //     ->options([
                //         'available' => "Available",
                //         'repair' => "Repair",
                //     ])
                //     ->default('available'),
                Forms\Components\Select::make('character')
                    ->label('Character')
                    ->options([
                        'no smoking' => 'No Smoking',
                        'smoking' => 'Smoking',
                    ])
                    ->default('no smoking')
                    ->required(),
                // Forms\Components\Textarea::make('description')
                //     ->columnSpanFull(),
                Forms\Components\FileUpload::make('image')
                    ->imageEditor()
                    ->disk('cloudinary')->visibility('private')

                    ->directory('rooms')
                    ->image()
                    ->columnSpanFull(),
                // Forms\Components\Toggle::make('is_available')
                //     ->required(),
                Forms\Components\TextInput::make('comment')
                    ->columnSpanFull()
                    ->maxLength(255),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->where('hotel_id', auth()->user()->hotel_id)->orderByDesc('created_at');
            })
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('type.name')
                    ->extraAttributes(['class' => 'capitalize'])
                    ->suffix(" room")
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('floor.name')
                    ->extraAttributes(['class' => 'capitalize'])
                    ->suffix(" Floor")
                    ->searchable()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('hotel.name')
                //     ->numeric()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->state(fn(Model $record) => formatAsNaira($record->type?->price1 ?? $record->price))
                    ->sortable(),
                Tables\Columns\TextColumn::make('capacity')
                    ->numeric()
                    ->alignCenter()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('bed_type')
                //     ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label("Today's Status")
                    ->state(fn(Model $record) => $record->currentStatus(today()))
                    ->badge()
                    ->color(fn($record) => match ($record->status) {
                        'paid' => 'success',
                        'unpaid' => 'warning',
                        'unavailable' => Color::Hex('#000000'),
                        'available' => Color::Hex('#ddd'),
                        'repair' => Color::Hex('#000000'),
                        'dirty' => Color::Hex('#A52A2A'),
                        'occupied' => Color::Hex('#FF0000')
                    })
                    ->extraAttributes(['class' => 'capitalize'])
                    ->searchable(),
                // Tables\Columns\TextColumn::make('character')
                //     ->searchable(),
                // Tables\Columns\ImageColumn::make('image'),
                // Tables\Columns\IconColumn::make('is_available')
                //     ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                // Tables\Columns\TextColumn::make('updated_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                // Tables\Columns\TextColumn::make('comment')
                //     ->searchable(),
            ])
            ->filters([
                SelectFilter::make('type_id')
                    ->options(fn() => Type::where('hotel_id', auth()->user()->hotel_id)->get()->pluck('name', 'id')->toArray())
                    ->label('Room Category')
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn($record) => ucfirst("{$record->name} (Room)"))
                    ->searchable() // If you want a searchable dropdown
                    ->placeholder('Select Room'),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\Action::make('set_unavailability')
                        ->label('Set Unavailability')
                        ->form([
                            Forms\Components\DatePicker::make('from')
                                ->afterOrEqual(today())
                                ->label('Starting From')
                                ->required(),
                            Forms\Components\DatePicker::make('to')
                                ->label('Ending At')
                                ->after('from')
                                ->required(),
                        ])
                        ->icon('heroicon-m-eye')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (array $data, Room $record) {
                            $dates = getDateRange($data['from'], $data['to']);
                            foreach ($dates as $date) {
                                $record->statuses()->firstOrCreate(
                                    [
                                        'room_id' => $record->id, // Conditions to check for existence
                                        'date' => $date,
                                    ],
                                    [
                                        'status' => 'occupied', // Attributes to set if a new record is created
                                    ]
                                );
                            }
                            $myHotel = myHotel()->name;
                            $admins = User::role(['super-admin', 'back-office'])->get();

                            foreach ($admins as $admin) {
                                Notification::make()
                                    ->color('info')
                                    ->title("Unavailablity alert from {$myHotel}")
                                    ->body("Room number {$record->number} is now set for Unavailable from {$data['from']} to {$data['to']}")
                                    ->sendToDatabase($admin);
                            }
                            Notification::make()
                                ->title("Success! Your room will not be available for booking from {$data['from']} to {$data['to']}")
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\EditAction::make()
                        ->color('info')
                        ->successNotificationTitle('Room Updated successfully!'),
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
            'index' => Pages\ManageRooms::route('/'),
        ];
    }
}
