<?php

namespace App\Filament\Hotel\Resources\VideoResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Storage;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Hotel\Resources\VideoResource;

class CreateVideo extends CreateRecord
{
    protected static string $resource = VideoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = Storage::disk('local')->mimeType($data['url']);
        $data['hotel_id'] = auth()->user()->hotel_id;

        return $data;
    }
}