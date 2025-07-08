<?php

namespace App\Filament\Exports;

use App\Models\Coupon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class CouponExporter extends Exporter
{
    protected static ?string $model = Coupon::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('code')
                ->label('Coupon Code')
                ->formatStateUsing(fn (string $state): string => strtoupper($state)),
            ExportColumn::make('percent')
                ->label('Discount (%)')
                ->suffix('%'),
            ExportColumn::make('type')
                ->label('Type')
                ->formatStateUsing(fn (string $state): string => strtoupper($state)),
            ExportColumn::make('valid_from')
                ->label('Valid From')
                ->formatStateUsing(fn ($state): string => $state ? $state->format('M d, Y') : ''),
            ExportColumn::make('valid_till')
                ->label('Valid Until')
                ->formatStateUsing(fn ($state): string => $state ? $state->format('M d, Y') : ''),
            ExportColumn::make('limit_usage')
                ->label('Usage Limit')
                ->formatStateUsing(fn ($state): string => $state == -1 ? 'Unlimited' : (string) $state),
            ExportColumn::make('highest_value')
                ->label('Maximum Discount Amount')
                ->formatStateUsing(fn ($state): string => $state ? '₦' . number_format($state, 2) : 'No limit'),
            ExportColumn::make('owner.name')
                ->label('Owner')
                ->enabledByDefault(false),
            ExportColumn::make('hotel.name')
                ->label('Hotel')
                ->enabledByDefault(false),
            ExportColumn::make('status')
                ->label('Status')
                ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive'),
            ExportColumn::make('usage_count')
                ->label('Times Used'),
            ExportColumn::make('created_at')
                ->label('Created At')
                ->formatStateUsing(fn ($state): string => $state ? $state->format('M d, Y H:i') : '')
                ->enabledByDefault(false),
            ExportColumn::make('updated_at')
                ->label('Updated At')
                ->formatStateUsing(fn ($state): string => $state ? $state->format('M d, Y H:i') : '')
                ->enabledByDefault(false),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your coupon export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
