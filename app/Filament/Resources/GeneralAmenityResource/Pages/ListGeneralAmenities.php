<?php

namespace App\Filament\Resources\GeneralAmenityResource\Pages;

use App\Filament\Resources\GeneralAmenityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGeneralAmenities extends ListRecords
{
    protected static string $resource = GeneralAmenityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
