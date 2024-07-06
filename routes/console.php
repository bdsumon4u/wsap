<?php

use App\Models\Message;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::call(function () {
    $xannat = '8801778085554';

    Message::query()
        ->where('scheduled_at', '<=', now())
        ->where('status', '!=', 'sent')
        ->whereDoesntHave('contact', function ($query) use ($xannat) {
            $query->where('number', '=', $xannat);
        })
        ->get()->each->send();
    
    Message::query()
        ->whereHas('contact', function ($query) use ($xannat) {
            $query->where('number', '=', $xannat);
        })
        ->inRandomOrder()
        ->first()?->send();
})->hourly();