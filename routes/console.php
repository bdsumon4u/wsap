<?php

use App\Enums\MessageStatus;
use App\Jobs\SendMessage;
use App\Models\Message;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('queue:work --stop-when-empty')->everyMinute();

Schedule::call(function () {
    Message::query()
        ->where('status', MessageStatus::SCHEDULED)
        ->where('scheduled_at', '<=', now())
        ->update(['status' => MessageStatus::PENDING]);
    
    Message::query()
        ->where('status', MessageStatus::PENDING)
        ->get()->each(fn (Message $message) => SendMessage::dispatch($message));
})->everyMinute();
