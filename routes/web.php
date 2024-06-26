<?php

use App\Enums\MessageStatus;
use App\Models\Message;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    dd(Message::query()
        ->where('status', MessageStatus::SCHEDULED)
        ->where('scheduled_at', '<=', now())
        ->update(['status' => MessageStatus::PENDING]));
    return view('welcome');
});
