<?php

namespace App\Filament\Hotel\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Video;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Hotel\Resources\VideoResource\Pages;
use App\Filament\Hotel\Resources\VideoResource\RelationManagers;

class VideoResource extends Resource
{
    protected static ?string $model = Video::class;

    protected static ?string $navigationIcon = 'heroicon-o-video-camera';
    protected static ?string $navigationGroup = 'Images & Video';
    protected static string $view = 'filament.pages.view-video';

    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('url')
                    ->label('Hotel Video')
                    ->helpertext('Max video size of 20mb')
                    ->required()
                    ->disk('cloudinary')->visibility('private')
                    ->directory('video')
                    ->acceptedFileTypes([
                        'video/*',          // MP4
                        // 'video/x-msvideo',    // AVI
                        // 'video/quicktime',    // MOV
                        // 'video/x-matroska',   // MKV
                        // 'video/x-ms-wmv',     // WMV
                        // 'video/ogg',          // OGG
                        // 'video/webm',         // WebM
                        // 'video/x-flv',        // FLV
                        // 'video/3gpp',         // 3GP
                        // 'video/3gpp2',        // 3G2
                    ])
                    ->maxSize(20000),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListVideos::route('/'),
            'create' => Pages\CreateVideo::route('/create'),
            'view' => Pages\ViewVideo::route('/{record}'),
            'edit' => Pages\EditVideo::route('/{record}/edit'),
        ];
    }
}
