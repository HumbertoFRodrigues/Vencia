<?php

namespace App\Enums;

enum ServicoStatus: string
{
    case Activo = 'activo';
    case AVencer = 'a_vencer';
    case Vencido = 'vencido';
    case Suspenso = 'suspenso';
    case Cancelado = 'cancelado';
}
