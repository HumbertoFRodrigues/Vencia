<?php

namespace App\Enums;

enum IntervaloLembrete: string
{
    case D30 = 'd30';
    case D15 = 'd15';
    case D7 = 'd7';
    case D3 = 'd3';
    case D1 = 'd1';
    case NoDia = 'no_dia';
    case AposVencimento = 'apos_vencimento';

    /**
     * Fixed number of days before vencimento this interval fires, 0 for "no dia",
     * or null for "apos_vencimento" — that one isn't a fixed offset, it means
     * "any day after vencimento, sent at most once" and must be deduped by the
     * caller against lembretes_enviados rather than by comparing an offset.
     */
    public function diasOffset(): ?int
    {
        return match ($this) {
            self::D30 => 30,
            self::D15 => 15,
            self::D7 => 7,
            self::D3 => 3,
            self::D1 => 1,
            self::NoDia => 0,
            self::AposVencimento => null,
        };
    }

    /** @return list<self> All intervals in descending offset order (apos_vencimento last). */
    public static function ordenadas(): array
    {
        return [self::D30, self::D15, self::D7, self::D3, self::D1, self::NoDia, self::AposVencimento];
    }
}
