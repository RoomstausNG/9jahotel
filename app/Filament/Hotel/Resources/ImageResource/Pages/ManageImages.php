<?php

namespace App\Filament\Hotel\Resources\ImageResource\Pages;

use App\Filament\Hotel\Resources\ImageResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageImages extends ManageRecords
{
    protected static string $resource = ImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['imageable_id'] = auth()->user()->hotel_id;
                    $data['imageable_type'] = "App\Models\Hotel";
                    $data['hotel_id'] = auth()->user()->hotel_id;
                    return $data;
                })->successNotificationTitle("Image uploaded successfully"),
        ];
    }
}
