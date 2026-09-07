<?php

namespace App\Http\Controllers;

use App\Models\Configuracao;
use App\Models\Pagamento;
use App\View\Components\Ui\PaymentMethod;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Generates the one PDF a client-facing receipt needs — plain download, no
 * Livewire round-trip, since this is a one-shot file response and never
 * touches component state (see resources/views/pdf/recibo.blade.php for the
 * self-contained markup Dompdf renders).
 */
class ReciboController extends Controller
{
    public function pagamento(Pagamento $pagamento): Response
    {
        $pagamento->load(['cliente', 'servico']);

        $empresa = Configuracao::obter('empresa', []);

        $logoDataUri = null;
        $logoPath = $empresa['logo_path'] ?? null;

        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            $conteudo = Storage::disk('public')->get($logoPath);
            $mime = Storage::disk('public')->mimeType($logoPath) ?: 'image/png';
            $logoDataUri = 'data:'.$mime.';base64,'.base64_encode($conteudo);
        } elseif (is_file(public_path('assets/logo-mark.png'))) {
            // Falls back to the Vencia app mark until the empresa uploads its own logo,
            // same convention as RelatorioPdf::logoDataUri() — never ships a bare header.
            $logoDataUri = 'data:image/png;base64,'.base64_encode(file_get_contents(public_path('assets/logo-mark.png')));
        }

        $referencia = '#'.str_pad((string) $pagamento->id, 6, '0', STR_PAD_LEFT);

        $pdf = Pdf::loadView('pdf.recibo', [
            'pagamento' => $pagamento,
            'empresa' => $empresa,
            'logoDataUri' => $logoDataUri,
            'referencia' => $referencia,
            'metodoLabel' => PaymentMethod::labelFor($pagamento->metodo),
        ])->setPaper('a4');

        return $pdf->download('recibo-'.str_pad((string) $pagamento->id, 6, '0', STR_PAD_LEFT).'.pdf');
    }
}
