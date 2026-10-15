<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The platform's own short, factual emails (account confirmation, team
 * invites, plan invoices and reminders): plain paragraphs and at most one
 * link, in the same voice as ResetPasswordMail.
 */
class PlataformaMail extends Mailable
{
    use SerializesModels;

    /** @param list<string> $paragrafos */
    public function __construct(
        public string $assunto,
        public array $paragrafos,
        public ?string $url = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->assunto.' · Vencia');
    }

    public function content(): Content
    {
        $html = collect($this->paragrafos)->map(fn (string $p) => '<p>'.nl2br(e($p)).'</p>')->implode("\n");

        if ($this->url) {
            $html .= "\n".'<p><a href="'.e($this->url).'">'.e($this->url).'</a></p>';
        }

        return new Content(htmlString: $html."\n<p>— Vencia</p>");
    }
}
