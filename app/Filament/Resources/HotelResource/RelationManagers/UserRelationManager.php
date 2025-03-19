<?php

namespace App\Filament\Resources\HotelResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Filament\Resources\RelationManagers\RelationManager;

class UserRelationManager extends RelationManager
{
    protected static string $relationship = 'user';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('avatar')
                    ->image()
                    ->avatar()
                    ->directory('staff')
                    ->disk('cloudinary')->visibility('private')
                    // ->storeFileNamesIn('avatar')  // Store public_id in DB
                    // ->getUploadedFileNameForStorageUsing(function ($file) {
                    //     $uploadResponse = Cloudinary::upload($file->getRealPath(), [
                    //         'folder' => 'staff'
                    //     ]);
                    //     return $uploadResponse->getPublicId();  // Save only public_id
                    // })
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
                    ->required()
                    ->default('admin')
                    ->options(["admin" => "Admin", "manager" => "Manager", 'frontdesk' => "Frontdesk", 'frontdesk-manager' => "Frontdesk Manager", "sales-staff" => "Sales Staff", "sales-manager" => "Sales Manager"]),
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

                Forms\Components\Textarea::make('address')
                    ->required(),
                Forms\Components\Toggle::make('can_login')
                    ->default(true)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('username')
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->size(64)
                    ->state(fn(Model $record) => $record->avatar ? url(getCloudinaryUrl($record->avatar)) : url('img/avatar.png'))
                    ->disk('cloudinary')
                    // ->defaultImageUrl(url('img/avatar.png'))
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
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data) {
                        $data['password'] = Hash::make($data['password']);
                        $data['designation'] = 'admin';
                        return $data;
                    })
                    ->successNotificationTitle("Staff created successfully"),
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
}
