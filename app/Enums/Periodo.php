<?php

namespace App\Enums;

/**
 * Coarse billing period used purely for the "valor / mês / ano" display label
 * (see MoneyValue.jsx). MRR normalisation uses Periodicidade instead, since it
 * distinguishes trimestral/semestral from anual.
 */
enum Periodo: string
{
    case Mes = 'mes';
    case Ano = 'ano';
}
