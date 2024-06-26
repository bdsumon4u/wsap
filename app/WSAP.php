<?php

namespace App;

use App\Models\Contact;
use App\Models\Device;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\HtmlString;

class WSAP
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public static function qr(Device $device)
    {
        if ($device->isConnected()) {
            return new HtmlString('
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                </svg>
            ');
        }

        $alt = Http::get(implode('/', [
            config('services.wsap.host'),
            'session/qr',
            $device->uuid,
            'image'
        ]))->json('message', 'QR Code');

        return new HtmlString('
            <div style="background-color: black; padding: 0.5rem;">
                <img wire:poll.3s src="' . $device->qrCodeImageSrc . '" alt="' . $alt . '" />
            </div>
        ');
    }

    public static function contacts(Device $device)
    {
        return Http::get(implode('/', [
            config('services.wsap.host'),
            'client/getContacts',
            $device->uuid,
        ]))->collect('contacts')->each(function ($contact) use ($device) {
            if (! isset($contact['name'])) {
                return;
            }

            Contact::updateOrCreate([
                'user_id' => $device->user_id,
                'number' => $contact['number'],
            ], [
                'name' => $contact['name'],
                'registered' => true,
            ]);
        });
    }

    public static function isRegistered(string $number): bool
    {
        $device = value(Filament::auth()->user())->devices()->inRandomOrder()->first();

        return Http::post(implode('/', [
            config('services.wsap.host'),
            'client/isRegisteredUser',
            $device->uuid,
        ]), compact('number'))->json('result');
    }
}
