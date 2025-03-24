<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Team;
use Filament\Tables;
use App\Models\Hotel;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\CityCopy;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Nnjeim\World\Models\City;
use Nnjeim\World\Models\State;
use Filament\Resources\Resource;
use Nnjeim\World\Models\Country;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Mokhosh\FilamentRating\Components\Rating;
use App\Filament\Resources\HotelResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Cloudinary\Transformation\Argument\Text\FontWeight;
use App\Filament\Resources\HotelResource\RelationManagers;
use HusamTariq\FilamentTimePicker\Forms\Components\TimePickerField;


class HotelResource extends Resource
{
    protected static ?string $model = Hotel::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('logo')
                    ->image()
                    ->optimize('webp')
                    ->disk('cloudinary')->visibility('private')
                    ->imageEditor()
                    ->required()
                    ->directory('hotel_logo'),
                Forms\Components\FileUpload::make('background_image')
                    ->image()
                    ->disk('cloudinary')->visibility('private')
                    ->imageEditor()
                    ->columnSpan(3)
                    ->required()
                    ->directory('hotel_background'),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->hiddenOn('create')
                    ->readonly()
                    ->required()
                    ->maxLength(255),
                // Forms\Components\FileUpload::make('background_image')
                //     ->image(),
                Forms\Components\TextInput::make('address')
                    ->required()
                    ->maxLength(255),
                // Forms\Components\Select::make('primary_currency')
                //     // ->relationship()
                //     ->hiddenOn("create")
                //     ->options(Currency::all()->pluck('name', 'id'))
                //     ->native(false)
                //     ->preload()
                //     ->searchable(),
                // Forms\Components\Select::make('secondary_currency')
                //     // ->relationship()
                //     ->hiddenOn("create")
                //     ->options(Currency::all()->pluck('name', 'id'))
                //     ->native(false)
                //     ->preload()
                //     ->searchable(),
                Forms\Components\Select::make('country_id')
                    ->label('Country')
                    ->preload()
                    ->default(160)
                    ->options(Country::query()->pluck('name', 'id')->toArray()) // Limit the number of records fetched
                    ->extraAttributes(['class' => 'cursor-not-allowed'])
                    ->searchable()
                    ->placeholder('Nigeria')
                    ->nullable()
                    ->live()
                    ->native(false)
                    ->afterStateUpdated(function (Set $set) {
                        $set('state_id', null);
                        $set('city_id', null);
                    }),
                Forms\Components\Select::make('state_id')
                    ->label('State')
                    ->options(fn(Get $get): Collection
                    => State::query()
                        ->where('country_id', $get('country_id'))
                        ->pluck('name', 'id'))
                    ->searchable(['name'])
                    ->nullable()
                    ->preload()
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(fn(Set $set) => $set('city_id', null)),
                // Forms\Components\Select::make('state_id')
                //     ->label('State')
                //     ->options(fn(Get $get): Collection
                //     => State::query()
                //         ->where('country_id', $get('country_id'))
                //         ->pluck('name', 'id'))
                //     ->searchable(['name'])
                //     ->nullable()
                //     ->preload()
                //     ->native(false)
                //     ->live()
                //     // ->default(fn($record) => $record ? $record->state_id : null)
                //     ->afterStateUpdated(fn(Set $set) => $set('city_id', null)),

                Forms\Components\Select::make('city_id')
                    ->label('City/Town')
                    ->relationship(name: 'city', titleAttribute: 'name')
                    ->options(fn(Get $get): Collection
                    => City::query()
                        // ->where('country_id', $get('country_id'))
                        ->where('state_id', $get('state_id'))
                        ->pluck('name', 'id'))
                    ->searchable(['name'])
                    ->nullable()
                    ->helperText("if you can'\t find your town/city, select the nearest city. Also contact us via email,phone and chat")
                    ->preload()
                    ->live()
                    ->native(false),
                Forms\Components\TextInput::make('telephone')
                    ->prefix('+234')
                    ->tel()
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('zip_code')
                    ->maxLength(255),
                Forms\Components\TextInput::make('service_charge')
                    ->hiddenOn("create")
                    ->numeric(),
                Forms\Components\Toggle::make('vat'),
                Forms\Components\TextInput::make('surcharge')
                    ->hiddenOn("create")
                    ->numeric(),
                // Forms\Components\TextInput::make('logo')
                //     ->maxLength(255),
                // Forms\Components\ColorPicker::make('primary_color')
                //     ->required(),
                // Forms\Components\ColorPicker::make('secondary_color')
                //     ->required(),
                TimePickerField::make('checked_out')
                    ->label('Checkout Time')
                    ->okLabel("Confirm")
                    ->cancelLabel("Cancel"),

