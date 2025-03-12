<?php

namespace App\Filament\Resources\GeneralItemResource\Pages;

use App\Filament\Resources\GeneralItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageGeneralItems extends ManageRecords
{
    protected static string $resource = GeneralItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data) {
                    $randomString = substr(str_shuffle($data['name']), 0, 3);
                    $data['code'] = strtoupper($randomString . rand(001, 999));
                    return $data;
                }),
        ];
    }
}