<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Filament\Resources\CouponResource\RelationManagers;
use App\Models\Coupon;
use App\Filament\Exports\CouponExporter;
use App\Jobs\SendCouponEmail;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(12),
                Forms\Components\TextInput::make('percent')
                    ->required()
                    ->maxValue(10)
                    ->numeric(),
                Forms\Components\Select::make('type')
                    ->required()
                    ->options([
                        'regular' => 'Regular',
                        'new_user' => 'New User',
                        'marketer' => 'Marketer',
                        'agent' => 'Agent',
                        'hotel' => 'Hotel',
                    ])
                    ->live(),
                Forms\Components\DatePicker::make('valid_from')
                    ->required(),
                Forms\Components\DatePicker::make('valid_till')
                    ->required(),
                Forms\Components\TextInput::make('limit_usage')
                    ->numeric()
                    ->default(-1)
                    ->helperText('Use -1 for unlimited usage.'),
                Forms\Components\TextInput::make('highest_value')
                    ->numeric()
                    ->required()
                    ->helperText('The maximum discount amount that can be applied, even if the percentage is higher.'),
                Forms\Components\Select::make('hotel_id')
                    ->relationship('hotel', 'name')
                    ->searchable()
                    ->native(false)
                    ->helperText('Is this coupon associated with a particular hotel?')
                    ->visible(fn(Forms\Get $get) => in_array($get('type'), ['hotel'])),
                Forms\Components\Select::make('owner_id')
                    ->relationship(
                        name: 'owner',
                        titleAttribute: 'firstname',
                        modifyQueryUsing: fn(Builder $query) => $query->whereHas('roles', fn(Builder $query) => $query->whereIn('name', ['agent', 'marketer']))
                    )
                    ->getOptionLabelFromRecordUsing(fn(User $record) => Str::title("{$record->firstname} {$record->lastname}"))
                    ->native(false)
                    ->searchable()
                    ->helperText('Does this coupon belong to a specific agent or marketer?')
                    ->visible(fn(Forms\Get $get) => in_array($get('type'), ['agent', 'marketer'])),
                Forms\Components\Toggle::make('status')
                    ->required()
                    ->default(true)
                    ->label('Active Status'),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->formatStateUsing(fn(string $state): string => strtoupper($state)),
                Tables\Columns\TextColumn::make('percent')
                    ->numeric()
                    ->sortable()
                    ->suffix('%'),
                Tables\Columns\TextColumn::make('type')
                    ->searchable()
                    ->formatStateUsing(fn(string $state): string => strtoupper($state)),
                Tables\Columns\TextColumn::make('valid_from')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('valid_till')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('status')
                    ->boolean(),
                Tables\Columns\TextColumn::make('usage_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->exporter(CouponExporter::class),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ActionGroup::make([
                        Tables\Actions\Action::make('send_to_all_customers')
                            ->label('Send to All Customers')
                            ->icon('heroicon-o-paper-airplane')
                            ->color('success')
                            ->action(function (Coupon $record, array $data) {
                                $customers = User::whereHas('roles', fn($query) => $query->where('name', 'customer'))->get();
                                $adminUserId = auth()->id();
                                $totalEmails = 0;

                                foreach ($customers as $customer) {
                                    SendCouponEmail::dispatch($record, $data['message'], $customer->email, $adminUserId);
                                    $totalEmails++;
                                }

                                Notification::make()
                                    ->title('Coupon Sent to All Customers')
                                    ->body("Started sending coupon {$record->code} to {$totalEmails} customers. You'll receive notifications for each email.")
                                    ->success()
                                    ->send();
                            })
                            ->form([
                                Forms\Components\Textarea::make('message')
                                    ->required()
                                    ->default('Enjoy a discount on your next purchase with this coupon!')
                                    ->rows(4),
                            ]),
                        Tables\Actions\Action::make('send_to_emails')
                            ->label('Send to Emails')
                            ->icon('heroicon-o-envelope')
                            ->color('info')
                            ->action(function (Coupon $record, array $data) {
                                $emails = array_map('trim', explode(',', $data['emails']));
                                $adminUserId = auth()->id();
                                $totalEmails = 0;

                                foreach ($emails as $email) {
                                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                        SendCouponEmail::dispatch($record, $data['message'], $email, $adminUserId);
                                        $totalEmails++;
                                    }
                                }

                                Notification::make()
                                    ->title('Coupon Sent to Email List')
                                    ->body("Started sending coupon {$record->code} to {$totalEmails} email addresses. You'll receive notifications for each email.")
                                    ->success()
                                    ->send();
                            })
                            ->form([
                                Forms\Components\Textarea::make('emails')
                                    ->required()
                                    ->helperText('Enter a comma-separated list of email addresses.')
                                    ->rows(3),
                                Forms\Components\Textarea::make('message')
                                    ->required()
                                    ->default('Enjoy a discount on your next purchase with this coupon!')
                                    ->rows(4),
                            ]),
                    ])
                    ->label('Send Email')
                    ->icon('heroicon-o-envelope')
                    ->color('primary'),
                    Tables\Actions\ActionGroup::make([
                        Tables\Actions\ExportAction::make()
                            ->exporter(CouponExporter::class),
                        Tables\Actions\Action::make('print')
                            ->label('Print')
                            ->icon('heroicon-o-printer')
                            ->color('gray')
                            ->url(fn (Coupon $record): string => route('coupon.print', $record))
                            ->openUrlInNewTab(),
                    ])
                    ->label('Export & Print')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('warning'),
                    Tables\Actions\DeleteAction::make(),
                ])
                ->label('Actions')
                ->icon('heroicon-o-ellipsis-vertical')
                ->size('sm')
                ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkActionGroup::make([
                        Tables\Actions\BulkAction::make('send_to_all_customers')
                            ->label('Send to all Customers')
                            ->icon('heroicon-o-paper-airplane')
                            ->action(function (Collection $records, array $data) {
                                $customers = User::whereHas('roles', fn($query) => $query->where('name', 'customer'))->get();
                                $adminUserId = auth()->id();
                                $totalEmails = 0;

                                foreach ($records as $coupon) {
                                    foreach ($customers as $customer) {
                                        SendCouponEmail::dispatch($coupon, $data['message'], $customer->email, $adminUserId);
                                        $totalEmails++;
                                    }
                                }

                                Notification::make()
                                    ->title('Coupons Batch Started')
                                    ->body("Started sending {$totalEmails} coupon emails to all customers. You'll receive notifications for each email.")
                                    ->success()
                                    ->send();
                            })
                            ->form([
                                Forms\Components\Textarea::make('message')
                                    ->required()
                                    ->default('Enjoy a discount on your next purchase with this coupon!')
                                    ->rows(4),
                            ]),
                        Tables\Actions\BulkAction::make('send_to_emails')
                            ->label('Send to Emails')
                            ->icon('heroicon-o-envelope')
                            ->action(function (Collection $records, array $data) {
                                $emails = array_map('trim', explode(',', $data['emails']));
                                $adminUserId = auth()->id();
                                $totalEmails = 0;

                                foreach ($records as $coupon) {
                                    foreach ($emails as $email) {
                                        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                            SendCouponEmail::dispatch($coupon, $data['message'], $email, $adminUserId);
                                            $totalEmails++;
                                        }
                                    }
                                }

                                Notification::make()
                                    ->title('Coupons Batch Started')
                                    ->body("Started sending {$totalEmails} coupon emails to specified addresses. You'll receive notifications for each email.")
                                    ->success()
                                    ->send();
                            })
                            ->form([
                                Forms\Components\Textarea::make('emails')
                                    ->required()
                                    ->helperText('Enter a comma-separated list of email addresses.')
                                    ->rows(3),
                                Forms\Components\Textarea::make('message')
                                    ->required()
                                    ->default('Enjoy a discount on your next purchase with this coupon!')
                                    ->rows(4),
                            ]),
                    ])
                    ->label('Send Emails')
                    ->icon('heroicon-o-envelope')
                    ->color('primary'),
                    Tables\Actions\BulkActionGroup::make([
                        Tables\Actions\ExportBulkAction::make()
                            ->exporter(CouponExporter::class),
                    ])
                    ->label('Export & Print')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('warning'),
                    Tables\Actions\DeleteBulkAction::make(),
                ])
                ->label('Bulk Actions')
                ->icon('heroicon-o-bars-3-bottom-left'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCoupons::route('/'),
        ];
    }
}
