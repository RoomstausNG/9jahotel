<?php

namespace App\Filament\Hotel\Resources\HallResource\Pages;

use App\Filament\Hotel\Resources\HallResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageHalls extends ManageRecords
{
    protected static string $resource = HallResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->mutateFormDataUsing(function (array $data) {
                $data['hotel_id'] = auth()->user()->hotel_id;
                return $data;
            })->successNotificationTitle('Hall created successfully'),
        ];
    }
}