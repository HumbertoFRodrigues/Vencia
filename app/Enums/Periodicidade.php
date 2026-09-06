<?php

namespace App\Enums;

enum Periodicidade: string
{
    case Mensal = 'mensal';
    case Trimestral = 'trimestral';
    case Semestral = 'semestral';
    case Anual = 'anual';
    case Unica = 'unica';
    case Personalizada = 'personalizada';

    public function label(): string
    {
        return match ($this) {
            self::Mensal => 'Mensal',
            self::Trimestral => 'Trimestral',
            self::Semestral => 'Semestral',
            self::Anual => 'Anual',
            self::Unica => 'Única',
            self::Personalizada => 'Personalizada',
        };
    }
}
