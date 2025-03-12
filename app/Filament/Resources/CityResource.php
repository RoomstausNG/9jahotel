<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\CityCopy;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CityResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CityResource\RelationManagers;

class CityResource extends Resource
{
    protected static ?string $model = CityCopy::class;
    protected static ?string $modelLabel = 'City';

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('state_id')
                    ->relationship('state', 'name')
                    ->label('State')
                    ->searchable()
                    ->native(false)
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->label('City Name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where('country_id', 160))
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('Index')
                    ->getStateUsing(fn($rowLoop) => $rowLoop->iteration),
                Tables\Columns\TextColumn::make('name')
                    ->label('City Name')
                    ->extraAttributes(['class' => 'font-medium'])
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('state.name')
                    ->label('State')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('hotels_count')
                    ->label('Hotels')
                    ->counts('hotels')
                    ->extraAttributes(['class' => 'font-semibold'])
                    ->alignCenter()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->successNotificationTitle('City updated successfully'),
                Tables\Actions\DeleteAction::make()
                    ->before(function (DeleteAction $action, $record) {
                        if ($record->hotels()->exists()) {
                            Notification::make()
                                ->warning()
                                ->title('This city has hotels!')
                                ->body('You cannot delete a city that has hotels.')
                                ->persistent()
                                ->send();

                            $action->halt();
                        }
                    })->successNotificationTitle('City deleted successfully'),
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
            'index' => Pages\ManageCities::route('/'),
        ];
    }
}