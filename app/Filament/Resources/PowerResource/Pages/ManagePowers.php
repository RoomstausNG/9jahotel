<?php

namespace App\Filament\Resources\PowerResource\Pages;

use App\Filament\Resources\PowerResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePowers extends ManageRecords
{
    protected static string $resource = PowerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
