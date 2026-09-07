<?php

namespace App\Mail;

use App\Mail\Concerns\ComLayoutDeEmail;
use App\Models\Configuracao;
use App\Models\Servico;
use App\Models\Suspensao;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * "Serviço terminado" email, sent from SuspenderDialog when its "Enviar
 * email de aviso" checkbox is on. Reads the editable template stored at
 * configuracoes.email_template_servico_terminado (assunto/corpo) fresh on
 * every send, so Phase 7's template editor works against this mailable with
 * zero changes here.
 *
 * Renders through the shared resources/views/emails/notificacao.blade.php
 * wrapper (see ComLayoutDeEmail) for a real HTML layout instead of the old
 * nl2br(e($corpo)) plain-text rendering; the admin's own template wording
 * still flows through unchanged, only the presentation changed.
 */
class ServicoTerminadoMail extends Mailable
{
    use ComLayoutDeEmail;
    use SerializesModels;

    public function __construct(
        public Servico $servico,
        public Suspensao $suspensao,
    ) {}

    public function envelope(): Envelope
    {
        $template = Configuracao::obter('email_template_servico_terminado', []);

        return new Envelope(
            subject: $this->substituir((string) ($template['assunto'] ?? 'O seu acesso foi suspenso')),
        );
    }

    public function content(): Content
    {
        $template = Configuracao::obter('email_template_servico_terminado', []);
        $corpo = $this->substituir((string) ($template['corpo'] ?? ''));

        return $this->contentComLayout('Serviço suspenso', $corpo, [
            'Serviço' => $this->servico->nome,
            'Valor' => $this->formatarValor((float) $this->servico->valor),
            'Suspenso em' => $this->suspensao->data?->format('d/m/Y') ?? '',
        ]);
    }

    /** Replaces the shared placeholder tokens — [DATA] is the suspension date, not the vencimento. */
    private function substituir(string $texto): string
    {
        $empresa = Configuracao::obter('empresa', []);
        $cliente = $this->servico->cliente;

        return strtr($texto, [
            '[NOME]' => $cliente?->nome ?? '',
            '[SERVIÇO]' => $this->servico->nome,
            '[VALOR]' => $this->formatarValor((float) $this->servico->valor),
            '[DATA]' => $this->suspensao->data?->format('d/m/Y') ?? '',
            '[NOME DA EMPRESA]' => $empresa['nome'] ?? '',
        ]);
    }

    /** Same convention as resources/views/components/ui/money-value.blade.php: dot thousands, comma decimals, configured currency suffix. */
    private function formatarValor(float $valor): string
    {
        $abs = abs($valor);
        $inteiro = (int) floor($abs + 1e-9);
        $fracao = (int) round(($abs - $inteiro) * 100);
        $agrupado = number_format($inteiro, 0, '', '.');
        $formatado = ($valor < 0 ? '-' : '').$agrupado.($fracao ? ','.str_pad((string) $fracao, 2, '0', STR_PAD_LEFT) : '');

        return $formatado.' '.Configuracao::moeda();
    }
}
