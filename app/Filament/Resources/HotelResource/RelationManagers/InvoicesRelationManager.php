<?php

namespace App\Filament\Resources\HotelResource\RelationManagers;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Package;
use App\Models\Invoice;
use App\Models\Hotel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Forms\Components\Datepicker;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Cloudinary\Transformation\Argument\Text\FontWeight;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Notifications\Notification;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('subscription')
                    ->label('Package')
                    ->options(function () {
                        return Package::all()->pluck('name', 'name')->toArray();
                    })
                    ->default(function () {
                        return Package::first()?->name ?? 'annually';
                    })
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get) {
                        $package = Package::where('name', $get('subscription'))->first();
                        if ($package) {
                            $set('amount', $package->amount);
                        }
                    }),
                Forms\Components\TextInput::make('amount')
                    ->prefix('NGN')
                    ->required()
                    ->default(function () {
                        return Package::first()?->amount ?? 99995;
                    })
                    ->extraAttributes(['class' => 'cursor-not-allowed bg-gray-300'])
                    ->readonly()
                    ->live(),
                Forms\Components\DatePicker::make('from')
                    ->label('Valid From')
                    ->required()
                    ->default(today()),
                Forms\Components\DatePicker::make('to')
                    ->label('Valid To')
                    ->required()
                    ->live()
                    ->default(function (Get $get) {
                        $subscription = $get('subscription');
                        $from = $get('from');

                        if (!$from) {
                            $from = today();
                        }

                        return match ($subscription) {
                            'monthly' => Carbon::parse($from)->addMonth(),
                            'annually' => Carbon::parse($from)->addYear(),
                            default => Carbon::parse($from)->addYear(),
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
                    ->successNotificationTitle("Invoice Created successfully")
                    ->after(function (Model $record) {
                        $hotel = $this->getOwnerRecord();

                        // Queue the email notification
                        Mail::to($hotel->email)
                            ->queue(new \App\Mail\InvoiceCreated($record, $hotel));

                        // Also send a notification in the UI
                        Notification::make()
                            ->title('Invoice Email Sent')
                            ->body("An email notification has been sent to {$hotel->email}")
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->hidden(fn(Model $state) => $state->status == 'paid')
                    ->successNotificationTitle("Invoice updated successfully"),
                Tables\Actions\DeleteAction::make()->hidden(fn(Model $state) => $state->status == 'paid')
                    ->successNotificationTitle("Invoice deleted successfully"),
                Tables\Actions\Action::make('sendReminder')
                    ->label('Send Reminder')
                    ->icon('heroicon-o-envelope')
                    ->color('warning')
                    ->hidden(fn(Model $record) => $record->status == 'paid')
                    ->action(function (Model $record) {
                        $hotel = $this->getOwnerRecord();

                        // Queue the reminder email
                        Mail::to($hotel->email)
                            ->queue(new \App\Mail\InvoicePaymentReminder($record, $hotel));

                        Notification::make()
                            ->title('Payment Reminder Sent')
                            ->body("A payment reminder has been sent to {$hotel->email}")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
