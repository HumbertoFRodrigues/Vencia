<?php

namespace App\Http\Controllers;

use App\Models\Pagamento;
use App\Services\Recibos;
use Symfony\Component\HttpFoundation\Response;

/**
 * Downloads a payment's receipt PDF — plain response, no Livewire round-trip.
 * The PDF itself is built by App\Services\Recibos (shared with the email that
 * sends it to the client).
 */
class ReciboController extends Controller
{
    public function pagamento(Pagamento $pagamento): Response
    {
        return response(app(Recibos::class)->pdf($pagamento), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.Recibos::nomeFicheiro($pagamento).'"',
        ]);
    }
}
