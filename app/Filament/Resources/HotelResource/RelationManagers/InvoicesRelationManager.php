<?php

namespace App\Filament\Resources\HotelResource\RelationManagers;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Forms\Components\Datepicker;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Cloudinary\Transformation\Argument\Text\FontWeight;
use Filament\Resources\RelationManagers\RelationManager;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('subscription')
                    ->default('annually')
                    ->options([
                        'monthly' => 'Monthly',
                        'annually' => 'Annually',
                    ])->live()
                    ->afterStateUpdated(function (Set $set, Get $get) {
                        $set('amount', match ($get('subscription')) {
                            'monthly' => 15000,
                            'annually' => 99995,
                        });
                        // $set('to', match ($get('subscription')) {
                        //     'monthly' => Carbon::parse($get('from'))->addMonth(),
                        //     'annually' => Carbon::parse($get('from'))->addYear(),
                        // });
                    }),
                Forms\Components\TextInput::make('amount')
                    ->prefix('NGN')
                    ->required()
                    ->default(99995)
                    ->readonly()
                    ->live(),
                Forms\Components\Datepicker::make('from')
                    ->label('Valid From')
                    ->required()
                    ->default(now()),
                Forms\Components\Datepicker::make('to')
                    ->label('Valid To')
                    ->required()
                    ->live()
                    ->default(function (Get $get) {
                        return match ($get('subscription')) {
                            'monthly' => Carbon::parse($get('from'))->addMonth(),
                            'annually' => Carbon::parse($get('from'))->addYear(),
                        };
                    })
                    ->dehydrated(false),
                Forms\Components\Textarea::make('description')
                    ->default('Invoice for renewal of your subscription')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->orderByDesc('id'))
            ->recordTitleAttribute('reference')
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('subscription')
                    ->extraAttributes(['class' => 'uppercase']),
                Tables\Columns\TextColumn::make('amount')
                    ->money('NGN'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->weight(FontWeight::MEDIUM)
                    ->extraAttributes(['class' => 'uppercase upper'])
                    ->color(fn($state) => match ($state) {
                        'paid' => 'success',
                        'unpaid' => 'warning'
                    }),

                Tables\Columns\TextColumn::make('from')
                    ->label('Started On')
                    ->date(),
                Tables\Columns\TextColumn::make('to')
                    ->label('Expired On')
                    ->date(),

                Tables\Columns\TextColumn::make('date_paid')
                    ->label('Date Paid')
                    ->sortable()
                    ->date(),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->successNotificationTitle("Invoice Created successfully"),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->hidden(fn(Model $state) => $state->status == 'paid')
                    ->successNotificationTitle("Invoice updated successfully"),
                Tables\Actions\DeleteAction::make()->hidden(fn(Model $state) => $state->status == 'paid')
                    ->successNotificationTitle("Invoice deleted successfully"),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}