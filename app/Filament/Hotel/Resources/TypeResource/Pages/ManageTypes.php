<?php

namespace App\Filament\Hotel\Resources\TypeResource\Pages;

use App\Filament\Hotel\Resources\TypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTypes extends ManageRecords
{
    protected static string $resource = TypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->successNotificationTitle('Room category created successfully'),
        ];
    }
}