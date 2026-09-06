<?php

namespace App\Mail;

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
 */
class ServicoTerminadoMail extends Mailable
{
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

        return new Content(htmlString: nl2br(e($corpo)));
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

    /** Same convention as resources/views/components/ui/money-value.blade.php: dot thousands, comma decimals, "MZN" suffix. */
    private function formatarValor(float $valor): string
    {
        $abs = abs($valor);
        $inteiro = (int) floor($abs + 1e-9);
        $fracao = (int) round(($abs - $inteiro) * 100);
        $agrupado = number_format($inteiro, 0, '', '.');
        $formatado = ($valor < 0 ? '-' : '').$agrupado.($fracao ? ','.str_pad((string) $fracao, 2, '0', STR_PAD_LEFT) : '');

        return $formatado.' MZN';
    }
}
