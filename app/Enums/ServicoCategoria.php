<?php

namespace App\Enums;

/**
 * What a recurring service is, for its icon and tint (--cat-<value>-bg/fg).
 * Everyday businesses first, then the IT ones the app started with. The
 * order here is the order of every category picker.
 */
enum ServicoCategoria: string
{
    case Educacao = 'educacao';
    case Saude = 'saude';
    case Imoveis = 'imoveis';
    case Financas = 'financas';
    case Seguros = 'seguros';
    case Manutencao = 'manutencao';
    case Seguranca = 'seguranca';
    case Transporte = 'transporte';
    case Consultoria = 'consultoria';
    case Telefonia = 'telefonia';
    case Marketing = 'marketing';
    case Design = 'design';
    case Dominio = 'dominio';
    case Hospedagem = 'hospedagem';
    case Email = 'email';
    case Software = 'software';
    case Ia = 'ia';
    case Desenvolvimento = 'desenvolvimento';
    case Outro = 'outro';

    public function icon(): string
    {
        return match ($this) {
            self::Educacao => 'graduation-cap',
            self::Saude => 'heart-pulse',
            self::Imoveis => 'house',
            self::Financas => 'landmark',
            self::Seguros => 'file-text',
            self::Transporte => 'car',
            self::Dominio => 'globe',
            self::Hospedagem => 'server',
            self::Email => 'mail',
            self::Ia => 'sparkles',
            self::Software => 'app-window',
            self::Manutencao => 'wrench',
            self::Desenvolvimento => 'code',
            self::Marketing => 'megaphone',
            self::Design => 'palette',
            self::Seguranca => 'shield-check',
            self::Consultoria => 'briefcase',
            self::Telefonia => 'phone',
            self::Outro => 'package',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Educacao => 'Educação e explicações',
            self::Saude => 'Saúde e desporto',
            self::Imoveis => 'Imóveis e rendas',
            self::Financas => 'Contabilidade e finanças',
            self::Seguros => 'Seguros',
            self::Transporte => 'Transporte',
            self::Dominio => 'Domínio',
            self::Hospedagem => 'Hospedagem',
            self::Email => 'Email',
            self::Ia => 'IA',
            self::Software => 'Software',
            self::Manutencao => 'Manutenção e limpeza',
            self::Desenvolvimento => 'Desenvolvimento',
            self::Marketing => 'Marketing',
            self::Design => 'Design',
            self::Seguranca => 'Segurança',
            self::Consultoria => 'Consultoria',
            self::Telefonia => 'Telefonia e internet',
            self::Outro => 'Outro',
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
