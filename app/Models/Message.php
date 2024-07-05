<?php

namespace App\Models;

use Awcodes\Curator\Models\Media;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Http;

class Message extends Model
{
    use HasFactory;

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function send(): void
    {
        ['success' => $success, 'message' => $message] = Http::withoutVerifying()
            ->post(config('services.wsap.point'). '/messages/send?id='.$this->device->uuid, [
                'receiver' => $this->contact->number,
                'message' => $this->getMessageBody(),
            ])->json();

        $this->update([
            'response' => ['message' => $message],
            'status' => $success ? 'sent' : 'failed',
            $success ? 'delivered_at' : 'initiated_at' => now(),
        ]);
    }

    private function getMessageBody(): array
    {
        if (! $this->media_id) {
            return ['text' => $this->content];
        }

        $type = current(explode('/', $this->media->type));
        if (! in_array($type, ['image', 'audio', 'video'])) {
            $type = 'document';
        }

        $fileName = $this->media->title . '.' . $this->media->ext;

        return [
            $type => [
                'url' => './storage/app/public/'.$this->media->path,
            ],
            'mimetype' => $this->media->type,
            'caption' => $this->content,
            'fileName' => $fileName,
        ];
    }
}
