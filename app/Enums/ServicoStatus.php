<?php

namespace App\Enums;

enum ServicoStatus: string
{
    case Activo = 'activo';
    case AVencer = 'a_vencer';
    case Vencido = 'vencido';
    case Suspenso = 'suspenso';
    case Cancelado = 'cancelado';

    /** Lower-case, for use mid-sentence ("Estado alterado para a vencer"). */
    public function label(): string
    {
        return match ($this) {
            self::Activo => 'activo',
            self::AVencer => 'a vencer',
            self::Vencido => 'vencido',
            self::Suspenso => 'suspenso',
            self::Cancelado => 'cancelado',
        };
    }
}
