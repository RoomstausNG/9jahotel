<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Hotel;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\RelationManagers;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Staff';
    protected static ?string $modelLabel = 'Staff';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                // Forms\Components\TextInput::make('nok_name')
                //     ->maxLength(255),
                // Forms\Components\TextInput::make('nok_avatar')
                //     ->maxLength(255),
                // Forms\Components\TextInput::make('nok_phone')
                //     ->tel()
                //     ->maxLength(255),
                // Forms\Components\TextInput::make('nok_email')
                //     ->email()
                //     ->maxLength(255),
                // Forms\Components\DatePicker::make('date_of_birth'),

                Forms\Components\DatePicker::make('date_of_employment')
                    ->before(now())
                    ->required(),

                Forms\Components\Select::make('current_role')
                    ->required(fn(string $operation) => $operation === 'create')
                    ->default('back-office')
                    ->options(auth()->user()->hasRole('super-admin') ? [
                        'admin' => 'Admin',
                        'back-office' => 'Back Office',
                        'marketer' => 'Marketer',
                    ] : [
                        'back-office' => 'Back Office',
                        'marketer' => 'Marketer'
                    ]),
                // Forms\Components\TextInput::make('gender')
                //     ->maxLength(255),
                //     ->columnSpanFull(),
                // Forms\Components\TextInput::make('account_number')
                //     ->maxLength(255),
                // Forms\Components\TextInput::make('bank_name')
                //     ->maxLength(255),
                // Forms\Components\TextInput::make('account_name')
                //     ->maxLength(255),
                // Forms\Components\TextInput::make('signature')
                //     ->maxLength(255),
                // Forms\Components\TextInput::make('gross_annual_salary')
                //     ->numeric(),
                // Forms\Components\TextInput::make('payee')
                //     ->numeric(),
                // Forms\Components\TextInput::make('surcharges')
                //     ->numeric(),
                // Forms\Components\TextInput::make('special_bonus')
                //     ->numeric(),
                // Forms\Components\TextInput::make('health_insurance')
                //     ->numeric(),
                // Forms\Components\TextInput::make('transport_insurance')
                //     ->numeric(),
                // Forms\Components\TextInput::make('housing_insurance')
                //     ->numeric(),
                // Forms\Components\TextInput::make('overtime_insurance')
                //     ->numeric(),
                // Forms\Components\TextInput::make('special_duty_insurance')
                //     ->numeric(),
                // Forms\Components\TextInput::make('other_deductions')
                //     ->numeric(),
                // Forms\Components\TextInput::make('country_id')
                //     ->numeric(),
                // Forms\Components\TextInput::make('state_id')
                //     ->numeric(),
                // Forms\Components\TextInput::make('city_id')
                //     ->numeric(),


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

                Forms\Components\Select::make("hotels")
                    ->relationship('hotels', 'name')
                    ->multiple()
                    // ->required()
                    ->options(Hotel::all()->pluck('name', 'id'))
                    ->native(false)
                    ->preload()
                    ->searchable()
                    ->searchDebounce(500)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('address')
                    ->required(),
                Forms\Components\Toggle::make('can_login')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->role(['marketer',  'back-office']))
            // ->query(fn(Builder $query) => $query->where('current_role',  'back-office'))
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    // ->disk('cloudinary')->visibility('private')
                    ->state(fn(Model $record) => $record->avatar ? url(getCloudinaryUrl($record->avatar)) : url('img/avatar.png'))
                    ->size(64)
                    ->disk('cloudinary')
                    ->circular(),
                Tables\Columns\TextColumn::make('firstname')
                    ->extraAttributes(['class' => 'capitalize'])
                    ->searchable(),

                Tables\Columns\TextColumn::make('lastname')
                    ->extraAttributes(['class' => 'capitalize'])
                    ->searchable(),
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('nok_name')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('nok_avatar')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('nok_phone')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('nok_email')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('date_of_birth')
                //     ->date()
                //     ->sortable(),

                Tables\Columns\TextColumn::make('date_of_employment')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('can_login')
                    ->boolean()
                    ->alignCenter(),

                // Tables\Columns\TextColumn::make('current_role')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('designation')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('gender')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('account_number')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('bank_name')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('account_name')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('signature')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('gross_annual_salary')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('payee')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('surcharges')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('special_bonus')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('health_insurance')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('transport_insurance')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('housing_insurance')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('overtime_insurance')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('special_duty_insurance')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('other_deductions')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('city_id')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('state_id')
                //     ->numeric()
                //     ->sortable(),

                // Tables\Columns\TextColumn::make('hotels.name')
                //     ->separator(",")
                //     ->searchable()
                //     ->badge(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),


            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->successNotificationTitle('User Updated'),
                Tables\Actions\DeleteAction::make()
                    ->successNotificationTitle('User Deleted'),
                Action::make('activities')->url(fn($record) => UserResource::getUrl('activities', ['record' => $record]))->visible(fn() => auth()->user()->hasRole(['admin', 'super-admin']))->label('Staff Activities'),
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
            'index' => Pages\ManageUsers::route('/'),
            'activities' => Pages\ListUserActivities::route('/{record}/activities'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole('super-admin', 'admin') || auth()->user()?->current_role === 'admin';
    }
}