<?php

namespace App\Enums;

/**
 * Icon + CSS tint mapping ported verbatim from components/data/ServiceLogo.jsx's CATEGORY map.
 */
enum ServicoCategoria: string
{
    case Dominio = 'dominio';
    case Hospedagem = 'hospedagem';
    case Email = 'email';
    case Ia = 'ia';
    case Software = 'software';
    case Manutencao = 'manutencao';
    case Desenvolvimento = 'desenvolvimento';
    case Outro = 'outro';

    public function icon(): string
    {
        return match ($this) {
            self::Dominio => 'globe',
            self::Hospedagem => 'server',
            self::Email => 'mail',
            self::Ia => 'sparkles',
            self::Software => 'app-window',
            self::Manutencao => 'wrench',
            self::Desenvolvimento => 'code',
            self::Outro => 'package',
        };
    }

    /**
     * @return array{bg: string, fg: string}
     */
    public function tintVar(): array
    {
        return [
            'bg' => "--cat-{$this->value}-bg",
            'fg' => "--cat-{$this->value}-fg",
        ];
    }
}
