<?php

namespace App\Filament\Resources\HotelResource\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\HotelResource;
use Illuminate\Support\Facades\Cache;

class EditHotel extends EditRecord
{
    protected static string $resource = HotelResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\DeleteAction::make(),
    //     ];
    // }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Hotel updated Successfully');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // protected function afterSave(): void
    // {
    //     parent::afterSave();
    //     Cache::forget('hotel_resource_' . $this->record->id);
    //     Cache::forget('filament_hotels_ids'); // Invalidate hotel list cache
    // }

    // protected function afterDelete(): void
    // {
    //     parent::afterDelete();
    //     Cache::forget('hotel_resource_' . $this->record->id);
    //     Cache::forget('filament_hotels_ids'); // Invalidate hotel list cache
    // }
}