<?php

namespace App\Filament\Hotel\Resources\AmenityResource\Pages;

use App\Filament\Hotel\Resources\AmenityResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAmenities extends ManageRecords
{
    protected static string $resource = AmenityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data) {
                    $data['hotel_id'] = myHotel()->id;
                    return $data;
                })
                ->successNotificationTitle('Amenity updated successfully'),
        ];
    }
}