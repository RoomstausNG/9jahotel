<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentMethodResource\Pages;
use App\Models\PaymentMethod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('code')->required()->unique(),
                Forms\Components\Textarea::make('description'),
                Forms\Components\Toggle::make('is_active')->label('Active'),
                Forms\Components\KeyValue::make('config')->label('Config')->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('code'),
                Tables\Columns\TextColumn::make('description')->limit(30),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->actions([
                Action::make('enable')
                    ->label('Enable')
                    ->icon('heroicon-o-check')
                    ->visible(fn($record) => !$record->is_active)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['is_active' => true]);

                        \Filament\Notifications\Notification::make()
                            ->title('Payment method enabled successfully.')
                            ->success()
                            ->send();
                    }),
                Action::make('disable')
                    ->label('Disable')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn($record) => $record->is_active)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['is_active' => false]);

                        \Filament\Notifications\Notification::make()
                            ->title('Payment method disabled successfully.')
                            ->success()
                            ->send();
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Payment Method')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Create Payment Method')
                    ->successNotificationTitle('Payment method created successfully!'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePaymentMethods::route('/'),
        ];
    }
}