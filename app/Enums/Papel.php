<?php

namespace App\Enums;

/** What a member can do inside a conta (the business side; the personal panel is always private). */
enum Papel: string
{
    case Dono = 'dono';
    case Gestor = 'gestor';
    case Registos = 'registos';

    public function label(): string
    {
        return match ($this) {
            self::Dono => 'Dono',
            self::Gestor => 'Gestor',
            self::Registos => 'Registos',
        };
    }

    public function descricao(): string
    {
        return match ($this) {
            self::Dono => 'Tudo, incluindo o plano, a faturação e a equipa.',
            self::Gestor => 'Tudo na empresa menos o plano e a equipa.',
            self::Registos => 'Regista clientes, serviços e pagamentos. Não vê Finanças nem Configurações e não apaga.',
        };
    }

    /** Configurações, Finanças, Histórico, deleting and cancelling. */
    public function podeGerirNegocio(): bool
    {
        return $this !== self::Registos;
    }
}
