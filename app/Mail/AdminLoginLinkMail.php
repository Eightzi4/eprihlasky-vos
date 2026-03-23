<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class AdminLoginLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $url;
    public Carbon $expiresAt;

    public function __construct(string $url, Carbon $expiresAt)
    {
        $this->url       = $url;
        $this->expiresAt = $expiresAt;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Přihlášení do administrace E-přihláška');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.login_link');
    }
}
