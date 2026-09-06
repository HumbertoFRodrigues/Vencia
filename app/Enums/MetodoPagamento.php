<?php

namespace App\Enums;

enum MetodoPagamento: string
{
    case Mpesa = 'mpesa';
    case Emola = 'emola';
    case Transferencia = 'transferencia';
    case Dinheiro = 'dinheiro';
    case Outro = 'outro';
}
