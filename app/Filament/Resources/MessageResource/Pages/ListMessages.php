<?php

namespace App\Filament\Resources\MessageResource\Pages;

use App\Filament\Resources\MessageResource;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;

class ListMessages extends ListRecords
{
    protected static string $resource = MessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->slideOver()
                ->modalWidth('md')
                ->modalHeading('Send Message')
                ->modalSubmitActionLabel('Send/Schedule')
                ->createAnother(false)
                ->mutateFormDataUsing(function (array $data) {
                    if (! $data['device_id']) {
                        $data['device_id'] = value(Filament::auth()->user())
                            ->devices()->where('status', 'connected')
                            ->inRandomOrder()->value('id');
                    }

                    $data['scheduled_at'] ??= now();

                    return $data;
                }),
        ];
    }
}
