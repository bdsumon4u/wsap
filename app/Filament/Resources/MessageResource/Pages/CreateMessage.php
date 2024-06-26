<?php

namespace App\Filament\Resources\MessageResource\Pages;

use App\Filament\Resources\MessageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMessage extends CreateRecord
{
    protected static string $resource = MessageResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! $data['device_id']) {
            $data['device_id'] = value(auth()->user())->devices()->inRandomorder()->value('id');
        }

        if (! $data['scheduled_at']) {
            $data['scheduled_at'] = now();
        }

        return $data;
    }
}
