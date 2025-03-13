<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\GeneralAmenity;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\GeneralAmenityResource\Pages;
use App\Filament\Resources\GeneralAmenityResource\RelationManagers;

class GeneralAmenityResource extends Resource
{
    protected static ?string $model = GeneralAmenity::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift-top';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('icon')
                    ->required()
                    ->startsWith('<svg')
                    ->helperText(new HtmlString('Click <a href="https://icones.js.org/collection/all" class="underline text-primary" target="_blank">here</a> To get an icon SVG code, search for your desired icon on any icon library website (like FontAwesome or Material Icons), click on the icon to select it, then look for and click an option labeled *SVG* or *Copy SVG* to view or copy the code, and finally paste the complete SVG code (from <svg> to </svg>) here.'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
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
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGeneralAmenities::route('/'),
            'create' => Pages\CreateGeneralAmenity::route('/create'),
            'edit' => Pages\EditGeneralAmenity::route('/{record}/edit'),
        ];
    }
}