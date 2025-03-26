<?php

namespace App\Filament\Resources\HotelResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use App\Models\Revenue;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\GeneralItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('revenue_id')
                    ->label('Revenue Head')
                    ->required()
                    ->relationship('revenueSource', 'source', modifyQueryUsing: fn(Builder $query) => $query->where('hotel_id', $this->ownerRecord->id))
                    ->searchable(['source'])
                    ->native(false)
                    ->preload(10)
                    ->createOptionForm([
                        Forms\Components\TextInput::make('source')
                            ->required(),
                    ])
                    ->createOptionUsing(function (array $data): int {
                        $data['hotel_id'] = $this->ownerRecord->id;
                        return Revenue::create($data)->getKey();
                    }),
                Forms\Components\Select::make('general_item_id')
                    ->label('Item')
                    ->required()
                    ->extraAttributes(['class' => 'capitalize'])
                    ->relationship('generalItem', 'name')
                    // ->options(function () {
                    //     return GeneralItem::query()
                    //         ->get()
                    //         ->mapWithKeys(function ($item) {
                    //             return [
                    //                 $item->id => "<div class='flex items-center gap-2'>
                    //                     <img src='" . getCloudinaryItemImage($item->id) . "' class='w-8 h-8 rounded-full object-cover' />
                    //                     <span class='capitalize'>{$item->name}</span>
                    //                 </div>"
                    //             ];
                    //         });
                    // })
                    ->searchable(['name'])
                    ->native(false)
                    ->preload(5)
                    ->allowHtml()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->unique(ignoreRecord: true)
                            ->required(),
                        Forms\Components\FileUpload::make('image')
                            ->required()
                            ->disk('cloudinary')
                            ->directory('items')
                            ->optimize('webp')
                            ->image()
                            ->imageEditor()
                            ->imageCropAspectRatio('1:1')
                            ->imageEditorAspectRatios([
                                '1:1',
                            ]),
                    ])
                    ->createOptionUsing(function (array $data): int {
                        $randomString = substr(str_shuffle($data['name']), 0, 3);
                        $data['code'] = strtoupper($randomString . rand(001, 999));
                        return GeneralItem::create($data)->getKey();
                    }),
                Forms\Components\TextInput::make('price')
                    ->label('Selling price')
                    ->numeric()
                    ->required()
                    ->prefix('NGN'),
                Forms\Components\TextInput::make('cost_price')
                    ->nullable()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('limit')
                    ->label('Re-order Limit')
                    ->nullable()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\ImageColumn::make('generalItem.image_url')
                    ->label('image')
                    ->state(fn(Model $record) => $record->generalItem?->image_url ? url($record->generalItem->image) : url('img/9jahotel.png'))
                    ->disk('cloudinary'),
                Tables\Columns\TextColumn::make('generalItem.name')
                    ->state(fn(Model $record) => $record->generalItem?->name ?? $record->name)
                    ->label('Item'),

                Tables\Columns\TextColumn::make('cost_price')
                    ->state(fn(Model $record) => formatAsNaira($record->cost_price)),
                Tables\Columns\TextColumn::make('price')
                    ->state(fn(Model $record) => formatAsNaira($record->price)),
                Tables\Columns\TextColumn::make('revenueSource.source')
                    ->label('Revenue Point'),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('limit')
                    ->label('Re-order Limit')
                    ->alignCenter(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $revenuehead = Revenue::find($data['revenue_id']);
                        $generalItem = GeneralItem::find($data['general_item_id']);
                        // $data['hotel_id'] = $revenuehead->hotel_id;
                        $data['name'] = $generalItem->name;
                        // $data['image'] = $generalItem->image;
                        return $data;
                    })->successNotificationTitle('Item created successfully.'),
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

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->type === 'both';
    }

    public function getContentTabIcon(): ?string
    {
        return 'heroicon-m-cog';
    }
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return "Revenue Items";
    }
}
