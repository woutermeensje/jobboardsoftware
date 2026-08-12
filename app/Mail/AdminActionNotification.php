<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminActionNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $details
     * @param  array<string, mixed>|null  $actor
     */
    public function __construct(
        public string $title,
        public array $details = [],
        public ?array $actor = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[JobBoardSoftware] '.$this->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-action-notification',
        );
    }

    /**
     * @return array<int, mixed>
     */
    public function attachments(): array
    {
        return [];
    }
}
