<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NoticeMail extends Mailable
{
    public function __construct(
        public string $noticeTitle,
        public string $noticeBody,
        public ?string $noticeLink,
        public string $toName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'MushFungAI: '.$this->noticeTitle);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.notice');
    }
}
