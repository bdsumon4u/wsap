<?php

namespace App\Filament\Resources\DeviceResource\Pages;

use App\Filament\Resources\DeviceResource;
use Filament\Actions;
use Filament\Facades\Filament;
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
                    return $data + [
                        'uuid' => Str::uuid(),
                        'user_id' => Filament::auth()->id(),
                    ];
                })
                ->after(fn ($record) => null)
                ->createAnother(false)
                ->modalSubmitActionLabel('Submit')
                ->modalHeading('Add Device')
                ->slideOver(),
        ];
    }
}
