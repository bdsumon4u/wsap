<?php

namespace App\Filament\Resources\ContactResource\Pages;

use App\Filament\Resources\ContactResource;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Http;

class ListContacts extends ListRecords
{
    protected static string $resource = ContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->slideOver()
                ->modalWidth('md')
                ->modalHeading('Add Contact')
                ->mutateFormDataUsing(function (array $data) {
                    $uuid = value(Filament::auth()->user())
                        ->devices()->where('status', 'connected')
                        ->inRandomOrder()->value('uuid');

                    return $data + [
                        'user_id' => Filament::auth()->id(),
                        'exists' => Http::post(config('services.wsap.point') . '/check/' . $uuid, [
                            'phone' => $data['number'],
                        ])->json('success'),
                    ];
                }),
        ];
    }
}
