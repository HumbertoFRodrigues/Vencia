<?php

namespace App\Contracts;

use App\Models\Fatura;

/** A gateway that charges right away (e.g. M-Pesa C2B: PIN prompt on the customer's phone). */
interface CobrancaImediata
{
    /**
     * Charges the fatura to $telefone. On success the fatura is already paid
     * (Faturacao::marcarPaga) when this returns.
     *
     * @return array{ok: bool, mensagem: string, referencia: ?string}
     */
    public function cobrar(Fatura $fatura, string $telefone): array;
}
