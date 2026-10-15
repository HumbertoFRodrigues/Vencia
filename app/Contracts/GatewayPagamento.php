<?php

namespace App\Contracts;

use App\Models\Fatura;

/**
 * How a plan invoice gets paid. Today only GatewayManual (the customer pays
 * by M-Pesa/e-Mola/transfer and the super-admin approves). An automatic one
 * (e.g. M-Pesa C2B) implements the same contract and, once the operator
 * confirms, calls Faturacao::marcarPaga() — nothing else changes.
 */
interface GatewayPagamento
{
    /** Short code saved on the fatura (manual, mpesa…). */
    public function nome(): string;

    /** Whether the customer has to send a reference/proof for a person to check. */
    public function manual(): bool;

    /**
     * What to show the customer to pay this fatura.
     *
     * @return list<array{metodo: string, titulo: string, detalhe: string}>
     */
    public function instrucoes(Fatura $fatura): array;
}
