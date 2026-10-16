<?php

namespace App\Services;

use App\Enums\HistoricoKind;
use App\Mail\ReciboPagamentoMail;
use App\Models\Configuracao;
use App\Models\Historico;
use App\Models\Pagamento;
use App\View\Components\Ui\PaymentMethod;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

/** Payment receipts: the numbered PDF (download or email attachment) and sending it to the client. */
class Recibos
{
    public static function referencia(Pagamento $pagamento): string
    {
        return '#'.self::numero($pagamento);
    }

    public static function nomeFicheiro(Pagamento $pagamento): string
    {
        return 'recibo-'.self::numero($pagamento).'.pdf';
    }

    /** Numbered per conta; the id only for a sample that was never saved. */
    private static function numero(Pagamento $pagamento): string
    {
        return str_pad((string) ($pagamento->numero ?? $pagamento->id), 6, '0', STR_PAD_LEFT);
    }

    /** Suffix for the toast after recording a payment: whether the receipt went out. */
    public static function notaEnvio(?bool $enviado): string
    {
        return match ($enviado) {
            true => ' · recibo enviado ao cliente',
            false => ' · o recibo não foi enviado (cliente sem email ou SMTP por configurar)',
            null => '',
        };
    }

    /** The receipt PDF as raw bytes. */
    public function pdf(Pagamento $pagamento): string
    {
        $pagamento->loadMissing(['cliente', 'servico']);
        $empresa = Configuracao::obter('empresa', []);

        return Pdf::loadView('pdf.recibo', [
            'pagamento' => $pagamento,
            'empresa' => $empresa,
            'logoDataUri' => $this->logoDataUri($empresa['logo_path'] ?? null),
            'referencia' => self::referencia($pagamento),
            'metodoLabel' => PaymentMethod::labelFor($pagamento->metodo),
        ])->setPaper('a4')->output();
    }

    /**
     * Emails the receipt to the client (empresa in BCC) and logs it in the
     * history. Never throws: a failed email must not undo a recorded payment.
     */
    public function enviar(Pagamento $pagamento): bool
    {
        $pagamento->loadMissing(['cliente', 'servico']);
        $email = $pagamento->cliente?->email;

        // No real mailer yet (no SMTP saved, .env still on "log"): don't pretend it was sent.
        if (! $email || ! Configuracao::envioDisponivel()) {
            return false;
        }

        try {
            Configuracao::aplicarSmtpEmTempoDeExecucao();
            Mail::to($email)->bcc(Configuracao::emailBccAdmin($email))->send(new ReciboPagamentoMail($pagamento, $this->pdf($pagamento)));

            Historico::create([
                'cliente_id' => $pagamento->cliente_id,
                'servico_id' => $pagamento->servico_id,
                'occurred_at' => Carbon::now(),
                'title' => 'Recibo enviado',
                'description' => self::referencia($pagamento).' para '.$email,
                'kind' => HistoricoKind::Email,
            ]);

            return true;
        } catch (Throwable $e) {
            Log::error('Falha ao enviar o recibo '.self::referencia($pagamento).': '.$e->getMessage());

            return false;
        }
    }

    /** The empresa logo inlined, falling back to the Vencia mark so a receipt never has a bare header. */
    private function logoDataUri(?string $logoPath): ?string
    {
        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            $mime = Storage::disk('public')->mimeType($logoPath) ?: 'image/png';

            return 'data:'.$mime.';base64,'.base64_encode(Storage::disk('public')->get($logoPath));
        }

        return is_file(public_path('assets/logo-mark.png'))
            ? 'data:image/png;base64,'.base64_encode(file_get_contents(public_path('assets/logo-mark.png')))
            : null;
    }
}
