<?php

namespace App\Filament\Hotel\Resources;

use Filament\Forms;
use App\Models\Type;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Hotel\Resources\TypeResource\Pages;
use App\Filament\Hotel\Resources\TypeResource\RelationManagers;

class TypeResource extends Resource
{
    protected static ?string $model = Type::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-vertical';
    protected static ?string $navigationGroup = 'Rooms & Halls';
    protected static ?string $navigationLabel = 'Rooms Category';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('price1')
                    ->prefix('NGN')
                    ->numeric(),
                Forms\Components\TextInput::make('price2')
                    ->prefix('$')
                    ->numeric(),
                // Forms\Components\TextInput::make('deposit1')
                //     ->nullable()
                //     ->prefix('NGN')
                //     ->numeric(),
                // Forms\Components\TextInput::make('deposit2')
                //     ->nullable()
                //     ->prefix('$')
                //     ->numeric(),
                // Forms\Components\TextInput::make('weekend_price')
                //     ->prefix('NGN')
                //     ->numeric(),
                // Forms\Components\TextInput::make('weekend_start_day')

                //     ->maxLength(255),
                // Forms\Components\TextInput::make('weekend_end_day')
                //     ->maxLength(255),
                // Forms\Components\TextInput::make('promo_price')
                //     ->prefix('NGN')
                //     ->numeric(),
                // Forms\Components\DatePicker::make('promo_start_day'),
                // Forms\Components\DatePicker::make('promo_end_day'),
                Forms\Components\Textarea::make('remarks')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->where('hotel_id', auth()->user()->hotel_id)->orderByDesc('created_at');
            })
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price1')
                    ->state(fn(Model $record) => formatAsNaira($record->price1))
                    ->sortable(),
                Tables\Columns\TextColumn::make('price2')
                    ->state(fn(Model $record) => formatAsDollar($record->price2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('rooms_count')
                    ->label('No of Rooms')
                    ->alignCenter()
                    ->counts('rooms')
                    ->numeric()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('deposit1')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('deposit2')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('weekend_price')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('weekend_start_day')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('weekend_end_day')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('promo_price')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('promo_start_day')
                //     ->date()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('promo_end_day')
                //     ->date()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('hotel.name')
                //     ->numeric()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->successNotificationTitle('Room category updated successfully'),
                Tables\Actions\DeleteAction::make()
                    ->successNotificationTitle('Room category deleted successfully'),
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
            'index' => Pages\ManageTypes::route('/'),
        ];
    }
}