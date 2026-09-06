<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Password-reset link email for the single admin account. A short, factual
 * transactional message — no urgency language — consistent with the other
 * Mailables in this app (LembreteVencimentoMail, ServicoTerminadoMail).
 *
 * Sent from App\Models\User::sendPasswordResetNotification(), which
 * overrides Laravel's default (English-language) ResetPassword notification
 * so the wording stays pt-PT and matches this app's voice.
 */
class ResetPasswordMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public string $url,
        public int $expiraEmMinutos,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reposição da password · Vencia',
        );
    }

    public function content(): Content
    {
        $corpo = "Foi pedida a reposição da password da sua conta Vencia.\n\n"
            ."Para definir uma nova password, aceda a esta ligação:\n{$this->url}\n\n"
            ."Esta ligação expira dentro de {$this->expiraEmMinutos} minutos.\n\n"
            .'Se não foi você a pedir esta alteração, pode ignorar este email — a password actual mantém-se válida.';

        return new Content(htmlString: nl2br(e($corpo)));
    }
}
