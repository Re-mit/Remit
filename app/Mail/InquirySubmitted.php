<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class InquirySubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $title,
        public string $content,
        public Carbon $submittedAt,
        public ?string $appName = null,
        public ?string $appUrl = null,
        public ?string $env = null,
    ) {}

    public function envelope(): Envelope
    {
        $subjectApp = $this->appName ?: 'Remit';
        $subjectTitle = mb_strimwidth($this->title, 0, 80, '…', 'UTF-8');

        return new Envelope(
            subject: "[문의] {$subjectTitle} - {$subjectApp}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.inquiry-submitted',
        );
    }
}







