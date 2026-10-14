<?php

namespace App\Mail;

use App\Mail\Concerns\ComLayoutDeEmail;
use App\Models\Configuracao;
use App\Models\Pagamento;
use App\Services\Recibos;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** "Recibo de pagamento" with the numbered PDF attached, in the same layout as the other client emails. */
class ReciboPagamentoMail extends Mailable
{
    use ComLayoutDeEmail;

    public function __construct(
        public Pagamento $pagamento,
        private string $pdf,
    ) {}

    public function envelope(): Envelope
    {
        $empresa = Configuracao::obter('empresa', [])['nome'] ?? config('app.name');

        return new Envelope(subject: 'Recibo '.Recibos::referencia($this->pagamento).' — '.$empresa);
    }

    public function content(): Content
    {
        $p = $this->pagamento;
        $empresa = Configuracao::obter('empresa', [])['nome'] ?? '';

        $corpo = 'Olá, '.($p->cliente?->nome ?? '').".\n\n"
            .'Confirmamos o pagamento de '.$this->formatarValor((float) $p->valor).' referente a '.($p->servico?->nome ?? 'serviço').".\n\n"
            ."Segue em anexo o recibo. Obrigado pela preferência.\n\n"
            ."Atenciosamente,\n{$empresa}";

        return $this->contentComLayout('Recibo de pagamento', $corpo, [
            'Recibo' => Recibos::referencia($p),
            'Serviço' => $p->servico?->nome ?? '',
            'Valor' => $this->formatarValor((float) $p->valor),
            'Data' => $p->data->format('d/m/Y'),
        ]);
    }

    /** @return list<Attachment> */
    public function attachments(): array
    {
        return [Attachment::fromData(fn () => $this->pdf, Recibos::nomeFicheiro($this->pagamento))->withMime('application/pdf')];
    }

    private function formatarValor(float $valor): string
    {
        return number_format($valor, $valor == floor($valor) ? 0 : 2, ',', '.').' '.Configuracao::moeda();
    }
}
