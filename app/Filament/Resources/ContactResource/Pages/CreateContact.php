<?php

namespace App\Filament\Resources\ContactResource\Pages;

use App\Filament\Resources\ContactResource;
use App\WSAP;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateContact extends CreateRecord
{
    protected static string $resource = ContactResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data + [
            'user_id' => value(Filament::auth())->id(),
            'registered' => WSAP::isRegistered($data['number']),
        ];
    }
}