                Forms\Components\Select::make('status')
                    ->options(['active' => 'Active', 'inactive' => 'Inactive'])
                    ->hiddenOn('create')
                    ->default('active'),
                Forms\Components\Select::make('type')
                    ->options(['both' => 'Both', '9jahotel' => '9jaHotel'])
                    ->default('active'),
                Forms\Components\DatePicker::make('renew')
                    ->hiddenOn('create'),
                // ->readonly(),
                Forms\Components\TextInput::make('percentage_charge')
                    ->suffix('%')
                    ->numeric()
                    ->default(10),
                Forms\Components\TextInput::make('online_price')
                    ->suffix('%')
                    ->hiddenOn("create")
                    ->numeric()
                    ->default(10),
                Forms\Components\TextInput::make('non_refundable_price')
                    ->suffix('%')
                    ->hiddenOn("create")
                    ->numeric()
                    ->default(15),
                Forms\Components\Select::make('team_id')
                    ->label('Team')
                    // ->relationship('team', 'name')
                    ->options(Team::all()->mapWithKeys(fn($team) => [$team->id => ucfirst($team->name . " - " . $team->code)]))
                    ->preload()
                    ->visibleOn('create')
                    ->native(false)
                    ->searchable()
                    ->nullable(),
                Rating::make('star_rating')->color('success'),
                Forms\Components\Select::make('amenities')
                    ->multiple()
                    ->label('Amenities')
                    ->relationship('amenities', 'name')
                    ->preload()
                    ->extraAttributes(['class' => 'capitalize'])
                    ->native(false)
                    ->searchable()
                    ->nullable()
                    ->columnSpan(['lg' => 3])
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('icon')
                            ->required()
                            ->startsWith('<svg')
                            ->helperText(new HtmlString('Click <a href="https://icones.js.org/collection/all" class="underline text-primary" target="_blank">here</a> To get an icon SVG code, search for your desired icon on any icon library website (like FontAwesome or Material Icons), click on the icon to select it, then look for and click an option labeled *SVG* or *Copy SVG* to view or copy the code, and finally paste the complete SVG code here.'))
                            ->columnSpanFull(),
                    ]),
                Forms\Components\RichEditor::make('description')
                    ->columnSpanFull(),
            ])->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->size(48)
                    ->state(fn(Model $record) => $record->logo ? url(getCloudinaryUrl($record->logo)) : url('img/9jahotel.png'))
                    ->disk('cloudinary')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->weight(FontWeight::MEDIUM)
                    ->sortable()
                    ->tooltip(fn($record) => strtoupper($record->type))
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->sortable()
                    ->searchable(),
                // Tables\Columns\ImageColumn::make('background_image'),
                Tables\Columns\TextColumn::make('address')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('city.name')
                    ->sortable()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('telephone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\ColorColumn::make('primary_color')
                    ->label('PC')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\ColorColumn::make('secondary_color')
                    ->label('SC')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label("Created On")
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('renew')
                    ->extraAttributes(['class' => 'text-red-500'])
                    ->weight(FontWeight::BOLD)
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->extraAttributes(['class' => 'text-red-500'])
                    ->state(fn(Model $record) => ucfirst("{$record->createdBy?->firstname}   {$record->createdBy?->lastname}"))
                    ->weight(FontWeight::MEDIUM)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->extraAttributes(['class' => 'capitalize'])
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                SelectFilter::make('city_id')
                    ->options(fn() => CityCopy::with('hotels')->has('hotels')->get()->pluck('name', 'id')->toArray())
                    ->label('City')
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn($record) => ucfirst("{$record->name} ({$record->hotels->count()})"))
                    ->searchable() // If you want a searchable dropdown
                    ->placeholder('Select City'),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\InvoicesRelationManager::class,
            RelationManagers\UserRelationManager::class,
            RelationManagers\RoomCategoryRelationManager::class,
            RelationManagers\RevenuesRelationManager::class,
            RelationManagers\ItemsRelationManager::class,
            // RelationManagers\AmenitiesRelationManager::class,
            RelationManagers\ImagesRelationManager::class,
            RelationManagers\FaqsRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHotels::route('/'),
            'create' => Pages\CreateHotel::route('/create'),
            'edit' => Pages\EditHotel::route('/{record}/edit'),
        ];
    }
}
