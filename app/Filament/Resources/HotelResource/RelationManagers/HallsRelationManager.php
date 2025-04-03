<?php

namespace App\Filament\Resources\HotelResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\Colors\Color;
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
                Forms\Components\FileUpload::make('image')
                    ->optimize('webp')
                    ->disk('cloudinary')
                    ->visibility('private')
                    ->directory('hall')
                    ->image()
                    ->imageEditor(),
                Forms\Components\Toggle::make('is_available')
                    ->default(true)
                    ->required(),
                Forms\Components\TextInput::make('remarks')
                    ->maxLength(255)
                    ->default('sellable'),
                Forms\Components\Repeater::make('images')
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
                        Forms\Components\TextInput::make('alt')
                            ->label('Image Description')
                            ->maxLength(255),
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
                    ->reorderable()
                    ->grid(2),
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

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Tables\Columns\TextColumn::make('promo_price')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('promo_start_day')
                //     ->date()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('promo_end_day')
                //     ->date()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('reference')
                //     ->date()
                //     ->sortable(),
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
                Tables\Columns\TextColumn::make('remarks')
                    ->searchable(),
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