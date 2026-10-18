<?php

namespace App\Mail;

use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;

/**
 * Sends through PHP's own mail(), i.e. the hosting's mail server (exim on
 * cPanel), so emails go out with no SMTP account to set up. Chosen in Admin →
 * Plataforma → Servidor de email → "Função mail() do PHP".
 */
class TransportePhpMail extends AbstractTransport
{
    protected function doSend(SentMessage $message): void
    {
        if (! function_exists('mail')) {
            throw new TransportException('A função mail() está desligada neste servidor. Usa um servidor SMTP.');
        }

        // Symfony leaves Bcc out of the raw message (it is only in the envelope); mail() needs it as a header.
        $bcc = [];
        $original = $message->getOriginalMessage();
        if ($original instanceof Email) {
            $bcc = array_map(fn (Address $a) => $a->toString(), MessageConverter::toEmail($original)->getBcc());
        }

        [$cabecalho, $corpo] = explode("\r\n\r\n", $message->toString(), 2) + [1 => ''];

        // To and Subject are mail()'s own arguments; everything else stays a header, as encoded by Symfony.
        $para = $assunto = '';
        $resto = [];
        foreach (preg_split('/\r\n(?![ \t])/', $cabecalho) as $linha) {
            [$nome, $valor] = explode(':', $linha, 2) + [1 => ''];
            match (strtolower(trim($nome))) {
                'to' => $para = trim($valor),
                'subject' => $assunto = trim($valor),
                default => $resto[] = $linha,
            };
        }
        if ($bcc !== []) {
            $resto[] = 'Bcc: '.implode(', ', $bcc);
        }

        $remetente = $message->getEnvelope()->getSender()->getAddress();
        $parametros = preg_match('/^[A-Za-z0-9._%+@-]+$/', $remetente) ? '-f'.$remetente : '';

        if (! $this->mail($para, $assunto, $corpo, implode("\r\n", $resto), $parametros)) {
            $erro = error_get_last()['message'] ?? 'o servidor recusou o envio';

            throw new TransportException('mail() falhou: '.$erro);
        }
    }

    /** PHP's mail(); a seam for the tests, which cannot send real mail. */
    protected function mail(string $para, string $assunto, string $corpo, string $cabecalhos, string $parametros): bool
    {
        return @mail($para, $assunto, $corpo, $cabecalhos, $parametros);
    }

    public function __toString(): string
    {
        return 'phpmail://default';
    }
}
