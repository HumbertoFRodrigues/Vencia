<?php

namespace App\Mail;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;
use Throwable;

/**
 * Sends through Brevo's HTTP API (https://www.brevo.com): a professional
 * sending service with its own reputation, so emails land in the inbox, and
 * it goes over HTTPS, which shared hosting never blocks (unlike SMTP ports).
 * Chosen in Admin → Plataforma → Envio de email.
 */
class TransporteBrevo extends AbstractTransport
{
    public const URL = 'https://api.brevo.com/v3/smtp/email';

    public function __construct(private string $chave)
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $endereco = fn (Address $a) => array_filter(['email' => $a->getAddress(), 'name' => $a->getName() ?: null]);

        $remetente = $email->getFrom()[0] ?? $message->getEnvelope()->getSender();
        $corpo = array_filter([
            'sender' => $endereco($remetente),
            'to' => array_map($endereco, $email->getTo()),
            'cc' => array_map($endereco, $email->getCc()) ?: null,
            'bcc' => array_map($endereco, $email->getBcc()) ?: null,
            'replyTo' => ($r = $email->getReplyTo()[0] ?? null) ? $endereco($r) : null,
            'subject' => (string) $email->getSubject(),
            'htmlContent' => $email->getHtmlBody() ?: null,
            'textContent' => $email->getTextBody() ?: null,
            'attachment' => array_map(fn ($p) => [
                'name' => $p->getFilename() ?: 'anexo',
                'content' => base64_encode($p->getBody()),
            ], $email->getAttachments()) ?: null,
        ]);

        // Brevo refuses a message with neither body: a text-only email still needs one of them.
        if (! isset($corpo['htmlContent']) && ! isset($corpo['textContent'])) {
            $corpo['textContent'] = ' ';
        }

        try {
            $resposta = Http::withHeaders(['api-key' => $this->chave, 'accept' => 'application/json'])
                ->timeout(20)
                ->post(self::URL, $corpo);
        } catch (Throwable $e) {
            throw new TransportException('Sem ligação ao Brevo: '.$e->getMessage(), 0, $e);
        }

        if (! $resposta->successful()) {
            throw new TransportException(self::mensagemErro($resposta->status(), (string) ($resposta->json('message') ?? $resposta->body())));
        }

        if ($id = $resposta->json('messageId')) {
            $message->setMessageId(trim((string) $id, '<>'));
        }
    }

    /** What went wrong, in words the admin can act on. */
    public static function mensagemErro(int $estado, string $detalhe): string
    {
        return match (true) {
            $estado === 401 => 'A chave API do Brevo não é válida. Cola-a de novo em Admin → Plataforma.',
            str_contains(strtolower($detalhe), 'sender') => 'O Brevo não aceita este endereço de envio: confirma o remetente (ou o domínio) em Brevo → Senders & Domains. ('.$detalhe.')',
            $estado === 402 || $estado === 403 => 'O Brevo recusou o envio (conta por activar ou limite diário atingido). ('.$detalhe.')',
            default => 'O Brevo recusou o envio ('.$estado.'): '.$detalhe,
        };
    }

    /** Checks a key without sending anything. @return array{ok: bool, mensagem: string} */
    public static function verificarChave(string $chave): array
    {
        try {
            $r = Http::withHeaders(['api-key' => $chave, 'accept' => 'application/json'])->timeout(15)->get('https://api.brevo.com/v3/account');
        } catch (Throwable $e) {
            return ['ok' => false, 'mensagem' => 'Sem ligação ao Brevo: '.$e->getMessage()];
        }

        if ($r->status() === 401) {
            return ['ok' => false, 'mensagem' => 'Chave inválida. Em Brevo: o teu nome → SMTP & API → API Keys → Generate.'];
        }
        if (! $r->successful()) {
            return ['ok' => false, 'mensagem' => 'O Brevo respondeu '.$r->status().'.'];
        }

        return ['ok' => true, 'mensagem' => 'Chave válida (conta '.($r->json('email') ?? '').').'];
    }

    public function __toString(): string
    {
        return 'brevo+api://default';
    }
}
