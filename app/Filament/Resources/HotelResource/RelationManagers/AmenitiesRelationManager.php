<?php

namespace App\Filament\Resources\HotelResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\GeneralAmenity;
use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class AmenitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'amenities';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('general_amenity_id')
                    ->relationship('amenity')
                    ->searchable()
                    ->required()
                    ->preload(10)
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('icon')
                            ->required()
                            ->startsWith('<svg')
                            ->helperText(new HtmlString('Click <a href="https://icones.js.org/collection/all" class="underline text-primary" target="_blank">here</a> To get an icon SVG code, search for your desired icon on any icon library website (like FontAwesome or Material Icons), click on the icon to select it, then look for and click an option labeled *SVG* or *Copy SVG* to view or copy the code, and finally paste the complete SVG code here.'))
                            ->columnSpanFull(),
                    ])
                    ->createOptionUsing(function (array $data): int {
                        $data['hotel_id'] = $this->ownerRecord->id;
                        return GeneralAmenity::create($data)->getKey();
                    }),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->getStateUsing(fn($record) => $record->generalAmenity?->name ?? $record->name),
                Tables\Columns\TextColumn::make('icon')
                    ->getStateUsing(fn($record) => $record->generalAmenity?->icon ?? $record->icon)
                    ->formatStateUsing(fn(string $state): HtmlString => new HtmlString($state)),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->successNotificationTitle('Amenity created successfully.'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->successNotificationTitle('Amenity updated successfully.'),
                Tables\Actions\DeleteAction::make()
                    ->successNotificationTitle('Amenity deleted successfully.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
