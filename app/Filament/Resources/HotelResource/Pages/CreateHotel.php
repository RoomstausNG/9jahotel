<?php

namespace App\Filament\Resources\HotelResource\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use App\Filament\Resources\HotelResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Cache;

class CreateHotel extends CreateRecord
{
    protected static string $resource = HotelResource::class;
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Hotel created Successfully');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        parent::afterCreate();
        Cache::forget('hotel_resource_' . $this->record->id);
    }

    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     $data['short'] = strtoupper($data['short']);

    //     return $data;
    // }
}
