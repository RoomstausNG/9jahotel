<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Power;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PowerResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PowerResource\RelationManagers;

class PowerResource extends Resource
{
    protected static ?string $model = Power::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('number')
                    ->maxLength(255),
                Forms\Components\Textarea::make('icon')
                    ->required()
                    ->startsWith('<svg')
                    ->helperText(new HtmlString('Click <a href="https://icones.js.org/collection/all" class="underline text-primary" target="_blank">here</a> To get an icon SVG code, search for your desired icon on any icon library website (like FontAwesome or Material Icons), click on the icon to select it, then look for and click an option labeled *SVG* or *Copy SVG* to view or copy the code, and finally paste the complete SVG code here.'))
                    ->columnSpanFull(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->extraAttributes(['class' => 'capitalize'])
                    ->searchable(),
                Tables\Columns\TextColumn::make('number')
                    ->alignCenter()
                    ->searchable(),
                Tables\Columns\TextColumn::make('icon')
                    ->formatStateUsing(fn(string $state): HtmlString => new HtmlString($state)),
                // ->html(),
                Tables\Columns\TextColumn::make('hotels_count')
                    ->counts('hotels')
                    ->label('Hotel Usage')
                    ->alignCenter(),

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
                Tables\Actions\DeleteAction::make()->hidden(fn(Power $power) => $power->hotels()->exists()),
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
            'index' => Pages\ManagePowers::route('/'),
        ];
    }
}