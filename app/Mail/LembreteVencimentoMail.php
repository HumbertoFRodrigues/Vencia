<?php

namespace App\Mail;

use App\Enums\IntervaloLembrete;
use App\Models\Configuracao;
use App\Models\Servico;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * "Renewal reminder" email. Reuses the single editable template stored at
 * configuracoes.email_template_renovacao (assunto/corpo, with tokens
 * [NOME]/[SERVIÇO]/[VALOR]/[DATA]/[NOME DA EMPRESA]) for every reminder
 * interval (30/15/7/3/1 dias antes, no dia, após vencimento) — Phase 6 does
 * not invent a second per-interval template, per the brief's "Decisões
 * tomadas" (only two templates exist: this one and "Serviço terminado").
 *
 * The interval only changes a short, factual context line inserted right
 * after the greeting paragraph ("Faltam 30 dias para o vencimento." /
 * "O vencimento é hoje." / "O vencimento já passou."), so the tense of the
 * message stays correct before/on/after the due date without touching the
 * DB-editable wording. Everything else — subject and the rest of the body —
 * is read fresh from Configuracao on every send, so Phase 7's template
 * editor works against this mailable with zero changes.
 */
class LembreteVencimentoMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public Servico $servico,
        public IntervaloLembrete $intervalo,
    ) {}

    public function envelope(): Envelope
    {
        $template = Configuracao::obter('email_template_renovacao', []);

        return new Envelope(
            subject: $this->substituir((string) ($template['assunto'] ?? 'O seu serviço vence em breve')),
        );
    }

    public function content(): Content
    {
        $template = Configuracao::obter('email_template_renovacao', []);
        $corpo = $this->substituir((string) ($template['corpo'] ?? ''));
        $corpo = $this->comContextoIntervalo($corpo);

        return new Content(htmlString: nl2br(e($corpo)));
    }

    /** Replaces the shared placeholder tokens with real values for this servico. */
    private function substituir(string $texto): string
    {
        $empresa = Configuracao::obter('empresa', []);
        $cliente = $this->servico->cliente;

        return strtr($texto, [
            '[NOME]' => $cliente?->nome ?? '',
            '[SERVIÇO]' => $this->servico->nome,
            '[VALOR]' => $this->formatarValor((float) $this->servico->valor),
            '[DATA]' => $this->servico->vencimento?->format('d/m/Y') ?? '',
            '[NOME DA EMPRESA]' => $empresa['nome'] ?? '',
        ]);
    }

    /**
     * Inserts a one-line, factual, interval-specific statement between the
     * greeting paragraph and the rest of the template body (the seeded
     * template always separates paragraphs with a blank line). Falls back
     * to prepending it if the template has no blank-line break at all, so
     * a future template edit that drops paragraph structure never breaks
     * the send — it just reads slightly less elegantly.
     */
    private function comContextoIntervalo(string $corpo): string
    {
        $contexto = match ($this->intervalo) {
            IntervaloLembrete::D30 => 'Faltam 30 dias para o vencimento.',
            IntervaloLembrete::D15 => 'Faltam 15 dias para o vencimento.',
            IntervaloLembrete::D7 => 'Faltam 7 dias para o vencimento.',
            IntervaloLembrete::D3 => 'Faltam 3 dias para o vencimento.',
            IntervaloLembrete::D1 => 'Falta 1 dia para o vencimento.',
            IntervaloLembrete::NoDia => 'O vencimento é hoje.',
            IntervaloLembrete::AposVencimento => 'O vencimento já passou.',
        };

        $partes = explode("\n\n", $corpo, 2);

        if (count($partes) === 2) {
            return $partes[0]."\n\n".$contexto."\n\n".$partes[1];
        }

        return $contexto."\n\n".$corpo;
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
