<?php

namespace App\Models;

use App\Enums\DeviceStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class Device extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => DeviceStatus::class,
        ];
    }

    public function init()
    {
        return Http::get(implode('/', [
            config('services.wsap.host'),
            'session/start',
            $this->uuid,
        ]))->throw();
    }

    public function qrCodeImageSrc(): Attribute
    {
        return Attribute::get(fn () => implode('/', [
            config('services.wsap.point'),
            'session/qr',
            $this->uuid,
            'image'
        ]));
    }

    private function state(): ?string
    {
        return Http::get(implode('/', [
            'http://wsap:3000',
            'session/status',
            $this->uuid,
        ]))->json('state');
    }

    public function isConnected(): bool
    {
        if ($this->state() == 'CONNECTED') {
            if ($this->status != DeviceStatus::CONNECTED) {
                $this->update(['status' => DeviceStatus::CONNECTED]);
            }

            return true;
        }

        if ($this->status != DeviceStatus::INTERRUPT) {
            $this->update(['status' => DeviceStatus::INTERRUPT]);
        }

        return false;
    }
}
