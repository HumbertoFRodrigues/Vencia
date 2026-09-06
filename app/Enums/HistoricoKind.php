<?php

namespace App\Enums;

enum HistoricoKind: string
{
    case Criado = 'criado';
    case Pagamento = 'pagamento';
    case Activado = 'activado';
    case Lembrete = 'lembrete';
    case Vencido = 'vencido';
    case Suspenso = 'suspenso';
    case Cancelado = 'cancelado';
    case Alterado = 'alterado';
    case Email = 'email';
}
