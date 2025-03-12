<?php

namespace App\Filament\Hotel\Resources;

use Filament\Forms;
use App\Models\Hall;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Hotel\Resources\HallResource\Pages;
use App\Filament\Hotel\Resources\HallResource\RelationManagers;

class HallResource extends Resource
{
    protected static ?string $model = Hall::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Rooms & Halls';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
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
                // Forms\Components\Select::make('type_id')
                //     ->relationship('type', 'name'),
                // Forms\Components\TextInput::make('deposit')
                //     ->numeric(),
                // Forms\Components\TextInput::make('promo_price')
                //     ->numeric(),
                // Forms\Components\DatePicker::make('promo_start_day'),
                // Forms\Components\DatePicker::make('promo_end_day'),
                // Forms\Components\DatePicker::make('reference'),
                Forms\Components\TextInput::make('remarks')
                    ->maxLength(255)
                    ->default('sellable'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
            ->actions([
                Tables\Actions\EditAction::make()
                    ->successNotificationTitle('Hall updated successfully'),
                Tables\Actions\DeleteAction::make()->successNotificationTitle('Hall deleted successfully'),
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
            'index' => Pages\ManageHalls::route('/'),
        ];
    }
}