<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GeneralItemResource\Pages;
use App\Filament\Resources\GeneralItemResource\RelationManagers;
use App\Models\GeneralItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GeneralItemResource extends Resource
{
    protected static ?string $model = GeneralItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->maxLength(255),
                // Forms\Components\TextInput::make('code')
                //     ->maxLength(255),
                Forms\Components\FileUpload::make('image')
                    ->disk('cloudinary')->visibility('private')
                    ->directory('items')
                    ->optimize('webp')
                    ->image()
                    ->required()
                    ->imageEditor()
                    ->imageCropAspectRatio('1:1')
                    ->imageEditorAspectRatios([
                        '1:1',
                    ]),
                // Forms\Components\TextInput::make('price')
                //     ->numeric()
                //     ->nullable()
                //     ->prefix('$'),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    // ->directory('items')
                    ->disk('cloudinary')
                    ->default(fn($state)=> )
                    ->visibility('private'),
                Tables\Columns\TextColumn::make('name')
                    ->copyable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->alignCenter()
                    ->counts('items'),
            ])
            ->filters([
                //
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageGeneralItems::route('/'),
        ];
    }
}