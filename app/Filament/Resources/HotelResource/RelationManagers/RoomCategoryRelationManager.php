<?php

namespace App\Filament\Resources\HotelResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Wizard\Step;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class RoomCategoryRelationManager extends RelationManager
{
    protected static string $relationship = 'roomCategory';
    // protected static string $modelLabel = 'Room Category';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Room Category Details')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->required(),

                            Forms\Components\TextInput::make('price1')
                                ->prefix('₦')
                                ->numeric(),

                            Forms\Components\TextInput::make('price2')
                                ->prefix('$')
                                ->numeric(),

                            Forms\Components\TextInput::make('deposit1')
                                ->prefix('₦')
                                ->numeric(),

                            Forms\Components\TextInput::make('deposit2')
                                ->prefix('$')
                                ->numeric(),

                            Forms\Components\TextInput::make('weekend_price')
                                ->prefix('₦')
                                ->numeric(),

                            Forms\Components\Select::make('weekend_start_day')
                                ->options([
                                    'friday' => 'Friday',
                                    'saturday' => 'Saturday'
                                ])
                                ->nullable(),

                            Forms\Components\Select::make('weekend_end_day')
                                ->options([
                                    'saturday' => 'Saturday',
                                    'sunday' => 'Sunday'
                                ])
                                ->nullable(),

                            Forms\Components\TextInput::make('promo_price')
                                ->prefix('₦')
                                ->numeric(),

                            Forms\Components\DatePicker::make('promo_start_day'),

                            Forms\Components\DatePicker::make('promo_end_day')
                                ->after('promo_start_day'),

                            Forms\Components\Textarea::make('remarks'),
                        ])->columns(3),
                    Step::make('Room Details')
                        ->schema([
                            Repeater::make('rooms')
                                ->relationship()
                                ->schema([
                                    Forms\Components\TextInput::make('number')
                                        ->required(),

                                    // Forms\Components\TextInput::make('price')
                                    //     ->readOnly()
                                    //     ->prefix('₦')
                                    //     ->live()
                                    //     ->extraAttributes([
                                    //         'class' => 'text-gray-400 bg-gray-200 font-semibold',
                                    //         'readonly' => true,
                                    //     ])
                                    //     ->numeric(),

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

                                    Forms\Components\Select::make('status')
                                        ->options([
                                            'available' => 'Available',
                                            // 'occupied' => 'Occupied',
                                            'repair' => 'Repair',
                                            'dirty' => 'Dirty',
                                        ])
                                        ->default('available'),

                                    Forms\Components\Select::make('character')
                                        ->options([
                                            'no smoking' => 'No Smoking',
                                            'smoking' => 'Smoking',
                                        ])
                                        ->default('no smoking'),

                                    Forms\Components\Textarea::make('description'),

                                    Forms\Components\FileUpload::make('image')
                                        ->disk('cloudinary')->visibility('private')
                                        ->directory('room')
                                        ->imageEditor()
                                        ->optimize('webp')
                                        ->image(),
                                ])
                                ->columns(4)
                                ->columnSpanFull(),
                        ]),
                ])->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->extraAttributes(['class' => 'capitalize']),
                Tables\Columns\TextColumn::make('price1')
                    ->label('Naira Price')
                    ->state(
                        fn(Model $value) =>
                        formatAsNaira($value->price1)
                    ),
                Tables\Columns\TextColumn::make('price2')
                    ->label('Dollar Price')
                    ->state(
                        fn(Model $value) =>
                        formatAsDollar($value->price2)
                    ),
                Tables\Columns\TextColumn::make('rooms_count')
                    ->label('Total Rooms')
                    ->alignCenter()
                    ->counts('rooms'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->successNotificationTitle('Room Category created successfully'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->successNotificationTitle('Room Category updated successfully'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
