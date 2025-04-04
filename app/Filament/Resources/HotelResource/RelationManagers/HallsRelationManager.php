<?php

namespace App\Filament\Resources\HotelResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\Colors\Color;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class HallsRelationManager extends RelationManager
{
    protected static string $relationship = 'halls';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Hall Management')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Details')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('price')
                                    ->numeric()
                                    ->prefix('NGN'),
                                Forms\Components\TextInput::make('capacity')
                                    ->required()
                                    ->numeric(),
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'available' => 'Available',
                                        'occupied' => 'Occupied'
                                    ])
                                    ->default('available'),
                                Forms\Components\RichEditor::make('description')
                                    ->columnSpanFull(),
                                Forms\Components\Toggle::make('is_available')
                                    ->default(true)
                                    ->required(),
                                Forms\Components\TextInput::make('remarks')
                                    ->maxLength(255)
                                    ->default('sellable'),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('Images')
                            ->schema([
                                Forms\Components\FileUpload::make('image')
                                    ->label("Main Hall Image")
                                    ->optimize('webp')
                                    ->disk('cloudinary')
                                    ->visibility('private')
                                    ->required()
                                    ->directory('hall')
                                    ->image()
                                    ->imageEditor()
                                    ->columnSpanFull(),

                                Forms\Components\Repeater::make('images')
                                    ->label('Other Images')
                                    ->relationship('images')
                                    ->schema([
                                        Forms\Components\FileUpload::make('url')
                                            ->image()
                                            ->optimize('webp')
                                            ->disk('cloudinary')
                                            ->visibility('private')
                                            ->directory('hall_images')
                                            ->imageEditor()
                                            ->required(),
                                    ])
                                    ->collapsible()
                                    ->collapseAllAction(
                                        fn(Forms\Components\Actions\Action $action) => $action->label('Collapse All Images')
                                    )
                                    ->expandAllAction(
                                        fn(Forms\Components\Actions\Action $action) => $action->label('Expand All Images')
                                    )
                                    ->columnSpanFull()
                                    ->defaultItems(0)
                                    ->reorderable(),
                            ])
                    ])
                    ->columnSpanFull()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->state(fn(Model $record) => formatAsNaira($record->price))
                    ->sortable(),
                Tables\Columns\TextColumn::make('capacity')
                    ->alignCenter()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_available')
                    ->alignCenter()
                    ->boolean(),
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
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->state(fn(Model $record) => $record->image ? url(getCloudinaryUrl($record->image)) : url('img/9jahotel.png'))
                    ->disk('cloudinary')
                    ->circular(),
                Tables\Columns\TextColumn::make('remarks')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}