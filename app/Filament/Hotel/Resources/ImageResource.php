<?php

namespace App\Filament\Hotel\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Image;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Hotel\Resources\ImageResource\Pages;
use App\Filament\Hotel\Resources\ImageResource\RelationManagers;

class ImageResource extends Resource
{
    protected static ?string $model = Image::class;
    protected static ?string $modelName = "Image";
    protected static ?string $navigationLabel = "Image";
    protected static ?string $navigationGroup = "Images & Video";
    protected static ?int $navigationSort = 8;

    protected static ?string $navigationIcon = 'heroicon-o-camera';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\TextInput::make('imageable_id')
                //     ->required()
                //     ->numeric(),
                // Forms\Components\TextInput::make('imageable_type')
                //     ->required()
                //     ->maxLength(255),
                Forms\Components\FileUpload::make('url')
                    ->label('Hotel Images')
                    ->required()
                    ->image()
                    ->imageEditor()
                    ->minSize(100)
                    ->maxSize(2048)
                    ->panelLayout('grid')
                    ->optimize('webp')
                    ->disk('cloudinary')->visibility('private')
                    ->directory('background')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $hotelId = auth()->user()->hotel_id;
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where('hotel_id', $hotelId)->where('imageable_id', $hotelId))
            ->columns([
                // Tables\Columns\TextColumn::make('imageable_id')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('imageable_type')
                //     ->searchable(),
                Tables\Columns\ImageColumn::make('url')
                    ->state(fn(Model $record) => url(getCloudinaryUrl($record->url)))
                    ->disk('cloudinary')
                    ->height(300),

                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
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
            'index' => Pages\ManageImages::route('/'),
        ];
    }
}
