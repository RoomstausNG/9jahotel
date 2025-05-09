<?php

namespace App\Filament\Resources\HotelResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('url')
                    ->required()
                    ->disk('cloudinary')
                    ->visibility('private')
                    ->image()
                    ->imageEditor()
                    ->imageCropAspectRatio('16:9')
                    ->directory('hotels')
                // ->optimize('webp'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where('imageable_type', 'App\Models\Hotel'))
            ->recordTitleAttribute('url')
            ->columns([
                Tables\Columns\ImageColumn::make('url')
                    ->label('Hotel Image')
                    ->state(fn(Model $record) => url(getCloudinaryUrl($record->url)))
                    ->disk('cloudinary'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['imageable_type'] = 'App\Models\Hotel';
                        return $data;
                    })
                    ->hidden(fn() => $this->ownerRecord->images()->count() >= 10)
                    ->successNotificationTitle('Image created successfully.'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->successNotificationTitle('Image updated successfully.'),
                Tables\Actions\DeleteAction::make()
                    ->successNotificationTitle('Image deleted successfully.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}