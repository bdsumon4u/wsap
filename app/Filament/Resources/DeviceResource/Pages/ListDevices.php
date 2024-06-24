<?php

namespace App\Filament\Resources\DeviceResource\Pages;

use App\Filament\Resources\DeviceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Str;

class ListDevices extends ListRecords
{
    protected static string $resource = DeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalWidth('md')
                ->form(fn ($form) => static::$resource::form($form))
                ->mutateFormDataUsing(function (array $data) {
                    return $data + ['uuid' => Str::uuid()];
                })
                ->after(fn ($record) => $record->init())
                ->modalHeading('Add Device')
                ->slideOver(),
        ];
    }
}
