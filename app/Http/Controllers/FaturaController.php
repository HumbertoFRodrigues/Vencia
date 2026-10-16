<?php

namespace App\Http\Controllers;

use App\Enums\Papel;
use App\Models\Configuracao;
use App\Models\Fatura;
use App\Services\Faturacao;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/** Files behind a plan invoice: the proof the customer sent and the receipt once it is paid. */
class FaturaController extends Controller
{
    public function comprovativo(Request $request, Fatura $fatura): Response
    {
        $this->autorizar($request, $fatura);
        abort_unless($fatura->comprovativo_path && Storage::disk('local')->exists($fatura->comprovativo_path), 404);

        return Storage::disk('local')->response($fatura->comprovativo_path);
    }

    public function recibo(Request $request, Fatura $fatura, Faturacao $faturacao): Response
    {
        $this->autorizar($request, $fatura);
        abort_unless($fatura->estado === Fatura::PAGA, 404);

        $fatura->load('conta', 'plano');
        $logo = public_path('assets/logo-mark.png');

        $pdf = Pdf::loadView('pdf.recibo-fatura', [
            'fatura' => $fatura,
            'emissor' => Configuracao::plataforma('faturacao', []) + ['nome' => config('app.name')],
            'valor' => $faturacao->valor($fatura),
            'logoDataUri' => is_file($logo) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logo)) : null,
        ])->setPaper('a4');

        return $pdf->download('recibo-'.$fatura->referenciaInterna().'.pdf');
    }

    /** The super-admin, or the dono of the conta the invoice belongs to. */
    private function autorizar(Request $request, Fatura $fatura): void
    {
        $user = $request->user();

        abort_unless($user->super_admin || $user->papelNa($fatura->conta) === Papel::Dono, 404);
    }
}
