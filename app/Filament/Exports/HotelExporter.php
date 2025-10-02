<?php

namespace App\Filament\Exports;

use App\Models\Hotel;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class HotelExporter extends Exporter
{
    protected static ?string $model = Hotel::class;

    public static function modifyQuery(Builder $query): Builder
    {
        return $query->with(["city"]);
    }

    public static function getColumns(): array
    {
        return [
            ExportColumn::make("name")->label("Name"),
            ExportColumn::make("address")->label("Address"),
            ExportColumn::make("city_name_with_state")
                ->label("City and State")
                ->state(function (Hotel $record): string {
                    $city = $record->city;
                    if ($city) {
                        $state = DB::table("states")
                            ->where("id", $city->state_id)
                            ->first();
                        $stateName = $state ? $state->name : "";
                        return $stateName
                            ? "{$city->name}, {$stateName}"
                            : $city->name;
                    }
                    return "";
                }),
            ExportColumn::make("email")->label("Email Address"),
            ExportColumn::make("telephone")->label("Phone"),
            ExportColumn::make("link")
                ->label("Link")
                ->state(function (Hotel $record): string {
                    return "https://9jahotel.com/hotel/" . $record->code;
                }),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body =
            "Your hotel export has completed and " .
            number_format($export->successful_rows) .
            " " .
            str("row")->plural($export->successful_rows) .
            " exported.";

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .=
                " " .
                number_format($failedRowsCount) .
                " " .
                str("row")->plural($failedRowsCount) .
                " failed to export.";
        }

        return $body;
    }
}
