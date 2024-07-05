<?php

use App\Models\Message;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::call(function () {
    Message::where('scheduled_at', '<=', now())
        ->where('status', '!=', 'sent')
        ->get()->each->send();
})->everyMinute();