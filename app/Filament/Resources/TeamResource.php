<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamResource\Pages;
use App\Filament\Resources\TeamResource\RelationManagers;
use App\Models\Team;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TeamResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('marketers')
                    ->relationship('marketers')
                    ->multiple()
                    ->extraAttributes(['class' => 'capitalize'])
                    ->options(fn() => \App\Models\User::role(['marketer', 'back-office'])->get()->mapWithKeys(function ($user) {
                        return [$user->id => ucfirst("{$user->firstname} {$user->lastname}")];
                    }))->createOptionForm([
                        Forms\Components\FileUpload::make('avatar')
                            ->image()
                            ->avatar()
                            ->disk('cloudinary')->visibility('private')
                            ->directory('staff')
                            ->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('firstname')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('lastname')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->prefix('+234')
                            ->unique(ignoreRecord: true)
                            ->tel()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->prefix("@")
                            ->unique(ignoreRecord: true)
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('date_of_employment')
                            ->before(now())
                            ->required(),

                        Forms\Components\Select::make('current_role')
                            ->required()
                            ->default('marketer')
                            ->options([
                                'marketer' => 'Marketer',
                                'back-office' => 'Back office'
                            ]),

                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->confirmed()
                            ->revealable()
                            ->hiddenOn('edit')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->password()
                            ->revealable()
                            ->hiddenOn('edit')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('address')
                            ->required(),
                        Forms\Components\Toggle::make('can_login')
                            ->default(true)
                            ->required(),
                    ])
                    ->native(false)
                    ->searchable(['first_name', 'last_name'])
                    ->preload(),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->extraAttributes(['class' => 'text-center font-bold'])
                    ->searchable(),

                Tables\Columns\TextColumn::make('marketers_count')
                    ->label('Marketers')
                    ->alignCenter()
                    ->counts('marketers')
                    ->sortable(),
                Tables\Columns\TextColumn::make('hotels_count')
                    ->label('Hotels Onboarded')
                    ->alignCenter()
                    ->badge()
                    ->counts('hotels')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->button(),
                Tables\Actions\DeleteAction::make()
                    ->button(),
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
            'index' => Pages\ListTeams::route('/'),
            'create' => Pages\CreateTeam::route('/create'),
            'edit' => Pages\EditTeam::route('/{record}/edit'),
        ];
    }
}
