<?php

namespace App\Jobs;

use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Message $message,
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! $this->message->device->isConnected()) {
            $this->message->update(['status' => MessageStatus::FAILED]);

            return;
        }

        $this->message->update(['status' => MessageStatus::INITIATED, 'initiated_at' => now()]);

        $content = match ($this->message->type) {
            MessageType::Poll => $this->getPollContent(),
            MessageType::Contact => $this->getContactContent(),
            MessageType::List => $this->getListContent(),
            MessageType::Buttons => $this->getButtonsContent(),
            MessageType::Location => $this->getLocationContent(),
            MessageType::MediaMessage => $this->getMediaContent(),
            MessageType::MediaFromURL => $this->message->content,
            MessageType::PlainText => $this->message->content,
            default => $this->message->content,
        };

        $data = Http::post(implode('/', [
            config('services.wsap.host'),
            'client/sendMessage',
            $this->message->device->uuid,
        ]), [
            'chatId' => $this->getChatID(),
            'contentType' => $this->message->type,
            'content' => $content,
        ])->json();

        info('log', [
            'chatId' => $this->getChatID(),
            'contentType' => $this->message->type,
            'content' => $content,
        ]);

        if ($data['success']) {
            $this->message->update(['status' => MessageStatus::DELIVERED, 'delivered_at' => now()]);
        } else {
            info('failed', $data);
            $this->message->update(['status' => MessageStatus::FAILED]);
        }
    }

    private function getChatID(): string
    {
        return Http::post(implode('/', [
            config('services.wsap.host'),
            'client/getNumberId',
            $this->message->device->uuid,
        ]), [
            'number' => $this->message->contact->number,
        ])->json('result._serialized');
    }

    private function getMediaContent(): array
    {
        return [
            'mimetype' => 'image/jpeg',
            'data' => base64_encode(file_get_contents($this->message->content)),
            'filename' => 'image.jpg',
        ];
    }

    private function getLocationContent(): array
    {
        return [
            'latitude' => null,
            'longitude' => null,
            'description' => null,
        ];
    }

    private function getButtonsContent(): array
    {
        return [
            'body' => '',
            'buttons' => [
                [
                    'body' => '',
                ]
            ],
            'title' => '',
            'footer' => '',
        ];
    }

    private function getListContent(): array
    {
        return [
            'body' => '',
            'buttonText' => '',
            'sections' => [
                [
                    'title' => '',
                    'rows' => [
                        [
                            'title' => '',
                            'description' => '',
                            'id' => '',
                        ],
                    ],
                ],
            ],
            'title' => '',
            'footer' => '',
        ];
    }

    private function getContactContent(): array
    {
        return [
            'contactId' => '',
        ];
    }

    private function getPollContent(): array
    {
        return [
            'pollName' => '',
            'pollOptions' => [
                'Cat',
                'Dog',
            ],
            'options' => [
                'AllowMultipleAnswers' => true,
            ],
        ];
    }
}
