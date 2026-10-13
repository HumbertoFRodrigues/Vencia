<?php

namespace App\Enums;

enum TipoMovimento: string
{
    case Entrada = 'entrada';
    case Saida = 'saida';

    public function label(): string
    {
        return match ($this) {
            self::Entrada => 'Entrada',
            self::Saida => 'Saída',
        };
    }

    public function plural(): string
    {
        return match ($this) {
            self::Entrada => 'Entradas',
            self::Saida => 'Saídas',
        };
    }
}
